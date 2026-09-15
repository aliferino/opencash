@extends('layouts.panel')

@section('title', 'Periode — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Periode</h1>
        <p class="mt-1 text-[15px] text-muted">Kelola referensi periode kas (mis. Mingguan, Bulanan) yang dipakai semua kelas.</p>
    </div>

    @include('admin.periods._table')
    @include('admin.periods._modal')
@endsection
