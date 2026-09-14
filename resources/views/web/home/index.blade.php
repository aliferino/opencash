@extends('layouts.web')

@section('title', 'OpenCash — Kas kelas yang rapi, tanpa drama')
@section('meta_description', 'OpenCash membantu bendahara mencatat iuran, memverifikasi pembayaran QRIS, dan melaporkan saldo kas kelas secara transparan.')

@section('web')

    {{-- Hero --}}
    <section class="mx-auto max-w-6xl px-6 pt-16 pb-24 md:pt-24 md:pb-32">
        <div class="grid items-center gap-16 md:grid-cols-2">
            <div>
                <h1 class="text-5xl font-semibold leading-[1.05] tracking-tight text-ink md:text-6xl">
                    Kas kelas yang rapi, <span class="text-accent-bright">tanpa drama</span>.
                </h1>
                <p class="mt-6 max-w-md text-[17px] leading-relaxed text-muted">
                    OpenCash membantu bendahara mencatat iuran, memverifikasi pembayaran, dan melaporkan saldo kas — semua transparan dan bisa dilihat seluruh siswa kapan saja.
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-6">
                    <a
                        href="{{ route('register') }}"
                        class="rounded-md bg-accent px-6 py-3 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
                    >
                        Daftar sekarang
                    </a>
                    <a href="{{ route('login') }}" class="text-[15px] font-medium text-ink transition-colors hover:text-accent-bright">
                        Sudah punya akun? Masuk
                    </a>
                </div>
            </div>

            {{-- Stacked ledger cards --}}
            <div class="relative flex justify-center md:justify-end">
                <div class="pointer-events-none absolute -right-6 -top-10 h-72 w-72 rounded-full bg-accent/30 blur-[90px]"></div>
                <div class="pointer-events-none absolute -left-4 bottom-0 h-40 w-40 rounded-full bg-accent/20 blur-[70px]"></div>

                <div class="relative h-[360px] w-full max-w-sm">
                    {{-- back card 2 --}}
                    <div class="absolute inset-x-10 top-16 h-[280px] -rotate-6 rounded-2xl bg-surface/50 p-6 opacity-60 blur-[0.5px]">
                        <div class="h-2 w-2/3 rounded-full bg-white/10"></div>
                        <div class="mt-4 h-2 w-1/2 rounded-full bg-white/10"></div>
                        <div class="mt-4 h-2 w-3/5 rounded-full bg-white/10"></div>
                    </div>

                    {{-- back card 1 --}}
                    <div class="absolute inset-x-5 top-8 h-[300px] -rotate-3 rounded-2xl bg-surface/75 p-6 opacity-80">
                        <div class="h-2 w-2/3 rounded-full bg-white/10"></div>
                        <div class="mt-4 h-2 w-1/2 rounded-full bg-white/10"></div>
                        <div class="mt-4 h-2 w-3/5 rounded-full bg-white/10"></div>
                        <div class="mt-4 h-2 w-2/5 rounded-full bg-white/10"></div>
                    </div>

                    {{-- front card --}}
                    <div class="absolute inset-0 overflow-hidden rounded-2xl bg-surface shadow-[0_50px_100px_-30px_rgba(62,123,255,0.55)]">
                        <div class="h-1.5 bg-gradient-to-r from-accent to-accent-bright"></div>

                        <div class="p-6">
                            <div class="flex items-center justify-between pb-4">
                                <div>
                                    <p class="text-sm text-muted">Kas kelas</p>
                                    <p class="font-semibold text-ink">9B &middot; Semester Ganjil</p>
                                </div>
                                <span class="rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">Aktif</span>
                            </div>

                            <ul class="divide-y divide-line text-[15px]">
                                <li class="flex items-center justify-between py-3.5">
                                    <span class="text-ink">Bayu Saputra</span>
                                    <span class="flex items-center gap-1.5 font-mono text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-3.5 w-3.5"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Rp15.000
                                    </span>
                                </li>
                                <li class="flex items-center justify-between py-3.5">
                                    <span class="text-ink">Citra Putri</span>
                                    <span class="flex items-center gap-1.5 font-mono text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-3.5 w-3.5"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Rp15.000
                                    </span>
                                </li>
                                <li class="flex items-center justify-between py-3.5">
                                    <span class="text-ink">Dimas Ardiansyah</span>
                                    <span class="font-mono text-muted">Menunggu</span>
                                </li>
                            </ul>

                            <div class="mt-1 flex items-center justify-between pt-4">
                                <span class="text-sm text-muted">Saldo kas saat ini</span>
                                <span class="font-mono text-[17px] font-semibold text-ink">Rp1.240.000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cara kerja --}}
    <section id="cara-kerja" class="mx-auto max-w-6xl px-6 py-24">
        <h2 class="max-w-md text-3xl font-semibold tracking-tight text-ink">Cara kerja</h2>

        <div class="mt-12 space-y-10">
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