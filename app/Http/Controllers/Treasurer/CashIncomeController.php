<?php

namespace App\Http\Controllers\Treasurer;

use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\User;
use App\Notifications\CashIncomeSubmitted;
use App\Notifications\CashIncomeVerified;
use Illuminate\Http\Request;

class CashIncomeController extends Controller
{
    /**
     * Bendahara melihat seluruh riwayat pemasukan satu kelas.
     * Siswa hanya melihat riwayat pembayarannya sendiri.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = CashIncome::with(['cashSchedule', 'student', 'treasurer'])
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $user->group_id));

        if ($user->isStudent()) {
            $query->where('student_id', $user->id);
        }

        return $query->latest('id')->paginate(20);
    }

    /**
     * Jalur A — Pembayaran Tunai.
     * Siswa serahkan uang fisik ke bendahara, bendahara input langsung.
     * Status langsung 'verified' karena uang sudah dipegang bendahara,
     * dan proof_image dibiarkan kosong.
     */
    public function storeCash(Request $request)
    {
        $data = $request->validate([
            'cash_schedule_id' => ['required', 'exists:cash_schedules,id'],
            'student_id' => ['required', 'exists:users,id'],
            'amount_paid' => ['required', 'integer', 'min:0'],
            'fine_paid' => ['nullable', 'integer', 'min:0'],
            'income_date' => ['required', 'date'],
        ]);

        $treasurer = $request->user();

        $schedule = CashSchedule::findOrFail($data['cash_schedule_id']);
        abort_unless($schedule->group_id === $treasurer->group_id, 403);

        $student = User::findOrFail($data['student_id']);
        abort_unless(
            $student->role === 'student' && $student->group_id === $treasurer->group_id,
            403
        );

        $income = CashIncome::create([
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'treasurer_id' => $treasurer->id,
            'amount_paid' => $data['amount_paid'],
            'fine_paid' => $data['fine_paid'] ?? 0,
            'income_date' => $data['income_date'],
            'payment_method' => 'cash',
            'proof_image' => null,
            'status' => 'verified',
        ]);

        return response()->json($income, 201);
    }

    /**
     * Jalur B — Pembayaran QRIS mandiri oleh siswa.
     * Status masuk sebagai 'pending' sampai bendahara mengecek mutasi
     * dan memverifikasinya secara manual.
     */
    public function storeQris(Request $request)
    {
        $student = $request->user();
        abort_unless($student->isStudent(), 403);

        $data = $request->validate([
            'cash_schedule_id' => ['required', 'exists:cash_schedules,id'],
            'amount_paid' => ['required', 'integer', 'min:0'],
            'proof_image' => ['required', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule = CashSchedule::findOrFail($data['cash_schedule_id']);
        abort_unless($schedule->group_id === $student->group_id, 403);

        $path = $request->file('proof_image')->store('proofs/incomes', 'public');

        $income = CashIncome::create([
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'treasurer_id' => null,
            'amount_paid' => $data['amount_paid'],
            'fine_paid' => 0,
            'income_date' => now()->toDateString(),
            'payment_method' => 'qris',
            'proof_image' => $path,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        // Lonceng notifikasi bendahara berbunyi.
        User::where('group_id', $student->group_id)
            ->where('role', 'treasurer')
            ->get()
            ->each(fn (User $treasurer) => $treasurer->notify(new CashIncomeSubmitted($income)));

        return response()->json($income, 201);
    }

    /**
     * Bendahara memverifikasi atau menolak pembayaran QRIS yang pending,
     * setelah mengecek mutasi rekening secara manual.
     */
    public function verify(Request $request, CashIncome $cashIncome)
    {
        $treasurer = $request->user();

        abort_unless($cashIncome->cashSchedule->group_id === $treasurer->group_id, 403);
        abort_unless($cashIncome->status === 'pending', 422, 'Pembayaran ini sudah diproses sebelumnya.');

        $data = $request->validate([
            'status' => ['required', 'in:verified,rejected'],
            'fine_paid' => ['nullable', 'integer', 'min:0'],
        ]);

        $cashIncome->update([
            'status' => $data['status'],
            'fine_paid' => $data['fine_paid'] ?? $cashIncome->fine_paid,
            'treasurer_id' => $treasurer->id,
        ]);

        if ($data['status'] === 'verified') {
            // Lonceng notifikasi siswa berbunyi.
            $cashIncome->student->notify(new CashIncomeVerified($cashIncome));
        }

        return response()->json($cashIncome->fresh());
    }
}