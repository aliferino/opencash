@extends('layouts.app')

@section('title', $mode === 'register' ? 'Daftar — OpenCash' : 'Masuk — OpenCash')

@section('content')

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-6 md:px-8 md:py-8">
        <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-accent/20 blur-[120px]"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-accent/10 blur-[110px]"></div>

        <div id="authShell" class="auth-shell {{ $mode === 'register' ? 'active' : '' }}" data-login-url="{{ route('login') }}" data-register-url="{{ route('register') }}">

            {{-- Forms --}}
            <div class="auth-forms-wrap">

                {{-- Sign in --}}
                <div class="auth-form form-sign-in">
                    <h1 class="text-2xl font-semibold tracking-tight text-ink">Masuk ke akunmu</h1>
                    <p class="mt-1.5 text-[15px] text-muted">Satu akun untuk siswa, bendahara, dan admin — langsung diarahkan ke halamanmu.</p>

                    @if ($mode === 'login' && $errors->any())
                        <div class="mt-5 rounded-md bg-red-500/10 px-4 py-3 text-[14px] text-red-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-3.5">
                        @csrf

                        <div class="auth-field">
                            <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon"><path d="M3 6.5 10 11l7-4.5M4 4h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input type="email" name="email" id="login-email" value="{{ $mode === 'login' ? old('email') : '' }}" required
                                class="auth-input" placeholder="Email">
                        </div>

                        <div class="auth-field">
                            <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon"><rect x="4.5" y="9" width="11" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 9V6.5a3.5 3.5 0 0 1 7 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <input type="password" name="password" id="login-password" required
                                class="auth-input" placeholder="Password">
                        </div>

                        <label class="flex items-center gap-2 pt-1 text-[14px] text-muted">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-line bg-bg/60 text-accent focus:ring-accent">
                            Ingat aku di perangkat ini
                        </label>

                        <button type="submit" class="mt-2 w-full rounded-full bg-accent px-4 py-2.5 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">
                            Masuk
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-muted auth-mobile-toggle">
                        Belum punya akun?
                        <a href="{{ route('register') }}" data-auth-toggle="register" class="font-medium text-accent-bright hover:text-accent">Daftar</a>
                    </p>
                </div>

                {{-- Sign up --}}
                <div class="auth-form form-sign-up">
                    <h1 class="text-2xl font-semibold tracking-tight text-ink">Buat akun siswa</h1>
                    <p class="mt-1.5 text-[15px] text-muted">Setelah daftar, masukkan kode undangan dari bendahara untuk gabung kelas.</p>

                    @if ($mode === 'register' && $errors->any())
                        <div class="mt-5 rounded-md bg-red-500/10 px-4 py-3 text-[14px] text-red-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-3.5">
                        @csrf

                        <div class="auth-field">
                            <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon"><circle cx="10" cy="6.5" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M4 16.5c0-3 2.7-5 6-5s6 2 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <input type="text" name="name" id="register-name" value="{{ $mode === 'register' ? old('name') : '' }}" required
                                class="auth-input" placeholder="Nama lengkap">
                        </div>

                        <div class="auth-field">
                            <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon"><path d="M3 6.5 10 11l7-4.5M4 4h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input type="email" name="email" id="register-email" value="{{ $mode === 'register' ? old('email') : '' }}" required
                                class="auth-input" placeholder="Email">
                        </div>

                        <div class="auth-field">
                            <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon"><rect x="4.5" y="9" width="11" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 9V6.5a3.5 3.5 0 0 1 7 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <input type="password" name="password" id="register-password" required
                                class="auth-input" placeholder="Password">
                        </div>

                        <div class="auth-field">
                            <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon"><rect x="4.5" y="9" width="11" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 9V6.5a3.5 3.5 0 0 1 7 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <input type="password" name="password_confirmation" id="register-password_confirmation" required
                                class="auth-input" placeholder="Konfirmasi password">
                        </div>

                        <button type="submit" class="mt-2 w-full rounded-full bg-accent px-4 py-2.5 text-[15px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">
                            Daftar
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-muted auth-mobile-toggle">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" data-auth-toggle="login" class="font-medium text-accent-bright hover:text-accent">Masuk</a>
                    </p>
                </div>
            </div>

            {{-- Overlay --}}
            <div class="auth-overlay-wrap">
                <div class="overlay-content prompt-register">
                    <span class="flex h-10 w-10 items-center justify-center rounded-md bg-white/15 text-white">
                        <svg viewBox="0 0 20 20" fill="none" class="h-5 w-5"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2 class="mt-6 text-2xl font-semibold leading-snug text-white">Kas kelas yang rapi, tanpa drama.</h2>
                    <p class="mt-3 max-w-[260px] text-[15px] leading-relaxed text-white/80">
                        Belum punya akun? Daftar dan mulai catat kas kelasmu dalam hitungan menit.
                    </p>
                    <a href="{{ route('register') }}" data-auth-toggle="register" class="mt-7 rounded-md border border-white/40 px-6 py-2.5 text-[15px] font-medium text-white transition-colors hover:bg-white/10">
                        Daftar
                    </a>
                </div>

                <div class="overlay-content prompt-login">
                    <span class="flex h-10 w-10 items-center justify-center rounded-md bg-white/15 text-white">
                        <svg viewBox="0 0 20 20" fill="none" class="h-5 w-5"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2 class="mt-6 text-2xl font-semibold leading-snug text-white">Sudah pakai OpenCash?</h2>
                    <p class="mt-3 max-w-[260px] text-[15px] leading-relaxed text-white/80">
                        Masuk untuk lihat kas kelasmu yang sudah tercatat rapi.
                    </p>
                    <a href="{{ route('login') }}" data-auth-toggle="login" class="mt-7 rounded-md border border-white/40 px-6 py-2.5 text-[15px] font-medium text-white transition-colors hover:bg-white/10">
                        Masuk
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('head')
<style>
    .auth-shell {
        position: relative;
        width: 100%;
        max-width: 1180px;
        min-height: calc(100vh - 4rem);
        max-height: 860px;
        margin: 0 auto;
        border-radius: 1.25rem;
        overflow: hidden;
        background-color: #101935;
        box-shadow: 0 60px 160px -30px rgba(62, 123, 255, 0.45);
    }

    .auth-forms-wrap {
        position: absolute;
        top: 0;
        right: 0;
        width: 50%;
        height: 100%;
        transition: transform 0.6s ease-in-out;
        z-index: 10;
    }
    .auth-shell.active .auth-forms-wrap { transform: translateX(-100%); }

    .auth-form {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 3rem 4rem;
        transition: opacity 0.25s ease-in-out;
    }
    .form-sign-in { opacity: 1; z-index: 5; transition-delay: 0.2s; }
    .form-sign-up { opacity: 0; z-index: 1; pointer-events: none; }
    .auth-shell.active .form-sign-in { opacity: 0; z-index: 1; pointer-events: none; transition-delay: 0s; }
    .auth-shell.active .form-sign-up { opacity: 1; z-index: 5; pointer-events: auto; transition-delay: 0.2s; }

    .auth-overlay-wrap {
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        overflow: hidden;
        z-index: 20;
        background: linear-gradient(160deg, #3e7bff 0%, #1c3f91 100%);
        transition: transform 0.6s ease-in-out;
    }
    .auth-overlay-wrap::before {
        content: "";
        position: absolute;
        top: -30%;
        right: -25%;
        width: 65%;
        height: 65%;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.16), transparent 70%);
    }
    .auth-overlay-wrap::after {
        content: "";
        position: absolute;
        bottom: -20%;
        left: -20%;
        width: 55%;
        height: 55%;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(134, 180, 255, 0.35), transparent 70%);
    }
    .auth-shell.active .auth-overlay-wrap { transform: translateX(100%); }

    .overlay-content {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        padding: 3rem 2.5rem;
        text-align: left;
        transition: opacity 0.25s ease-in-out;
    }
    .prompt-register { opacity: 1; z-index: 5; transition-delay: 0.2s; }
    .prompt-login { opacity: 0; z-index: 1; pointer-events: none; }
    .auth-shell.active .prompt-register { opacity: 0; z-index: 1; pointer-events: none; transition-delay: 0s; }
    .auth-shell.active .prompt-login { opacity: 1; z-index: 5; pointer-events: auto; transition-delay: 0.2s; }

    .auth-mobile-toggle { display: none; }

    .auth-field { position: relative; }
    .auth-field-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        height: 1.05rem;
        width: 1.05rem;
        color: #92a0c0;
        pointer-events: none;
    }
    .auth-input {
        width: 100%;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background-color: rgba(7, 11, 24, 0.6);
        padding: 0.7rem 1rem 0.7rem 2.6rem;
        font-size: 15px;
        color: #f5f7fc;
    }
    .auth-input::placeholder { color: rgba(146, 160, 192, 0.7); }
    .auth-input:focus {
        outline: none;
        border-color: #3e7bff;
        box-shadow: 0 0 0 1px #3e7bff;
    }

    @media (max-width: 767px) {
        .auth-shell { min-height: 0; box-shadow: 0 40px 100px -30px rgba(62, 123, 255, 0.4); }
        .auth-forms-wrap { position: static; width: 100%; transform: none !important; }
        .auth-form { position: relative; inset: auto; padding: 2.5rem 1.5rem; display: none; }
        .form-sign-in { display: flex; }
        .auth-shell.active .form-sign-in { display: none; }
        .auth-shell.active .form-sign-up { display: flex; }
        .auth-overlay-wrap { display: none; }
        .auth-mobile-toggle { display: block; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var shell = document.getElementById('authShell');
        if (!shell) return;

        var loginUrl = shell.dataset.loginUrl;
        var registerUrl = shell.dataset.registerUrl;

        function setMode(mode, push) {
            var active = mode === 'register';
            shell.classList.toggle('active', active);

            if (push && window.history && window.history.pushState) {
                window.history.pushState({ authMode: mode }, '', active ? registerUrl : loginUrl);
            }
        }

        document.querySelectorAll('[data-auth-toggle]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                setMode(el.getAttribute('data-auth-toggle'), true);
            });
        });

        window.addEventListener('popstate', function (e) {
            var mode = e.state && e.state.authMode ? e.state.authMode : (shell.classList.contains('active') ? 'register' : 'login');
            setMode(mode, false);
        });
    })();
</script>
@endpush