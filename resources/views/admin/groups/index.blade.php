@extends('layouts.panel')

@section('title', 'Kelas — OpenCash')

@section('panel')
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Kelas</h1>
            <p class="mt-1 text-[15px] text-muted">Semua kas kelas yang terdaftar di OpenCash.</p>
        </div>

        <button
            type="button"
            id="group-create-open"
            class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
            Tambah Kelas
        </button>
    </div>

    @include('admin.groups._table')
    @include('admin.groups._modal')
@endsection