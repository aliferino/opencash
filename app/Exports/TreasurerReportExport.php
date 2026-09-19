<?php

namespace App\Exports;

use App\Models\CashExpense;
use App\Models\CashIncome;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export laporan kas bendahara ke .xlsx.
 *
 * `scope` menentukan isi berkas: `income` (pemasukan saja), `expense`
 * (pengeluaran saja), atau `all` (keduanya). Semua ditulis di satu sheet
 * sebagai blok-blok, sama seperti export riwayat siswa.
 *
 * Catatan: pemasukan yang diexport HANYA yang `verified`, supaya totalnya
 * konsisten dengan kartu "Total Pemasukan" di halaman Laporan (pemasukan
 * `pending` belum dianggap masuk kas).
 */
class TreasurerReportExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        private readonly int $groupId,
        private readonly string $scope = 'all',
        private readonly ?string $groupName = null,
    ) {}

    public function title(): string
    {
        return match ($this->scope) {
            'income' => 'Pemasukan',
            'expense' => 'Pengeluaran',
            default => 'Laporan Kas',
        };
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];

        $rows[] = ['Laporan Kas — '.($this->groupName ?? 'Kelas')];
        $rows[] = ['Dicetak', now()->translatedFormat('d F Y, H:i').' WIB'];
        $rows[] = ['Cakupan', $this->scopeLabel()];
        $rows[] = [];

        if (in_array($this->scope, ['income', 'all'], true)) {
            $incomes = $this->incomes();

            $rows[] = ['PEMASUKAN (terverifikasi)'];
            $rows[] = ['Tanggal', 'Siswa', 'Tagihan', 'Metode', 'Nominal', 'Denda', 'Total', 'Dicatat Oleh'];
            $rows[] = [];

            foreach ($incomes as $income) {
                $rows[] = [
                    optional($income->income_date)->format('d/m/Y') ?? '-',
                    $income->student?->name ?? '-',
                    $income->cashSchedule?->description ?? '-',
                    $income->payment_method === 'qris' ? 'QRIS' : 'Tunai',
                    (int) $income->amount_paid,
                    (int) $income->fine_paid,
                    (int) $income->amount_paid + (int) $income->fine_paid,
                    $income->treasurer?->name ?? 'Mandiri (QRIS)',
                ];
            }

            $rows[] = ['Total Pemasukan', '', '', '', '', '', (int) $incomes->sum(fn ($i) => (int) $i->amount_paid + (int) $i->fine_paid), ''];
            $rows[] = [];
        }

        if (in_array($this->scope, ['expense', 'all'], true)) {
            $expenses = $this->expenses();

            $rows[] = ['PENGELUARAN'];
            $rows[] = ['Tanggal', 'Keterangan', 'Dicatat Oleh', 'Nominal'];
            $rows[] = [];

            foreach ($expenses as $expense) {
                $rows[] = [
                    optional($expense->expense_date)->format('d/m/Y') ?? '-',
                    $expense->description,
                    $expense->treasurer?->name ?? '-',
                    (int) $expense->amount,
                ];
            }

            $rows[] = ['Total Pengeluaran', '', '', (int) $expenses->sum('amount')];
            $rows[] = [];
        }

        if ($this->scope === 'all') {
            $income = (int) $this->incomes()->sum(fn ($i) => (int) $i->amount_paid + (int) $i->fine_paid);
            $expense = (int) $this->expenses()->sum('amount');

            $rows[] = ['RINGKASAN'];
            $rows[] = ['Total Pemasukan', $income];
            $rows[] = ['Total Pengeluaran', $expense];
            $rows[] = ['Saldo Kas', $income - $expense];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
        ];
    }

    public function scopeLabel(): string
    {
        return match ($this->scope) {
            'income' => 'Pemasukan saja',
            'expense' => 'Pengeluaran saja',
            default => 'Pemasukan & pengeluaran',
        };
    }

    /** @return \Illuminate\Support\Collection<int, CashIncome> */
    public function incomes()
    {
        return CashIncome::where('status', 'verified')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $this->groupId))
            ->with(['student:id,name', 'cashSchedule:id,description', 'treasurer:id,name'])
            ->orderBy('income_date')
            ->orderBy('id')
            ->get();
    }

    /** @return \Illuminate\Support\Collection<int, CashExpense> */
    public function expenses()
    {
        return CashExpense::where('group_id', $this->groupId)
            ->with('treasurer:id,name')
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get();
    }
}
