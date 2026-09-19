@extends('layouts.panel')

@section('title', 'Tagihan Saya — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Tagihan Saya</h1>
        <p class="mt-1 text-[15px] text-muted">Rincian tagihan kas kelasmu — termasuk sisa yang belum dibayar.</p>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="receipt" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Tagihan</p>
            <p class="mt-1 text-2xl font-semibold text-ink">Rp{{ number_format($summary['total_billed'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                <i data-lucide="check-circle-2" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Sudah Dibayar</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-400">Rp{{ number_format($summary['total_paid'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-500/10 text-red-400">
                <i data-lucide="badge-alert" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Sisa Kurang</p>
            <p class="mt-1 text-2xl font-semibold text-red-400">Rp{{ number_format($summary['total_remaining'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                <i data-lucide="hourglass" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Menunggu Verifikasi</p>
            <p class="mt-1 text-2xl font-semibold text-amber-400">Rp{{ number_format($summary['total_pending'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3 text-[13px] text-muted">
        <span class="inline-flex items-center gap-1.5">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            {{ $summary['paid_count'] }} lunas
        </span>
        <span class="text-line">|</span>
        <span class="inline-flex items-center gap-1.5">
            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
            {{ $summary['partial_count'] }} kurang bayar
        </span>
        <span class="text-line">|</span>
        <span class="inline-flex items-center gap-1.5">
            <span class="h-2 w-2 rounded-full bg-red-400"></span>
            {{ $summary['unpaid_count'] }} belum bayar
        </span>
    </div>

    @include('student.bills._table')
    @include('student.bills._modal')
@endsection
