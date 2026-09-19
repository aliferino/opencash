<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kas — {{ $group?->name ?? 'Kelas' }}</title>
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
        .summary { width: 100%; margin-top: 10px; }
        .summary td { border: 1px solid #e5e7eb; padding: 8px 10px; }
        .summary .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
        .summary .value { font-size: 13px; font-weight: bold; margin-top: 2px; }
        .footer { margin-top: 24px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">Open<span>Cash</span></div>
            <h1>Laporan Kas</h1>
            <div class="muted">{{ $group?->name ?? 'Tanpa kelas' }} &middot; {{ $scopeLabel }}</div>
        </div>
        <div class="muted right">
            Dicetak {{ $printedAt->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    @php
        $incomeTotal = (int) $incomes->sum(fn ($i) => (int) $i->amount_paid + (int) $i->fine_paid);
        $expenseTotal = (int) $expenses->sum('amount');
    @endphp

    <table class="summary">
        <tr>
            @if ($scope !== 'expense')
                <td>
                    <div class="label">Total Pemasukan</div>
                    <div class="value">Rp{{ number_format($incomeTotal, 0, ',', '.') }}</div>
                </td>
            @endif
            @if ($scope !== 'income')
                <td>
                    <div class="label">Total Pengeluaran</div>
                    <div class="value">Rp{{ number_format($expenseTotal, 0, ',', '.') }}</div>
                </td>
            @endif
            @if ($scope === 'all')
                <td>
                    <div class="label">Saldo Kas</div>
                    <div class="value">Rp{{ number_format($incomeTotal - $expenseTotal, 0, ',', '.') }}</div>
                </td>
            @endif
        </tr>
    </table>

    @if ($scope !== 'expense')
        <h2>Pemasukan (terverifikasi)</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Tagihan</th>
                    <th>Metode</th>
                    <th class="right">Nominal</th>
                    <th class="right">Denda</th>
                    <th>Dicatat Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incomes as $income)
                    <tr>
                        <td>{{ $income->income_date?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>{{ $income->student?->name ?? '-' }}</td>
                        <td>{{ $income->cashSchedule?->description ?? '-' }}</td>
                        <td>{{ $income->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</td>
                        <td class="right">Rp{{ number_format($income->amount_paid, 0, ',', '.') }}</td>
                        <td class="right">Rp{{ number_format($income->fine_paid, 0, ',', '.') }}</td>
                        <td>{{ $income->treasurer?->name ?? 'Mandiri (QRIS)' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="center muted">Belum ada pemasukan terverifikasi.</td></tr>
                @endforelse
                @if ($incomes->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="4">Total Pemasukan</td>
                        <td class="right" colspan="3">Rp{{ number_format($incomeTotal, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    @if ($scope !== 'income')
        <h2>Pengeluaran</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Dicatat Oleh</th>
                    <th class="right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>{{ $expense->description }}</td>
                        <td>{{ $expense->treasurer?->name ?? '-' }}</td>
                        <td class="right">Rp{{ number_format($expense->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="center muted">Belum ada pengeluaran.</td></tr>
                @endforelse
                @if ($expenses->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="3">Total Pengeluaran</td>
                        <td class="right">Rp{{ number_format($expenseTotal, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dokumen ini dibuat otomatis oleh OpenCash — {{ config('app.name') }}
    </div>
</body>
</html>
