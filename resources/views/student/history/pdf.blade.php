<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat Kas — {{ $student->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 0; padding: 28px 32px; }
        h1 { font-size: 19px; margin: 0 0 2px; }
        h2 { font-size: 13px; margin: 22px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #d1d5db; }
        .muted { color: #6b7280; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .brand { font-size: 15px; font-weight: bold; }
        .brand span { color: #3e7bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; color: #4b5563; border-bottom: 1px solid #d1d5db; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .right { text-align: right; }
        .center { text-align: center; }
        .total-row td { font-weight: bold; background: #f9fafb; }
        .badge { display: inline-block; padding: 1px 7px; border-radius: 9px; font-size: 9px; font-weight: bold; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-unpaid { background: #fee2e2; color: #991b1b; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .summary { width: 100%; margin-top: 10px; }
        .summary td { border: 1px solid #e5e7eb; padding: 8px 10px; width: 25%; }
        .summary .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
        .summary .value { font-size: 13px; font-weight: bold; margin-top: 2px; }
        .footer { margin-top: 24px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">Open<span>Cash</span></div>
            <h1>Riwayat Kas</h1>
            <div class="muted">{{ $student->name }} &middot; {{ $group?->name ?? 'Tanpa kelas' }}</div>
        </div>
        <div class="muted right">
            Dicetak {{ $printedAt->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Total Terbayar</div>
                <div class="value">Rp{{ number_format($summary['total_paid'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Menunggu Verifikasi</div>
                <div class="value">Rp{{ number_format($summary['total_pending'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Sisa Kurang</div>
                <div class="value">Rp{{ number_format($bills->sum('remaining'), 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Jumlah Pembayaran</div>
                <div class="value">{{ $summary['payments_count'] }}</div>
            </td>
        </tr>
    </table>

    <h2>Sisa per tagihan</h2>
    <table>
        <thead>
            <tr>
                <th>Tagihan</th>
                <th>Jatuh Tempo</th>
                <th class="right">Nominal</th>
                <th class="right">Terbayar</th>
                <th class="right">Sisa</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bills as $bill)
                <tr>
                    <td>{{ $bill['description'] }}</td>
                    <td>{{ $bill['due_date']?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td class="right">Rp{{ number_format($bill['amount'], 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($bill['paid'], 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($bill['remaining'], 0, ',', '.') }}</td>
                    <td class="center">
                        @php
                            $billBadge = match ($bill['status']) {
                                'paid' => ['Lunas', 'badge-paid'],
                                'partial' => ['Kurang bayar', 'badge-partial'],
                                'pending' => ['Menunggu', 'badge-pending'],
                                default => ['Belum bayar', 'badge-unpaid'],
                            };
                        @endphp
                        <span class="badge {{ $billBadge[1] }}">{{ $billBadge[0] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="center muted">Belum ada tagihan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Daftar pembayaran</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tagihan</th>
                <th>Metode</th>
                <th class="right">Nominal</th>
                <th class="right">Denda</th>
                <th class="center">Status</th>
                <th>Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->income_date?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td>{{ $payment->cashSchedule?->description ?? '-' }}</td>
                    <td>{{ $payment->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</td>
                    <td class="right">Rp{{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($payment->fine_paid, 0, ',', '.') }}</td>
                    <td class="center">
                        @php
                            $payBadge = match ($payment->status) {
                                'verified' => ['Terverifikasi', 'badge-paid'],
                                'pending' => ['Menunggu', 'badge-pending'],
                                default => ['Ditolak', 'badge-rejected'],
                            };
                        @endphp
                        <span class="badge {{ $payBadge[1] }}">{{ $payBadge[0] }}</span>
                    </td>
                    <td>{{ $payment->treasurer?->name ?? 'Mandiri (QRIS)' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="center muted">Belum ada pembayaran tercatat.</td></tr>
            @endforelse
            @if ($payments->isNotEmpty())
                <tr class="total-row">
                    <td colspan="3">Total terverifikasi</td>
                    <td class="right">Rp{{ number_format($summary['total_paid'], 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh OpenCash — {{ config('app.name') }}
    </div>
</body>
</html>
