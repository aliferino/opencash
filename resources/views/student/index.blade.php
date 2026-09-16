@extends('layouts.panel')

@section('title', 'Dashboard Siswa — OpenCash')

@section('panel')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Dashboard</h1>
        <p class="mt-1 text-[15px] text-muted">Ringkasan tagihan kas Anda.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="wallet" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Terbayar</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="badge-alert" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Belum Dibayar</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $unpaidCount }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="calendar" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Tagihan</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $schedules->count() }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Belum dibayar</h2>
                <a href="{{ route('student.bills.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-1">
                @forelse ($unpaidSchedules as $schedule)
                    <div class="flex items-center justify-between rounded-md px-3 py-3 transition-colors hover:bg-white/5">
                        <div class="min-w-0">
                            <p class="truncate text-[15px] font-medium text-ink">{{ $schedule->description }}</p>
                            <p class="text-xs text-muted">Jatuh tempo {{ $schedule->due_date?->translatedFormat('d M Y') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400">Rp{{ number_format($schedule->amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Semua tagihan sudah dibayar.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Jadwal kas</h2>
                <a href="{{ route('cash-schedules.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-4">
                @forelse ($schedules as $schedule)
                    <div class="flex gap-3">
                        <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                            <i data-lucide="calendar" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-[14px] font-medium text-ink">{{ $schedule->description }}</p>
                                @if ($schedule->is_paid)
                                    <span class="shrink-0 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400">Lunas</span>
                                @elseif ($schedule->is_pending)
                                    <span class="shrink-0 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-medium text-amber-400">Menunggu</span>
                                @else
                                    <span class="shrink-0 rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-medium text-red-400">Belum bayar</span>
                                @endif
                            </div>
                            <p class="text-xs text-muted">Jatuh tempo {{ $schedule->due_date?->translatedFormat('d M Y') }} &middot; Rp{{ number_format($schedule->amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Belum ada jadwal kas.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection