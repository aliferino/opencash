<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\User;
use App\Notifications\CashIncomeSubmitted;
use App\Notifications\CashIncomeVerified;
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

        $this->abortIfAlreadyPaid($schedule->id, $student->id);

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
            'amount_paid' => ['required', 'integer', 'min:0'],
            'proof_image' => ['required', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule = CashSchedule::findOrFail($data['cash_schedule_id']);
        abort_unless($schedule->group_id === $student->group_id, 403);

        $this->abortIfAlreadyPaid($schedule->id, $student->id);

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
            'fine_paid' => ['nullable', 'integer', 'min:0'],
        ]);

        $cashIncome->update([
            'status' => $data['status'],
            'fine_paid' => $data['fine_paid'] ?? $cashIncome->fine_paid,
            'treasurer_id' => $treasurer->id,
        ]);

        if ($data['status'] === 'verified') {
            $cashIncome->student->notify(new CashIncomeVerified($cashIncome));
        }

        return response()->json($cashIncome->fresh());
    }

    private function abortIfAlreadyPaid(int $cashScheduleId, int $studentId): void
    {
        $alreadyExists = CashIncome::where('cash_schedule_id', $cashScheduleId)
            ->where('student_id', $studentId)
            ->whereIn('status', ['verified', 'pending'])
            ->exists();

        abort_if($alreadyExists, 422, 'Tagihan ini sudah dibayar atau sedang menunggu verifikasi.');
    }
}
