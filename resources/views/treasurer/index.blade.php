@extends('layouts.panel')

@section('title', 'Dashboard Bendahara — OpenCash')

@section('panel')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Dashboard</h1>
        <p class="mt-1 text-[15px] text-muted">Ringkasan kas kelas Anda.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="wallet" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Saldo Kas</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($balance, 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="badge-alert" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Menunggu Verifikasi</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $pendingCount }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="calendar-clock" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Tagihan Mendatang</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $upcomingSchedules->count() }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Menunggu verifikasi</h2>
                <a href="{{ route('cash-incomes.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-1">
                @forelse ($pendingIncomes as $income)
                    <div class="flex items-center justify-between rounded-md px-3 py-3 transition-colors hover:bg-white/5">
                        <div class="min-w-0">
                            <p class="truncate text-[15px] font-medium text-ink">{{ $income->student?->name }}</p>
                            <p class="truncate text-xs text-muted">{{ $income->cashSchedule?->description }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">Rp{{ number_format($income->amount_paid + $income->fine_paid, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Tidak ada pembayaran menunggu verifikasi.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Tagihan mendatang</h2>
                <a href="{{ route('cash-schedules.index') }}" class="text-[14px] font-medium text-accent-bright transition-colors hover:text-accent">Lihat semua →</a>
            </div>

            <div class="mt-5 space-y-4">
                @forelse ($upcomingSchedules as $schedule)
                    <div class="flex gap-3">
                        <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                            <i data-lucide="calendar" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-[14px] font-medium text-ink">{{ $schedule->description }}</p>
                                <span class="shrink-0 text-[14px] text-ink">Rp{{ number_format($schedule->amount, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-muted">Jatuh tempo {{ $schedule->due_date?->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-[14px] text-muted">Belum ada tagihan mendatang.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection