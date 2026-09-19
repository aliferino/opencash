@extends('layouts.panel')

@section('title', 'Riwayat Kas — OpenCash')

@section('panel')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Riwayat</h1>
            <p class="mt-1 text-[15px] text-muted">Kapan kamu bayar, untuk tagihan apa, dan berapa sisanya.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('student.history.export.excel') }}"
               class="inline-flex items-center gap-2 rounded-md border border-line bg-surface px-4 py-2.5 text-[14px] font-medium text-ink transition-colors hover:border-accent hover:text-accent-bright">
                <i data-lucide="file-spreadsheet" class="h-4 w-4" stroke-width="1.8"></i>
                Excel
            </a>
            <a href="{{ route('student.history.export.pdf') }}"
               class="inline-flex items-center gap-2 rounded-md border border-line bg-surface px-4 py-2.5 text-[14px] font-medium text-ink transition-colors hover:border-accent hover:text-accent-bright">
                <i data-lucide="file-text" class="h-4 w-4" stroke-width="1.8"></i>
                PDF
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                <i data-lucide="wallet" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Terbayar</p>
            <p class="mt-1 text-2xl font-semibold text-ink">Rp{{ number_format($summary['total_paid'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                <i data-lucide="hourglass" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Menunggu Verifikasi</p>
            <p class="mt-1 text-2xl font-semibold text-ink">Rp{{ number_format($summary['total_pending'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="list-checks" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Jumlah Pembayaran</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ $summary['payments_count'] }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="split" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Tunai / QRIS</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ $summary['cash_count'] }} <span class="text-muted">/</span> {{ $summary['qris_count'] }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-2xl border border-line bg-surface p-6">
        <h2 class="text-lg font-semibold text-ink">Sisa per tagihan</h2>
        <p class="mt-1 text-[13px] text-muted">Kalau kamu bayar sebagian, sisanya tetap muncul di sini.</p>

        <div class="mt-5 space-y-3">
            @forelse ($bills as $bill)
                <div class="rounded-xl border border-line bg-bg px-4 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-[14px] font-medium text-ink">{{ $bill['description'] }}</p>
                            <p class="text-xs text-muted">Jatuh tempo {{ $bill['due_date']?->translatedFormat('d M Y') ?? '—' }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-[13px] text-muted">
                                Rp{{ number_format($bill['paid'], 0, ',', '.') }} / Rp{{ number_format($bill['amount'], 0, ',', '.') }}
                            </span>

                            @if ($bill['status'] === 'paid')
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-medium text-emerald-300">Lunas</span>
                            @elseif ($bill['status'] === 'partial')
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-medium text-amber-300">Kurang Rp{{ number_format($bill['remaining'], 0, ',', '.') }}</span>
                            @elseif ($bill['status'] === 'pending')
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-medium text-amber-300">Menunggu verifikasi</span>
                            @else
                                <span class="rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-medium text-red-300">Belum bayar</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-white/8">
                        @php
                            $percent = $bill['amount'] > 0 ? min(100, round($bill['paid'] / $bill['amount'] * 100)) : 0;
                        @endphp
                        <span class="block h-full rounded-full {{ $bill['status'] === 'paid' ? 'bg-emerald-400' : 'bg-accent' }}" style="width: {{ $percent }}%"></span>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-[14px] text-muted">Belum ada tagihan dari bendahara.</p>
            @endforelse
        </div>
    </div>

    @include('student.history._table')
@endsection
