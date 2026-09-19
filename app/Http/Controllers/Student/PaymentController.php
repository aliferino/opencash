<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Support\CashLedger;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman "Tagihan Saya" — siswa hanya melihat.
 *
 * Bedanya dengan halaman Jadwal Tagihan bendahara: di sini setiap tagihan
 * menampilkan nominal, yang sudah dibayar, dan SISANYA, karena pembayaran
 * boleh dicicil. Siswa juga bisa mengunggah bukti QRIS untuk sisa yang belum
 * dibayar (atau sebagian darinya).
 */
class PaymentController extends Controller
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

        $summary = [
            'total_billed' => (int) $bills->sum('amount'),
            'total_paid' => (int) $bills->sum('paid'),
            'total_pending' => (int) $bills->sum('pending'),
            'total_remaining' => (int) $bills->sum('remaining'),
            'paid_count' => $bills->where('is_paid', true)->count(),
            'partial_count' => $bills->where('is_partial', true)->count(),
            'unpaid_count' => $bills->whereIn('status', ['unpaid', 'pending'])->count(),
        ];

        $qrisImage = $student->group?->qris_image;

        return view('student.bills.index', [
            'bills' => $bills,
            'summary' => $summary,
            'qrisImage' => $qrisImage,
        ]);
    }
}
