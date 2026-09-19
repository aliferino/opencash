@extends('layouts.panel')

@section('title', 'Pemasukan Kas — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Pemasukan Kas</h1>
        <p class="mt-1 text-[15px] text-muted">Catat pembayaran tunai dan verifikasi bukti transfer QRIS dari siswa.</p>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                <i data-lucide="badge-alert" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Menunggu Verifikasi</p>
            <p class="mt-1 text-3xl font-semibold text-amber-400">{{ $summary['pending'] }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                <i data-lucide="check-circle-2" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Terverifikasi</p>
            <p class="mt-1 text-3xl font-semibold text-emerald-400">{{ $summary['verified'] }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-500/10 text-red-400">
                <i data-lucide="x-circle" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Ditolak</p>
            <p class="mt-1 text-3xl font-semibold text-red-400">{{ $summary['rejected'] }}</p>
        </div>

        <div class="rounded-2xl border border-accent/40 bg-accent-tint p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent text-white">
                <i data-lucide="wallet" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Diterima</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($summary['total_verified'], 0, ',', '.') }}</p>
        </div>
    </div>

    @include('treasurer.incomes._table')
    @include('treasurer.incomes._modal')
    @include('treasurer.incomes._import')
@endsection
