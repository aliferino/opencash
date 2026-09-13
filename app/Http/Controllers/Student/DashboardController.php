<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();

        $schedules = CashSchedule::where('group_id', $student->group_id)
            ->orderByDesc('due_date')
            ->get();

        $incomes = CashIncome::where('student_id', $student->id)->get();

        $verifiedIds = $incomes->where('status', 'verified')->pluck('cash_schedule_id');
        $pendingIds = $incomes->where('status', 'pending')->pluck('cash_schedule_id');

        $schedules->each(function (CashSchedule $schedule) use ($verifiedIds, $pendingIds) {
            $schedule->is_paid = $verifiedIds->contains($schedule->id);
            $schedule->is_pending = $pendingIds->contains($schedule->id);
        });

        $unpaidSchedules = $schedules
            ->reject(fn (CashSchedule $s) => $s->is_paid || $s->is_pending)
            ->values();

        $totalPaid = (int) $incomes->where('status', 'verified')
            ->sum(fn (CashIncome $i) => $i->amount_paid + $i->fine_paid);

        return view('student.index', [
            'schedules' => $schedules,
            'unpaidSchedules' => $unpaidSchedules,
            'unpaidCount' => $unpaidSchedules->count(),
            'totalPaid' => $totalPaid,
        ]);
    }
}