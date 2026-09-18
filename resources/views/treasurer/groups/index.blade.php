@extends('layouts.panel')

@section('title', 'Grup — OpenCash')

@section('panel')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Grup</h1>
            <p class="mt-1 text-[15px] text-muted">Kelola informasi dan anggota kelas <span id="group-name-label" class="font-medium text-ink">{{ $group->name }}</span>.</p>
        </div>

        <button
            type="button"
            id="group-detail-open"
            class="flex shrink-0 items-center gap-2 rounded-md border border-line px-4 py-2.5 text-[14px] font-medium text-ink transition-colors hover:bg-white/5"
        >
            <i data-lucide="settings-2" class="h-4 w-4" stroke-width="1.8"></i>
            Detail Grup
        </button>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-3">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="users" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Anggota</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $group->students_count + $group->treasurers_count }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="graduation-cap" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Siswa</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $group->students_count }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="user-cog" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Bendahara</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $group->treasurers_count }}</p>
        </div>
    </div>

    @include('treasurer.groups._table')
    @include('treasurer.groups._modal')
@endsection
