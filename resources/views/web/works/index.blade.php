@extends('layouts.web')

@section('title', 'Karya — OpenCash')
@section('meta_description', 'Kelas-kelas dan sekolah yang sudah memakai OpenCash.')

@section('web')

    <section class="mx-auto flex max-w-6xl flex-col items-start px-6 py-24 md:py-32">
        <span class="rounded-full bg-accent-tint px-3 py-1 text-sm font-medium text-accent-bright">Segera hadir</span>
        <h1 class="mt-5 max-w-xl text-4xl font-semibold leading-[1.1] tracking-tight text-ink md:text-5xl">
            Halaman ini masih disiapkan.
        </h1>
        <p class="mt-5 max-w-md text-[17px] leading-relaxed text-muted">
            Nantinya di sini akan ada cerita kelas-kelas yang sudah pakai OpenCash. Sementara ini, coba dulu fiturnya lewat pendaftaran.
        </p>
        <a
            href="{{ route('register') }}"
            class="mt-8 inline-block rounded-md bg-accent px-6 py-3 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            Daftar sekarang
        </a>
    </section>

@endsection