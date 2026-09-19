<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\User;
use App\Notifications\CashIncomeSubmitted;
use App\Notifications\CashIncomeVerified;
use App\Support\CashLedger;
use Illuminate\Http\Request;

class CashIncomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = CashIncome::with(['cashSchedule', 'student', 'treasurer'])
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $user->group_id));

        if ($user->isStudent()) {
            $query->where('student_id', $user->id);
        }

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['pending', 'verified', 'rejected'], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('payment_method') && in_array($request->string('payment_method')->toString(), ['cash', 'qris'], true)) {
            $query->where('payment_method', $request->string('payment_method')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->whereHas('student', fn ($student) => $student->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('cashSchedule', fn ($schedule) => $schedule->where('description', 'like', '%'.$search.'%'));
            });
        }

        $query->latest('id');

        if (! $request->wantsJson() && $user->isTreasurer()) {
            $summaryQuery = CashIncome::whereHas('cashSchedule', fn ($q) => $q->where('group_id', $user->group_id));

            if ($user->isStudent()) {
                $summaryQuery->where('student_id', $user->id);
            }

            $summary = [
                'pending' => (clone $summaryQuery)->where('status', 'pending')->count(),
                'verified' => (clone $summaryQuery)->where('status', 'verified')->count(),
                'rejected' => (clone $summaryQuery)->where('status', 'rejected')->count(),
                'total_verified' => (int) (clone $summaryQuery)->where('status', 'verified')
                    ->selectRaw('COALESCE(SUM(amount_paid + fine_paid), 0) as total')
                    ->value('total'),
            ];

            return view('treasurer.incomes.index', compact('summary'));
        }

        return $query->paginate($request->integer('per_page', 15))->through(function (CashIncome $income) use ($user) {
            $income->setAttribute('can_verify', $user->isTreasurer() && $income->status === 'pending');

            return $income;
        });
    }

    /**
     * Sisa tagihan satu siswa untuk satu tagihan.
     *
     * Dipakai form "Catat Pembayaran Tunai" supaya bendahara langsung tahu
     * siswa ini masih kurang berapa, dan tidak bisa input melebihi sisa.
     */
    public function remaining(Request $request)
    {
        $treasurer = $request->user();

        $data = $request->validate([
            'cash_schedule_id' => ['required', 'exists:cash_schedules,id'],
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $schedule = CashSchedule::findOrFail($data['cash_schedule_id']);
        abort_unless($schedule->group_id === $treasurer->group_id, 403);

        $student = User::findOrFail($data['student_id']);
        abort_unless($student->group_id === $treasurer->group_id, 403);

        $remaining = CashLedger::remainingFor($schedule, $student->id);

        $incomes = CashIncome::where('cash_schedule_id', $schedule->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['verified', 'pending'])
            ->get();

        $summary = CashLedger::billSummary($schedule, $incomes);

        return response()->json([
            'amount' => $summary['amount'],
            'paid' => $summary['paid'],
            'pending' => $summary['pending'],
            'remaining' => max($remaining, 0),
            'status' => $summary['status'],
        ]);
    }

    public function storeCash(Request $request)
    {
        $data = $request->validate([
            'cash_schedule_id' => ['required', 'exists:cash_schedules,id'],
            'student_id' => ['required', 'exists:users,id'],
            'amount_paid' => ['required', 'integer', 'min:1'],
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

        $this->assertWithinRemaining($schedule, $student->id, (int) $data['amount_paid']);

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

    public function storeQris(Request $request)
    {
        $student = $request->user();
        abort_unless($student->isStudent(), 403);

        $data = $request->validate([
            'cash_schedule_id' => ['required', 'exists:cash_schedules,id'],
            'amount_paid' => ['required', 'integer', 'min:1'],
            'proof_image' => ['required', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule = CashSchedule::findOrFail($data['cash_schedule_id']);
        abort_unless($schedule->group_id === $student->group_id, 403);

        $this->assertWithinRemaining($schedule, $student->id, (int) $data['amount_paid']);

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

        User::where('group_id', $student->group_id)
            ->where('role', 'treasurer')
            ->get()
            ->each(fn (User $treasurer) => $treasurer->notify(new CashIncomeSubmitted($income)));

        return response()->json($income, 201);
    }

    public function verify(Request $request, CashIncome $cashIncome)
    {
        $treasurer = $request->user();

        abort_unless($cashIncome->cashSchedule->group_id === $treasurer->group_id, 403);
        abort_unless($cashIncome->status === 'pending', 422, 'Pembayaran ini sudah diproses sebelumnya.');

        $data = $request->validate([
            'status' => ['required', 'in:verified,rejected'],
            'amount_paid' => ['nullable', 'integer', 'min:1'],
            'fine_paid' => ['nullable', 'integer', 'min:0'],
        ]);

        // Bendahara boleh mengoreksi nominal saat verifikasi — mis. bukti transfer
        // ternyata berbeda dengan yang diisi siswa. Sisa tagihan dihitung dari
        // nominal lain (selain baris ini) supaya koreksinya tetap valid.
        if ($data['status'] === 'verified' && isset($data['amount_paid'])) {
            $this->assertWithinRemaining(
                $cashIncome->cashSchedule,
                $cashIncome->student_id,
                (int) $data['amount_paid'],
                exceptIncomeId: $cashIncome->id,
            );
        }

        $cashIncome->update([
            'status' => $data['status'],
            'amount_paid' => $data['amount_paid'] ?? $cashIncome->amount_paid,
            'fine_paid' => $data['fine_paid'] ?? $cashIncome->fine_paid,
            'treasurer_id' => $treasurer->id,
        ]);

        if ($data['status'] === 'verified') {
            $cashIncome->student->notify(new CashIncomeVerified($cashIncome));
        }

        return response()->json($cashIncome->fresh());
    }

    /**
     * Cicilan: pembayaran boleh berkali-kali, tapi totalnya tidak boleh
     * melebihi nominal tagihan.
     *
     * `due` (verified + pending) dipakai sebagai patokan, bukan hanya
     * `verified` — supaya siswa tidak bisa menembak pembayaran kedua saat
     * bukti QRIS pertamanya belum diverifikasi bendahara.
     */
    private function assertWithinRemaining(CashSchedule $schedule, int $studentId, int $amountPaid, ?int $exceptIncomeId = null): void
    {
        $remaining = CashLedger::remainingFor($schedule, $studentId, $exceptIncomeId);

        abort_if(
            $remaining <= 0,
            422,
            'Tagihan ini sudah lunas.'
        );

        abort_if(
            $amountPaid > $remaining,
            422,
            'Nominal melebihi sisa tagihan. Sisa yang belum dibayar Rp'.number_format($remaining, 0, ',', '.').'.'
        );
    }
}
