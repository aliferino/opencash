@extends('layouts.panel')

@section('title', 'Grup — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Grup</h1>
        <p class="mt-1 text-[15px] text-muted">Semua kas grup yang terdaftar di OpenCash.</p>
    </div>

    @include('admin.groups._table')
    @include('admin.groups._modal')
@endsection