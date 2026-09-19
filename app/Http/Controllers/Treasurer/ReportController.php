<?php

namespace App\Http\Controllers\Treasurer;

use App\Exports\TreasurerReportExport;
use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\User;
use App\Support\CashLedger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $groupId = $request->user()->group_id;

        $balance = CashLedger::balance($groupId);
        $totalIncome = $balance['income'];
        $totalExpense = $balance['expense'];

        $pending = CashIncome::where('status', 'pending')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(amount_paid + fine_paid), 0) as amount')
            ->first();

        $studentsCount = User::where('group_id', $groupId)->where('role', 'student')->count();

        $paidStudentsCount = CashIncome::where('status', 'verified')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->distinct()
            ->count('student_id');

        $summary = [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'pending_count' => (int) $pending->total,
            'pending_amount' => (int) $pending->amount,
            'students_count' => $studentsCount,
            'paid_students_count' => $paidStudentsCount,
            'unpaid_students_count' => max($studentsCount - $paidStudentsCount, 0),
            'schedules_count' => CashSchedule::where('group_id', $groupId)->count(),
            'expenses_count' => CashExpense::where('group_id', $groupId)->count(),
        ];

        $topStudents = CashIncome::where('status', 'verified')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->selectRaw('student_id, SUM(amount_paid + fine_paid) as total_paid, COUNT(*) as payments_count')
            ->groupBy('student_id')
            ->orderByDesc('total_paid')
            ->limit(5)
            ->with('student:id,name')
            ->get();

        $recentIncomes = CashIncome::where('status', 'verified')
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId))
            ->with(['student:id,name', 'cashSchedule:id,description'])
            ->latest('income_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (CashIncome $income) => [
                'type' => 'income',
                'date' => $income->income_date,
                'title' => $income->student?->name ?? '—',
                'subtitle' => $income->cashSchedule?->description ?? '—',
                'amount' => $income->amount_paid + $income->fine_paid,
            ])
            ->values();

        $recentExpenses = CashExpense::where('group_id', $groupId)
            ->with('treasurer:id,name')
            ->latest('expense_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (CashExpense $expense) => [
                'type' => 'expense',
                'date' => $expense->expense_date,
                'title' => $expense->description,
                'subtitle' => $expense->treasurer?->name ?? '—',
                'amount' => $expense->amount,
            ]);

        $recent = $recentIncomes->merge($recentExpenses)
            ->sortByDesc(fn (array $row) => optional($row['date'])->timestamp ?? 0)
            ->take(8)
            ->values();

        return view('treasurer.reports.index', [
            'summary' => $summary,
            'topStudents' => $topStudents,
            'recent' => $recent,
        ]);
    }

    public function incomes(Request $request)
    {
        $groupId = $request->user()->group_id;

        $query = CashIncome::with(['cashSchedule:id,description', 'student:id,name', 'treasurer:id,name'])
            ->whereHas('cashSchedule', fn ($q) => $q->where('group_id', $groupId));

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['pending', 'verified', 'rejected'], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->whereHas('student', fn ($student) => $student->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('cashSchedule', fn ($schedule) => $schedule->where('description', 'like', '%'.$search.'%'));
            });
        }

        return $query->latest('income_date')->latest('id')->paginate($request->integer('per_page', 15));
    }

    public function expenses(Request $request)
    {
        $groupId = $request->user()->group_id;

        $query = CashExpense::with('treasurer:id,name')->where('group_id', $groupId);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                    ->orWhereHas('treasurer', fn ($treasurer) => $treasurer->where('name', 'like', '%'.$search.'%'));
            });
        }

        return $query->latest('expense_date')->latest('id')->paginate($request->integer('per_page', 15));
    }

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

    /**
     * Export laporan. `scope` menentukan isi: `income`, `expense`, atau `all`.
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $scope = $this->scope($request);
        $export = new TreasurerReportExport($user->group_id, $scope, $user->group?->name);

        $pdf = Pdf::loadView('treasurer.reports.pdf', [
            'group' => $user->group,
            'scope' => $scope,
            'scopeLabel' => $export->scopeLabel(),
            'incomes' => in_array($scope, ['income', 'all'], true) ? $export->incomes() : collect(),
            'expenses' => in_array($scope, ['expense', 'all'], true) ? $export->expenses() : collect(),
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-kas-'.$scope.'-'.now()->format('Ymd').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $scope = $this->scope($request);

        return Excel::download(
            new TreasurerReportExport($user->group_id, $scope, $user->group?->name),
            'laporan-kas-'.$scope.'-'.now()->format('Ymd').'.xlsx'
        );
    }

    private function scope(Request $request): string
    {
        $scope = $request->string('scope')->toString();

        return in_array($scope, ['income', 'expense', 'all'], true) ? $scope : 'all';
    }
}
