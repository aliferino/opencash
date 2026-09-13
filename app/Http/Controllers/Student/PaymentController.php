<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\GroupSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
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
            $schedule->status = match (true) {
                $verifiedIds->contains($schedule->id) => 'verified',
                $pendingIds->contains($schedule->id) => 'pending',
                default => 'unpaid',
            };
        });

        $qrisSetting = GroupSetting::where('group_id', $student->group_id)
            ->whereNotNull('qris_image')
            ->latest('id')
            ->first();

        return view('student.bills.index', [
            'schedules' => $schedules,
            'qrisImage' => $qrisSetting?->qris_image,
        ]);
    }
}