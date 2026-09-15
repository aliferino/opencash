@extends('layouts.panel')

@section('title', 'User Audit — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">User Audit</h1>
        <p class="mt-1 text-[15px] text-muted">Log aktivitas perubahan data pengguna (role, nama, email). Halaman ini hanya untuk memantau — tidak ada aksi ubah/hapus.</p>
    </div>

    @include('admin.audits._table')
    @include('admin.audits._modal')
@endsection
