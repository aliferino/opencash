<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function balance(Request $request)
    {
        $groupId = $request->user()->group_id;

        $totalIncome = (int) CashIncome::where('status', 'verified')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->selectRaw('COALESCE(SUM(amount_paid + fine_paid), 0) as total')
            ->value('total');

        $totalExpense = (int) CashExpense::where('group_id', $groupId)->sum('amount');

        return response()->json([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ]);
    }

    public function studentReport(Request $request, User $student)
    {
        abort_unless($student->group_id === $request->user()->group_id, 403);

        $incomes = CashIncome::where('student_id', $student->id)
            ->where('status', 'verified')
            ->with('cashSchedule')
            ->get();

        return response()->json([
            'student' => $student->only(['id', 'name']),
            'incomes' => $incomes,
            'total_paid' => (int) $incomes->sum(fn (CashIncome $i) => $i->amount_paid + $i->fine_paid),
        ]);
    }

    public function groupReport(Request $request)
    {
        $groupId = $request->user()->group_id;

        return response()->json([
            'incomes' => CashIncome::where('status', 'verified')
                ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
                ->with(['cashSchedule', 'student'])
                ->get(),
            'expenses' => CashExpense::where('group_id', $groupId)->get(),
        ]);
    }

    public function exportPdf(Request $request)
    {
        abort(501, 'Export PDF belum aktif — install barryvdh/laravel-dompdf terlebih dahulu.');
    }

    public function exportExcel(Request $request)
    {
        abort(501, 'Export Excel belum aktif — install maatwebsite/excel terlebih dahulu.');
    }
}