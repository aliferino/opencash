<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Support\CashLedger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $groupId = $request->user()->group_id;

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
            'balance' => CashLedger::balance($groupId)['balance'],
            'pendingIncomes' => $pendingIncomes,
            'pendingCount' => $pendingIncomes->count(),
            'upcomingSchedules' => $upcomingSchedules,
        ]);
    }
}