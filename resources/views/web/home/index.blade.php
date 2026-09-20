@extends('layouts.web')

@section('title', 'OpenCash — Kas kelas yang rapi, tanpa drama')
@section('meta_description', 'OpenCash membantu bendahara mencatat iuran, memverifikasi pembayaran QRIS, dan melaporkan saldo kas kelas secara transparan.')

@section('web')

    {{-- Hero: tinggi viewport dikurangi navbar sticky (h-16 = 4rem) supaya
         hero pas satu layar tanpa memaksa scroll. --}}
    <section class="mx-auto flex min-h-[calc(100dvh-4rem)] max-w-6xl items-center px-6 pb-24 pt-10 md:pb-28">
        <div class="grid w-full items-center gap-14 md:grid-cols-[1.05fr_0.95fr] md:gap-12">
            <div>
                <span class="web-rise web-rise-1 inline-flex items-center gap-2 rounded-full border border-line bg-surface/60 px-3 py-1 text-[13px] font-medium text-muted">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-bright"></span>
                    Kas kelas untuk bendahara dan seluruh siswa
                </span>

                <h1 class="web-rise web-rise-2 mt-6 text-[2.6rem] font-semibold leading-[1.04] tracking-[-0.03em] text-ink sm:text-5xl md:text-6xl">
                    Kas kelas yang rapi,
                    <span class="whitespace-nowrap bg-gradient-to-r from-accent-bright to-accent bg-clip-text text-transparent">tanpa drama</span>.
                </h1>

                <p class="web-rise web-rise-3 mt-6 max-w-md text-[17px] leading-relaxed text-muted">
                    OpenCash membantu bendahara mencatat iuran, memverifikasi pembayaran, dan melaporkan saldo kas — semua transparan dan bisa dilihat seluruh siswa kapan saja.
                </p>

                <div class="web-rise web-rise-4 mt-9 flex flex-wrap items-center gap-x-7 gap-y-4">
                    <a
                        href="{{ route('register') }}"
                        class="group inline-flex items-center gap-2 rounded-md bg-accent px-6 py-3 text-[15px] font-medium text-white transition-all duration-200 hover:bg-accent-bright hover:text-[#070b18] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-bright active:translate-y-px"
                    >
                        Daftar sekarang
                        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5">
                            <path d="M3 8h9.5M9 4.5 12.5 8 9 11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="text-[15px] font-medium text-ink transition-colors hover:text-accent-bright focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-bright">
                        Sudah punya akun? Masuk
                    </a>
                </div>

                <dl class="web-rise web-rise-5 mt-11 grid max-w-md grid-cols-3 gap-6 border-t border-line pt-6">
                    <div>
                        <dt class="text-[13px] leading-snug text-muted">Sumber angka</dt>
                        <dd class="mt-1 text-[15px] font-medium text-ink">Satu perhitungan</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] leading-snug text-muted">Metode bayar</dt>
                        <dd class="mt-1 text-[15px] font-medium text-ink">Tunai &amp; QRIS</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] leading-snug text-muted">Saldo kas</dt>
                        <dd class="mt-1 text-[15px] font-medium text-ink">Real-time</dd>
                    </div>
                </dl>
            </div>

            {{-- Stacked feature card --}}
            <div class="relative flex justify-center md:justify-end">
                <div class="pointer-events-none absolute -right-10 -top-14 h-80 w-80 rounded-full bg-accent/30 blur-[110px]" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -left-8 bottom-0 h-52 w-52 rounded-full bg-accent/20 blur-[90px]" aria-hidden="true"></div>

                <div class="web-rise web-rise-3 relative w-full max-w-sm">
                    <div class="absolute inset-x-8 top-10 h-full -rotate-6 rounded-2xl bg-ink/20" aria-hidden="true"></div>
                    <div class="absolute inset-x-4 top-5 h-full -rotate-3 rounded-2xl bg-ink/60" aria-hidden="true"></div>

                    <div class="relative flex min-h-[360px] flex-col overflow-hidden rounded-2xl bg-surface shadow-[0_60px_160px_-20px_rgba(62,123,255,0.6)]">
                        <div class="flex flex-1 flex-col p-6">
                            <span class="w-fit rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">Fitur utama</span>

                            <p class="mt-4 text-xl font-semibold leading-snug text-ink">
                                Semua kas kelas, tercatat dan terverifikasi otomatis.
                            </p>

                            <ul class="mt-6 flex-1 space-y-4 text-[15px]">
                                <li class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-4 w-4" aria-hidden="true"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="text-ink">Pencatatan iuran otomatis</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-4 w-4" aria-hidden="true"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="text-ink">Verifikasi pembayaran tunai &amp; QRIS</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-tint text-accent-bright">
                                        <svg viewBox="0 0 16 16" fill="none" class="h-4 w-4" aria-hidden="true"><path d="M3 8.5 6.2 12 13 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="text-ink">Laporan saldo real-time</span>
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('works') }}" class="flex items-center justify-between border-t border-line px-6 py-4 transition-colors hover:bg-ink/[0.04] focus-visible:outline focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-accent-bright">
                            <span class="text-sm text-muted">Bisa dilihat</span>
                            <span class="inline-flex items-center gap-1.5 text-[15px] font-semibold text-ink">
                                Seluruh siswa
                                <svg viewBox="0 0 16 16" fill="none" class="h-3.5 w-3.5 text-muted" aria-hidden="true"><path d="M6 3.5 10.5 8 6 12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cara kerja — ringkas. Detail lengkap ada di halaman Cara Kerja. --}}
    <section class="border-t border-line">
        <div class="mx-auto max-w-6xl px-6 py-24">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-ink">Cara kerja</h2>
                    <p class="mt-3 max-w-md text-[15px] leading-relaxed text-muted">
                        Ringkasnya tiga langkah. Urutan lengkap beserta peran bendahara dan siswa ada di halaman Cara Kerja.
                    </p>
                </div>
                <a href="{{ route('works') }}" class="group inline-flex shrink-0 items-center gap-2 text-[15px] font-medium text-accent-bright transition-colors hover:text-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-bright">
                    Selengkapnya
                    <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5"><path d="M3 8h9.5M9 4.5 12.5 8 9 11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>

            <ol class="mt-12 grid gap-x-8 gap-y-10 md:grid-cols-3">
                <li class="border-t border-line pt-5">
                    <span class="font-mono text-[13px] text-accent-bright">01</span>
                    <p class="mt-2 text-lg font-medium text-ink">Kelas dan bendahara disiapkan</p>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">Admin membuat kelas dan menambahkan akun bendahara, lalu bendahara menyiapkan jadwal iuran.</p>
                </li>
                <li class="border-t border-line pt-5">
                    <span class="font-mono text-[13px] text-accent-bright">02</span>
                    <p class="mt-2 text-lg font-medium text-ink">Siswa bergabung dengan kode</p>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">Bendahara membagikan kode undangan. Siswa daftar, masukkan kode, dan langsung tergabung di kelas.</p>
                </li>
                <li class="border-t border-line pt-5">
                    <span class="font-mono text-[13px] text-accent-bright">03</span>
                    <p class="mt-2 text-lg font-medium text-ink">Bayar, diverifikasi, tercatat</p>
                    <p class="mt-2 text-[15px] leading-relaxed text-muted">Siswa bayar tunai atau QRIS, bendahara memverifikasi, dan saldo kas ikut terbarui untuk semua siswa.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- CTA --}}
    <section class="px-6 py-24 text-center">
        <h2 class="text-3xl font-semibold tracking-tight text-ink">Siap merapikan kas kelasmu?</h2>
        <p class="mx-auto mt-3 max-w-md text-[15px] text-muted">Buat kelas dalam hitungan menit. Gratis untuk digunakan.</p>
        <a
            href="{{ route('register') }}"
            class="mt-7 inline-block rounded-md bg-accent px-7 py-3 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-bright active:translate-y-px"
        >
            Daftar gratis
        </a>
    </section>

@endsection
