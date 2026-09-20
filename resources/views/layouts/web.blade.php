@extends('layouts.app')

@push('head')
<style>
    /* Motion bersama halaman publik (beranda, tentang, cara kerja).
       Dipakai lintas halaman, jadi ditaruh di layout — bukan di app.css. */
    @keyframes web-rise {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: none; }
    }

    .web-rise { animation: web-rise 640ms cubic-bezier(0.22, 1, 0.36, 1) both; }
    .web-rise-1 { animation-delay: 70ms; }
    .web-rise-2 { animation-delay: 150ms; }
    .web-rise-3 { animation-delay: 230ms; }
    .web-rise-4 { animation-delay: 310ms; }
    .web-rise-5 { animation-delay: 390ms; }
    .web-rise-6 { animation-delay: 470ms; }

    @media (prefers-reduced-motion: reduce) {
        .web-rise { animation: none; }
    }
</style>
@endpush

@section('content')
    @include('layouts.partials._navbar')

    <main>
        @yield('web')
    </main>

    @include('layouts.partials._footer')
@endsection
