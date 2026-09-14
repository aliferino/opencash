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

            {{-- Stacked feature card --}}
            <div class="relative flex justify-center md:justify-end">
                <div class="pointer-events-none absolute -right-10 -top-14 h-80 w-80 rounded-full bg-accent/30 blur-[110px]"></div>
                <div class="pointer-events-none absolute -left-8 bottom-0 h-52 w-52 rounded-full bg-accent/20 blur-[90px]"></div>

                <div class="relative h-[340px] w-full max-w-sm">
                    {{-- back sheet 2 --}}
                    <div class="absolute inset-x-8 top-10 h-full -rotate-6 rounded-2xl bg-ink/20"></div>

                    {{-- back sheet 1 --}}
                    <div class="absolute inset-x-4 top-5 h-full -rotate-3 rounded-2xl bg-ink/60"></div>

                    {{-- front card --}}
                    <div class="absolute inset-0 overflow-hidden rounded-2xl bg-surface shadow-[0_60px_160px_-20px_rgba(62,123,255,0.6)]">

                        <div class="flex h-full flex-col p-6">
                            <span class="w-fit rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">Fitur utama</span>

                            <p class="mt-4 text-xl font-semibold leading-snug text-ink">
                                Semua kas kelas, tercatat dan terverifikasi otomatis.
                            </p>

                            <ul class="mt-6 flex-1 space-y-4 text-[15px]">
                                <li class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-4 w-4"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="text-ink">Pencatatan iuran otomatis</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-4 w-4"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="text-ink">Verifikasi pembayaran tunai &amp; QRIS</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-4 w-4"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="text-ink">Laporan saldo real-time</span>
                                </li>
                            </ul>

                            <div class="flex items-center justify-between border-t border-line pt-4">
                                <span class="text-sm text-muted">Bisa dilihat</span>
                                <span class="text-[15px] font-semibold text-ink">Seluruh siswa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cara kerja (quick start) --}}
    <section class="mx-auto max-w-6xl px-6 py-24">
        <div class="flex items-end justify-between gap-6">
            <h2 class="text-3xl font-semibold tracking-tight text-ink">Cara kerja</h2>
            <a href="{{ route('works') }}" class="shrink-0 text-[15px] font-medium text-accent-bright transition-colors hover:text-accent">
                Selengkapnya →
            </a>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-3">
            <div>
                <span class="font-mono text-lg text-accent-bright">01</span>
                <p class="mt-2 text-lg font-medium text-ink">Bendahara membuat kelas</p>
            </div>
            <div>
                <span class="font-mono text-lg text-accent-bright">02</span>
                <p class="mt-2 text-lg font-medium text-ink">Siswa bergabung</p>
            </div>
            <div>
                <span class="font-mono text-lg text-accent-bright">03</span>
                <p class="mt-2 text-lg font-medium text-ink">Bayar dan terverifikasi</p>
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