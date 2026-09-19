<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Support\CashLedger;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman "Kas Kelas" — transparansi untuk siswa.
 *
 * Siswa boleh tahu ke mana uang kasnya pergi: saldo sekarang, rincian
 * pemasukan (siapa yang sudah bayar), dan seluruh pengeluaran kelas beserta
 * notasinya. Semua read-only.
 */
class StudentCashController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();
        $groupId = $student->group_id;

        $expenses = CashExpense::where('group_id', $groupId)
            ->with('treasurer:id,name')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        return view('student.cash.index', [
            'balance' => CashLedger::balance($groupId),
            'expenses' => $expenses,
            'expenseByCategory' => $this->expenseSummary($expenses),
        ]);
    }

    /**
     * Ringkasan pengeluaran per deskripsi, supaya siswa cepat melihat
     * "uangnya habis untuk apa saja" tanpa membaca satu per satu.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CashExpense>  $expenses
     * @return \Illuminate\Support\Collection<int, array{description: string, total: int, count: int}>
     */
    private function expenseSummary($expenses)
    {
        return $expenses
            ->groupBy(fn (CashExpense $expense) => trim((string) $expense->description))
            ->map(fn ($group, $description) => [
                'description' => $description !== '' ? $description : 'Tanpa keterangan',
                'total' => (int) $group->sum('amount'),
                'count' => $group->count(),
            ])
            ->sortByDesc('total')
            ->values();
    }
}
