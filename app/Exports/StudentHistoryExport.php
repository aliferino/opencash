<?php

namespace App\Exports;

use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Support\CashLedger;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export riwayat kas satu siswa ke .xlsx.
 *
 * Dua blok: ringkasan sisa per tagihan, lalu daftar pembayaran (kapan & untuk
 * tagihan mana). Angka ditulis sebagai integer supaya di Excel tetap bisa
 * dijumlahkan, bukan teks.
 */
class StudentHistoryExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly \App\Models\User $student) {}

    public function title(): string
    {
        return 'Riwayat Kas';
    }

    public function headings(): array
    {
        return ['Tanggal Bayar', 'Tagihan', 'Jatuh Tempo', 'Metode', 'Nominal Kas', 'Denda', 'Total', 'Status', 'Dicatat Oleh', 'Catatan'];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];

        $rows[] = ['Ringkasan per tagihan'];
        $rows[] = ['Tagihan', 'Jatuh Tempo', 'Nominal', 'Terbayar', 'Menunggu', 'Sisa', 'Status'];
        $rows[] = ['', '', '', '', '', '', ''];

        foreach ($this->billRows() as $bill) {
            $rows[] = [
                $bill['description'],
                optional($bill['due_date'])->format('d/m/Y') ?? '-',
                $bill['amount'],
                $bill['paid'],
                $bill['pending'],
                $bill['remaining'],
                $this->statusLabel($bill['status']),
            ];
        }

        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['Daftar pembayaran'];
        $rows[] = $this->headings();
        $rows[] = ['', '', '', '', '', '', '', '', '', ''];

        foreach ($this->payments() as $payment) {
            $total = (int) $payment->amount_paid + (int) $payment->fine_paid;

            $rows[] = [
                optional($payment->income_date)->format('d/m/Y') ?? '-',
                $payment->cashSchedule?->description ?? '-',
                optional($payment->cashSchedule?->due_date)->format('d/m/Y') ?? '-',
                $payment->payment_method === 'qris' ? 'QRIS' : 'Tunai',
                (int) $payment->amount_paid,
                (int) $payment->fine_paid,
                $total,
                $this->paymentStatusLabel($payment->status),
                $payment->treasurer?->name ?? 'Mandiri (QRIS)',
                $payment->notes ?? '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true]],
            11 => ['font' => ['bold' => true, 'size' => 12]],
            12 => ['font' => ['bold' => true]],
        ];
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function billRows()
    {
        $schedules = CashSchedule::where('group_id', $this->student->group_id)
            ->orderByDesc('due_date')
            ->get();

        $incomes = CashIncome::where('student_id', $this->student->id)
            ->whereIn('status', ['verified', 'pending'])
            ->get();

        return CashLedger::billSummaries($schedules, $incomes);
    }

    /** @return \Illuminate\Support\Collection<int, CashIncome> */
    private function payments()
    {
        return CashIncome::where('student_id', $this->student->id)
            ->with(['cashSchedule:id,description,due_date', 'treasurer:id,name'])
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->get();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Lunas',
            'partial' => 'Kurang bayar',
            'pending' => 'Menunggu verifikasi',
            default => 'Belum bayar',
        };
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'verified' => 'Terverifikasi',
            'pending' => 'Menunggu verifikasi',
            default => 'Ditolak',
        };
    }
}
