<?php

namespace App\Support;

use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use Illuminate\Support\Collection;

/**
 * Perhitungan kas kelas di satu tempat.
 *
 * Sebelumnya "lunas" cuma boolean (ada/tidak ada pembayaran verified), jadi
 * cicilan tidak mungkin dihitung. Sekarang setiap tagihan dihitung dari
 * JUMLAH nominal yang sudah dibayar, bukan dari ada/tidaknya baris:
 *
 *   - `paid`    = total amount_paid + fine_paid dari pembayaran verified
 *   - `pending` = total dari pembayaran yang masih menunggu verifikasi
 *   - `due`     = paid + pending  → dipakai untuk validasi supaya siswa tidak
 *                 bisa membayar dobel sementara bukti lamanya belum diverifikasi
 *   - `remaining` = amount - paid (minimal 0)
 *
 * Tagihan dianggap LUNAS kalau `remaining` sudah 0.
 */
class CashLedger
{
    /**
     * Status ringkas satu tagihan untuk satu siswa.
     *
     * `paid` & `pending` dipisah supaya UI bisa membedakan "sudah masuk kas"
     * dengan "masih menunggu verifikasi bendahara".
     *
     * @return array{paid: int, pending: int, due: int, remaining: int, amount: int, status: string, is_paid: bool, is_pending: bool, is_partial: bool}
     */
    public static function billSummary(CashSchedule $schedule, Collection $incomes): array
    {
        $amount = (int) $schedule->amount;

        $paid = (int) $incomes->where('status', 'verified')
            ->sum(fn (CashIncome $income) => (int) $income->amount_paid + (int) $income->fine_paid);

        $pending = (int) $incomes->where('status', 'pending')
            ->sum(fn (CashIncome $income) => (int) $income->amount_paid + (int) $income->fine_paid);

        // `due` = uang yang sudah "terpakai" (verified + menunggu verifikasi),
        // dipakai untuk mencegah pembayaran melebihi tagihan saat bukti QRIS
        // lama belum sempat diverifikasi bendahara.
        $due = $paid + $pending;
        $remaining = max($amount - $paid, 0);

        $status = match (true) {
            $remaining === 0 => 'paid',
            $paid > 0 => 'partial',
            $pending > 0 => 'pending',
            default => 'unpaid',
        };

        return [
            'amount' => $amount,
            'paid' => $paid,
            'pending' => $pending,
            'due' => $due,
            'remaining' => $remaining,
            'status' => $status,
            'is_paid' => $remaining === 0,
            'is_pending' => $status === 'pending',
            'is_partial' => $status === 'partial',
        ];
    }

    /**
     * Ringkas semua tagihan untuk SATU siswa (dipakai halaman Tagihan Saya,
     * Dashboard siswa, dan endpoint JSON).
     *
     * @param  Collection<int, CashSchedule>  $schedules
     * @param  Collection<int, CashIncome>  $incomes  seluruh pembayaran siswa ini
     * @return Collection<int, array<string, mixed>>
     */
    public static function billSummaries(Collection $schedules, Collection $incomes): Collection
    {
        return $schedules->map(function (CashSchedule $schedule) use ($incomes) {
            $own = $incomes->where('cash_schedule_id', $schedule->id)->values();

            return array_merge(
                self::billSummary($schedule, $own),
                [
                    'id' => $schedule->id,
                    'description' => $schedule->description,
                    'due_date' => $schedule->due_date,
                    'payments' => $own,
                ]
            );
        })->values();
    }

    /**
     * Progres pembayaran SELURUH siswa di kelas untuk satu tagihan.
     *
     * Siswa yang belum pernah membayar tetap dihitung, karena `leftJoin`
     * memakai daftar siswa sebagai tabel utama — jadi "belum bayar" muncul
     * eksplisit, bukan hilang dari daftar.
     *
     * @return array{paid: int, pending: int, remaining: int, target: int, paid_students: int, partial_students: int, unpaid_students: int, students: Collection<int, array<string, mixed>>}
     */
    public static function scheduleProgress(CashSchedule $schedule, Collection $students, Collection $incomes): array
    {
        $amount = (int) $schedule->amount;

        $rows = $students->map(function ($student) use ($schedule, $incomes) {
            $own = $incomes
                ->where('student_id', $student->id)
                ->where('cash_schedule_id', $schedule->id)
                ->values();

            $summary = self::billSummary($schedule, $own);

            return array_merge($summary, [
                'student_id' => $student->id,
                'student_name' => $student->name,
            ]);
        })->values();

        $paid = (int) $rows->sum('paid');
        $pending = (int) $rows->sum('pending');

        return [
            'target' => $amount * $rows->count(),
            'paid' => $paid,
            'pending' => $pending,
            'remaining' => max(($amount * $rows->count()) - $paid, 0),
            'paid_students' => $rows->where('is_paid', true)->count(),
            'partial_students' => $rows->where('is_partial', true)->count(),
            'unpaid_students' => $rows->whereIn('status', ['unpaid', 'pending'])->count(),
            'students' => $rows->sortBy('student_name')->values(),
        ];
    }

    /**
     * Sisa tagihan satu siswa untuk satu tagihan.
     *
     * `verified + pending` dihitung sebagai "sudah terpakai" supaya siswa
     * tidak bisa membayar dobel saat bukti QRIS-nya belum diverifikasi.
     * `$exceptIncomeId` dipakai saat bendahara mengoreksi nominal satu baris —
     * baris itu tidak boleh dihitung dua kali.
     */
    public static function remainingFor(CashSchedule $schedule, int $studentId, ?int $exceptIncomeId = null): int
    {
        $query = CashIncome::where('cash_schedule_id', $schedule->id)
            ->where('student_id', $studentId)
            ->whereIn('status', ['verified', 'pending']);

        if ($exceptIncomeId !== null) {
            $query->whereKeyNot($exceptIncomeId);
        }

        $alreadyDue = (int) $query
            ->selectRaw('COALESCE(SUM(amount_paid + fine_paid), 0) as total')
            ->value('total');

        return (int) $schedule->amount - $alreadyDue;
    }

    /**
     * Format rupiah yang aman untuk angka negatif.
     *
     * Saldo kas kelas bisa minus (pengeluaran lebih besar dari pemasukan yang
     * sudah terverifikasi), dan `number_format(-9000)` menghasilkan "Rp-9.000"
     * yang sulit dibaca. Di sini jadi "−Rp9.000".
     */
    public static function rupiah(int $amount): string
    {
        $formatted = number_format(abs($amount), 0, ',', '.');

        return $amount < 0 ? '−Rp'.$formatted : 'Rp'.$formatted;
    }

    /**
     * Saldo kas kelas = seluruh pemasukan verified − seluruh pengeluaran.
     *
     * Dipakai di dashboard bendahara, dashboard siswa, dan halaman Kas Kelas
     * supaya ketiganya menampilkan angka yang sama persis.
     *
     * @return array{income: int, expense: int, balance: int}
     */
    public static function balance(int $groupId): array
    {
        $income = (int) CashIncome::where('status', 'verified')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->selectRaw('COALESCE(SUM(amount_paid + fine_paid), 0) as total')
            ->value('total');

        $expense = (int) CashExpense::where('group_id', $groupId)->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }
}
