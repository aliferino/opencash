<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\User;
use App\Support\CashLedger;
use Illuminate\Http\Request;

class CashScheduleController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('treasurer.schedules.index');
        }

        $user = $request->user();

        $schedules = CashSchedule::where('group_id', $user->group_id)
            ->orderByDesc('due_date')
            ->get();

        if ($user->isStudent()) {
            $incomes = CashIncome::where('student_id', $user->id)
                ->whereIn('status', ['verified', 'pending'])
                ->get();

            return response()->json(
                CashLedger::billSummaries($schedules, $incomes)
            );
        }

        // Bendahara butuh progres kelas: berapa yang sudah terkumpul dan
        // berapa siswa yang masih kurang, bukan cuma daftar tagihannya.
        $students = User::where('group_id', $user->group_id)
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name']);

        $incomes = CashIncome::whereHas('cashSchedule', fn ($q) => $q->where('group_id', $user->group_id))
            ->whereIn('status', ['verified', 'pending'])
            ->get();

        $schedules->each(function (CashSchedule $schedule) use ($students, $incomes) {
            $schedule->progress = CashLedger::scheduleProgress($schedule, $students, $incomes);
        });

        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'due_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        $schedule = CashSchedule::create([
            ...$data,
            'group_id' => $request->user()->group_id,
        ]);

        return response()->json($schedule, 201);
    }

    public function update(Request $request, CashSchedule $cashSchedule)
    {
        $this->authorizeOwnership($request, $cashSchedule);

        $data = $request->validate([
            'due_date' => ['sometimes', 'date'],
            'description' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'integer', 'min:0'],
        ]);

        $cashSchedule->update($data);

        return response()->json($cashSchedule);
    }

    public function destroy(Request $request, CashSchedule $cashSchedule)
    {
        $this->authorizeOwnership($request, $cashSchedule);

        $cashSchedule->delete();

        return response()->noContent();
    }

    private function authorizeOwnership(Request $request, CashSchedule $cashSchedule): void
    {
        abort_unless($cashSchedule->group_id === $request->user()->group_id, 403);
    }
}