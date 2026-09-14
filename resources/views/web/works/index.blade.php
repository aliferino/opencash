@extends('layouts.web')

@section('title', 'Cara Kerja — OpenCash')
@section('meta_description', 'Lihat langkah demi langkah bagaimana bendahara dan siswa memakai OpenCash untuk mencatat, membayar, dan memverifikasi kas kelas.')

@section('web')

    <section class="mx-auto max-w-6xl px-6 pt-16 pb-6 md:pt-24">
        <span class="rounded-full bg-accent-tint px-3 py-1 text-sm font-medium text-accent-bright">Cara kerja</span>
        <h1 class="mt-5 max-w-xl text-4xl font-semibold leading-[1.1] tracking-tight text-ink md:text-5xl">
            Dari daftar kelas sampai kas terverifikasi.
        </h1>
        <p class="mt-5 max-w-md text-[17px] leading-relaxed text-muted">
            Tiga langkah sederhana yang dipakai bendahara dan siswa setiap kali ada iuran masuk.
        </p>
    </section>

    <section class="mx-auto max-w-6xl px-6 py-16">
        <div class="space-y-10">
            <div class="grid gap-2 md:grid-cols-[80px_1fr] md:gap-8">
                <span class="font-mono text-lg text-accent-bright">01</span>
                <div>
                    <p class="text-lg font-medium text-ink">Bendahara membuat kelas</p>
                    <p class="mt-1.5 max-w-xl text-[15px] leading-relaxed text-muted">
                        Bendahara mendaftar, membuat kelas, lalu mengatur jadwal dan nominal iuran sekali di awal.
                    </p>
                </div>
            </div>
            <div class="grid gap-2 md:grid-cols-[80px_1fr] md:gap-8">
                <span class="font-mono text-lg text-accent-bright">02</span>
                <div>
                    <p class="text-lg font-medium text-ink">Siswa bergabung</p>
                    <p class="mt-1.5 max-w-xl text-[15px] leading-relaxed text-muted">
                        Siswa masuk memakai kode undangan yang dibagikan bendahara, tanpa perlu persetujuan satu per satu.
                    </p>
                </div>
            </div>
            <div class="grid gap-2 md:grid-cols-[80px_1fr] md:gap-8">
                <span class="font-mono text-lg text-accent-bright">03</span>
                <div>
                    <p class="text-lg font-medium text-ink">Bayar dan terverifikasi</p>
                    <p class="mt-1.5 max-w-xl text-[15px] leading-relaxed text-muted">
                        Siswa bayar tunai atau QRIS, bendahara memverifikasi, dan kas langsung tercatat untuk semua orang.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="px-6 py-24 text-center">
        <h2 class="text-3xl font-semibold tracking-tight text-ink">Siap merapikan kas kelasmu?</h2>
        <p class="mx-auto mt-3 max-w-md text-[15px] text-muted">Buat kelas dalam hitungan menit. Gratis untuk digunakan.</p>
        <a
            href="{{ route('register') }}"
            class="mt-7 inline-block rounded-md bg-accent px-7 py-3 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            Daftar gratis
        </a>
    </section>

@endsection