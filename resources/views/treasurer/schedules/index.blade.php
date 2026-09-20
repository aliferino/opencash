@extends('layouts.panel')

@section('title', 'Jadwal Tagihan — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Jadwal Tagihan</h1>
        <p class="mt-1 text-[15px] text-muted">Kelola jadwal tagihan kas yang harus dibayar siswa.</p>
    </div>

    @include('treasurer.schedules._table')
    @include('treasurer.schedules._modal')
    @include('treasurer.schedules._import')
@endsection
