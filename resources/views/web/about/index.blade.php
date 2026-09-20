@extends('layouts.web')

@section('title', 'Tentang OpenCash')
@section('meta_description', 'Kenapa OpenCash dibuat dan apa saja yang bisa dilakukan bendahara serta siswa di dalamnya.')

@section('web')

    {{-- Intro --}}
    <section class="mx-auto max-w-6xl px-6 pt-16 pb-20 md:pt-24">
        <div class="max-w-2xl">
            <h1 class="web-rise web-rise-1 text-4xl font-semibold leading-[1.1] tracking-tight text-ink md:text-5xl">
                Kas kelas sering berantakan bukan karena bendaharanya malas.
            </h1>
            <p class="web-rise web-rise-2 mt-6 text-[17px] leading-relaxed text-muted">
                Uangnya dicatat di buku tulis, bukti transfer numpuk di chat WhatsApp, dan siswa cuma bisa percaya laporan lisan di akhir semester. OpenCash dibuat supaya satu kelas bisa mencatat, memverifikasi, dan melihat kas yang sama — secara real-time, tanpa harus jadi ahli spreadsheet dulu.
            </p>
        </div>
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="mx-auto max-w-6xl px-6 py-20">
        <h2 class="max-w-md text-3xl font-semibold tracking-tight text-ink">Yang bisa dilakukan di OpenCash</h2>

        <div class="mt-12 grid gap-x-10 gap-y-10 md:grid-cols-2">
            <div class="flex gap-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                    <svg viewBox="0 0 20 20" fill="none" class="h-4.5 w-4.5" aria-hidden="true"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="font-medium text-ink">Pencatatan otomatis</p>
                    <p class="mt-1.5 text-[15px] leading-relaxed text-muted">Setiap iuran dan pengeluaran tercatat rapi, tidak perlu rekap manual di buku atau spreadsheet.</p>
                </div>
            </div>
            <div class="flex gap-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                    <svg viewBox="0 0 20 20" fill="none" class="h-4.5 w-4.5" aria-hidden="true"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="font-medium text-ink">Verifikasi pembayaran QRIS</p>
                    <p class="mt-1.5 text-[15px] leading-relaxed text-muted">Siswa unggah bukti bayar, bendahara tinggal cek mutasi dan konfirmasi dalam sekali klik.</p>
                </div>
            </div>
            <div class="flex gap-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                    <svg viewBox="0 0 20 20" fill="none" class="h-4.5 w-4.5" aria-hidden="true"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="font-medium text-ink">Tagihan boleh dicicil</p>
                    <p class="mt-1.5 text-[15px] leading-relaxed text-muted">Satu tagihan bisa dibayar bertahap. Sisanya tetap terlihat jelas sebagai kekurangan, bukan dianggap lunas.</p>
                </div>
            </div>
            <div class="flex gap-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                    <svg viewBox="0 0 20 20" fill="none" class="h-4.5 w-4.5" aria-hidden="true"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="font-medium text-ink">Laporan yang terbuka</p>
                    <p class="mt-1.5 text-[15px] leading-relaxed text-muted">Semua siswa bisa melihat saldo dan riwayat kas kapan saja, tanpa harus bertanya ke bendahara.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="px-6 py-20 text-center">
        <h2 class="text-3xl font-semibold tracking-tight text-ink">Coba buat kelasmu sendiri</h2>
        <p class="mx-auto mt-3 max-w-md text-[15px] text-muted">Gratis, dan bisa langsung dipakai hari ini.</p>
        <a
            href="{{ route('register') }}"
            class="mt-7 inline-block rounded-md bg-accent px-7 py-3 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            Daftar gratis
        </a>
    </section>

@endsection