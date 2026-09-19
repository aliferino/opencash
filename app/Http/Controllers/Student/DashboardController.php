<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Support\CashLedger;
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

        $incomes = CashIncome::where('student_id', $student->id)
            ->whereIn('status', ['verified', 'pending'])
            ->get();

        $bills = CashLedger::billSummaries($schedules, $incomes);

        // Tagihan yang belum lunas — termasuk yang BARU DIBAYAR SEBAGIAN,
        // karena sisanya masih harus ditagih.
        $unpaidBills = $bills
            ->reject(fn (array $bill) => $bill['is_paid'])
            ->values();

        $recentExpenses = CashExpense::where('group_id', $student->group_id)
            ->with('treasurer:id,name')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('student.index', [
            'bills' => $bills,
            'unpaidBills' => $unpaidBills,
            'unpaidCount' => $unpaidBills->count(),
            'totalPaid' => (int) $bills->sum('paid'),
            'totalRemaining' => (int) $bills->sum('remaining'),
            'pendingTotal' => (int) $bills->sum('pending'),
            'balance' => CashLedger::balance($student->group_id),
            'recentExpenses' => $recentExpenses,
        ]);
    }
}
