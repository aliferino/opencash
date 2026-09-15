@extends('layouts.panel')

@section('title', 'Pengguna — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Pengguna</h1>
        <p class="mt-1 text-[15px] text-muted">Semua akun admin, bendahara, dan siswa di OpenCash.</p>
    </div>

    @include('admin.users._table')
    @include('admin.users._modal')
@endsection