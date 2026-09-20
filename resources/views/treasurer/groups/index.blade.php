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

    {{-- Grid konten: 3 kartu statistik + kartu QRIS dalam SATU baris 4 kolom,
         supaya tidak ada ruang kosong menganggur di bawah kartu statistik. --}}
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-accent/40 bg-accent-tint p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent text-white">
                <i data-lucide="users" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Anggota</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $group->students_count + $group->treasurers_count }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                <i data-lucide="graduation-cap" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Siswa</p>
            <p class="mt-1 text-3xl font-semibold text-emerald-400">{{ $group->students_count }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                <i data-lucide="user-cog" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Bendahara</p>
            <p class="mt-1 text-3xl font-semibold text-amber-400">{{ $group->treasurers_count }}</p>
        </div>

        {{-- Kartu QRIS. Tanpa pratinjau gambar — hanya teks status + tombol
             Lihat (buka modal viewer) dan Simpan (unduh ke perangkat).
             Mengunggah/mengganti gambar dilakukan dari tombol "Detail Grup". --}}
        <div
            id="qris-card"
            class="flex flex-col rounded-2xl border border-line bg-surface p-6"
            data-qris-url="{{ $group->qris_image ? Storage::disk('public')->url($group->qris_image) : '' }}"
        >
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                <i data-lucide="qr-code" class="h-4 w-4" stroke-width="1.8"></i>
            </span>

            <p class="mt-4 text-[13px] text-muted">QRIS Pembayaran</p>

            <p id="qris-card-status" class="mt-2 text-[12.5px] leading-snug text-muted">
                {{ $group->qris_image
                    ? 'Gambar QRIS kelas sudah tersedia.'
                    : 'Belum ada gambar QRIS. Tambahkan lewat Detail Grup.' }}
            </p>

            <div id="qris-card-actions" class="{{ $group->qris_image ? '' : 'hidden' }} mt-auto flex flex-wrap gap-2 pt-4">
                <button
                    type="button"
                    id="qris-open"
                    class="inline-flex items-center gap-1.5 rounded-md border border-line px-3 py-2 text-[12.5px] font-medium text-muted transition-colors hover:border-accent hover:text-accent-bright"
                >
                    <i data-lucide="maximize-2" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                    Lihat
                </button>

                <button
                    type="button"
                    id="qris-download-card"
                    class="inline-flex items-center gap-1.5 rounded-md border border-line px-3 py-2 text-[12.5px] font-medium text-muted transition-colors hover:border-accent hover:text-accent-bright"
                    title="Simpan gambar QRIS ke perangkat"
                >
                    <i data-lucide="download" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                    Simpan
                </button>
            </div>
        </div>
    </div>

    @include('treasurer.groups._table')
    @include('treasurer.groups._modal')
    @include('treasurer.groups._qris-viewer')
@endsection
