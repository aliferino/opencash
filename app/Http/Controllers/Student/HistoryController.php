<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Support\CashLedger;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman "Riwayat" — catatan pembayaran milik siswa sendiri.
 *
 * Menjawab dua pertanyaan: sudah bayar KAPAN, dan untuk tagihan YANG MANA.
 * Sisa tiap tagihan ikut ditampilkan supaya siswa tidak perlu menghitung
 * sendiri. Data bisa diexport ke PDF/Excel.
 */
class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();

        $payments = $this->payments($student);

        return view('student.history.index', [
            'payments' => $payments,
            'summary' => $this->summary($student, $payments),
            'bills' => $this->billRows($student),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $student = $request->user();
        $payments = $this->payments($student);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.history.pdf', [
            'student' => $student,
            'group' => $student->group,
            'payments' => $payments,
            'summary' => $this->summary($student, $payments),
            'bills' => $this->billRows($student),
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('riwayat-kas-'.$this->slug($student->name).'-'.now()->format('Ymd').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $student = $request->user();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StudentHistoryExport($student),
            'riwayat-kas-'.$this->slug($student->name).'-'.now()->format('Ymd').'.xlsx'
        );
    }

    /**
     * Seluruh pembayaran siswa (semua status, termasuk yang ditolak) —
     * pembayaran ditolak tetap ditampilkan supaya siswa tahu kenapa.
     */
    private function payments($student)
    {
        return CashIncome::where('student_id', $student->id)
            ->with(['cashSchedule:id,description,amount,due_date', 'treasurer:id,name'])
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Sisa per tagihan: dibutuhkan di halaman & di berkas export.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function billRows($student)
    {
        $schedules = CashSchedule::where('group_id', $student->group_id)
            ->orderByDesc('due_date')
            ->get();

        $verified = CashIncome::where('student_id', $student->id)
            ->whereIn('status', ['verified', 'pending'])
            ->get();

        return CashLedger::billSummaries($schedules, $verified);
    }

    private function summary($student, $payments): array
    {
        $verified = $payments->where('status', 'verified');

        return [
            'total_paid' => (int) $verified->sum(fn (CashIncome $i) => (int) $i->amount_paid + (int) $i->fine_paid),
            'total_pending' => (int) $payments->where('status', 'pending')
                ->sum(fn (CashIncome $i) => (int) $i->amount_paid + (int) $i->fine_paid),
            'payments_count' => $payments->count(),
            'cash_count' => $verified->where('payment_method', 'cash')->count(),
            'qris_count' => $verified->where('payment_method', 'qris')->count(),
        ];
    }

    private function slug(string $name): string
    {
        return str($name)->slug()->value() ?: 'siswa';
    }
}
