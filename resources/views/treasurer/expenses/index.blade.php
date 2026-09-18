@extends('layouts.panel')

@section('title', 'Pengeluaran Kas — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Pengeluaran Kas</h1>
        <p class="mt-1 text-[15px] text-muted">Catat pengeluaran kas kelas beserta foto nota sebagai bukti.</p>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="receipt" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Jumlah Pengeluaran</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $summary['count'] }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="arrow-up-circle" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Pengeluaran</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($summary['total'], 0, ',', '.') }}</p>
        </div>
    </div>

    @include('treasurer.expenses._table')
    @include('treasurer.expenses._modal')
@endsection
