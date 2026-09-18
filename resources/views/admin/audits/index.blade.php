@extends('layouts.panel')

@section('title', 'User Audit — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">User Audit</h1>
        <p class="mt-1 text-[15px] text-muted">Log aktivitas seluruh perubahan data — pengguna maupun grup. Setiap perubahan dari admin atau bendahara tercatat otomatis, termasuk perbandingan nilai sebelum dan sesudah.</p>
    </div>

    @include('admin.audits._table')
    @include('admin.audits._modal')
@endsection
