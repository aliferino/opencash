@extends('layouts.panel')

@section('title', 'Pengguna — OpenCash')

@section('panel')
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Pengguna</h1>
            <p class="mt-1 text-[15px] text-muted">Semua akun admin, bendahara, dan siswa di OpenCash.</p>
        </div>

        <button
            type="button"
            id="user-create-open"
            class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
            Tambah Pengguna
        </button>
    </div>

    @include('admin.users._table')
    @include('admin.users._modal')
@endsection