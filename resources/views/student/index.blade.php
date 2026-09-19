@extends('layouts.panel')

@section('title', 'Dashboard Siswa — OpenCash')

@section('panel')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Dashboard</h1>
        <p class="mt-1 text-[15px] text-muted">Ringkasan tagihan dan kas kelasmu.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                <i data-lucide="wallet" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Terbayar</p>
            <p class="mt-1 text-2xl font-semibold text-ink">Rp{{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-500/10 text-red-400">
                <i data-lucide="badge-alert" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Sisa Kurang</p>
            <p class="mt-1 text-2xl font-semibold text-red-400">Rp{{ number_format($totalRemaining, 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="calendar" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Tagihan Belum Lunas</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ $unpaidCount }}</p>
        </div>

        <div class="rounded-2xl border border-accent/40 bg-accent-tint p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent text-white">
                <i data-lucide="landmark" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Saldo Kas Kelas</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ \App\Support\CashLedger::rupiah($balance['balance']) }}</p>
        </div>
    </div>

    @if ($pendingTotal > 0)
        <div class="mt-5 flex items-start gap-3 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-[13.5px] text-amber-200">
            <i data-lucide="hourglass" class="mt-0.5 h-4 w-4 shrink-0" stroke-width="1.8"></i>
            <span>
                Ada <strong>Rp{{ number_format($pendingTotal, 0, ',', '.') }}</strong> pembayaranmu yang masih menunggu verifikasi bendahara.
                Angka itu belum dihitung sebagai terbayar.
            </span>
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Tagihan belum lunas</h2>
                <a href="{{ route('student.bills.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-1">
                @forelse ($unpaidBills as $bill)
                    <div class="flex items-center justify-between rounded-md px-3 py-3 transition-colors hover:bg-white/5">
                        <div class="min-w-0">
                            <p class="truncate text-[15px] font-medium text-ink">{{ $bill['description'] }}</p>
                            <p class="text-xs text-muted">
                                Jatuh tempo {{ $bill['due_date']?->translatedFormat('d M Y') ?? '—' }}
                                @if ($bill['paid'] > 0)
                                    &middot; sudah bayar Rp{{ number_format($bill['paid'], 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400">
                            Sisa Rp{{ number_format($bill['remaining'], 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Semua tagihan sudah lunas.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Pengeluaran kas terbaru</h2>
                <a href="{{ route('student.cash.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-1">
                @forelse ($recentExpenses as $expense)
                    <div class="flex items-center justify-between gap-3 rounded-md px-3 py-3 transition-colors hover:bg-white/5">
                        <div class="min-w-0">
                            <p class="truncate text-[14px] font-medium text-ink">{{ $expense->description }}</p>
                            <p class="text-xs text-muted">
                                {{ $expense->expense_date?->translatedFormat('d M Y') ?? '—' }}
                                &middot; {{ $expense->treasurer?->name ?? '—' }}
                            </p>
                        </div>
                        <span class="shrink-0 text-[14px] font-medium text-red-400">Rp{{ number_format($expense->amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Belum ada pengeluaran kas.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
