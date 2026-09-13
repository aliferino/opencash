<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $groupId = $request->user()->group_id;

        $totalIncome = (int) CashIncome::where('status', 'verified')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->selectRaw('COALESCE(SUM(amount_paid + fine_paid), 0) as total')
            ->value('total');

        $totalExpense = (int) CashExpense::where('group_id', $groupId)->sum('amount');

        $pendingIncomes = CashIncome::where('status', 'pending')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->with(['student:id,name', 'cashSchedule:id,description,amount'])
            ->latest('id')
            ->get();

        $upcomingSchedules = CashSchedule::where('group_id', $groupId)
            ->where('due_date', '>=', now()->toDateString())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('treasurer.index', [
            'balance' => $totalIncome - $totalExpense,
            'pendingIncomes' => $pendingIncomes,
            'pendingCount' => $pendingIncomes->count(),
            'upcomingSchedules' => $upcomingSchedules,
        ]);
    }
}