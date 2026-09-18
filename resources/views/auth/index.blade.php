@extends('layouts.app')

@section('title', $mode === 'register' ? 'Daftar — OpenCash' : 'Masuk — OpenCash')

@section('content')

    <main class="relative flex min-h-[100dvh] items-center justify-center overflow-hidden px-4 py-6 md:px-8 md:py-8">
        <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-accent/20 blur-[120px]"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-accent/10 blur-[110px]"></div>

        <div id="authShell" class="auth-shell {{ $mode === 'register' ? 'active' : '' }}" data-login-url="{{ route('login') }}" data-register-url="{{ route('register') }}">

            {{-- Forms --}}
            <div class="auth-forms-wrap">

                {{-- Sign in --}}
                <section class="auth-form form-sign-in" aria-labelledby="login-heading">
                    <div class="auth-form-inner">
                        <span class="auth-eyebrow">Masuk</span>
                        <h1 id="login-heading" class="auth-title">Selamat datang kembali.</h1>
                        <p class="auth-subtitle">Satu akun untuk siswa, bendahara, dan admin — langsung diarahkan ke halamanmu.</p>

                        @if ($mode === 'login' && $errors->any())
                            <div class="auth-alert" role="alert" aria-live="polite">
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 6.5v4M10 13.5h.01M8.6 3.3 2.5 14a1.6 1.6 0 0 0 1.4 2.4h12.2A1.6 1.6 0 0 0 17.5 14L11.4 3.3a1.6 1.6 0 0 0-2.8 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}" class="auth-form-body" data-auth-form>
                            @csrf

                            <div class="auth-group">
                                <label for="login-email" class="auth-label">Email</label>
                                <div class="auth-field">
                                    <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon" aria-hidden="true"><path d="M3 6.5 10 11l7-4.5M4 4h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <input type="email" name="email" id="login-email" value="{{ $mode === 'login' ? old('email') : '' }}" required
                                        autocomplete="email" spellcheck="false" inputmode="email"
                                        class="auth-input" placeholder="nama@sekolah.id">
                                </div>
                            </div>

                            <div class="auth-group">
                                <label for="login-password" class="auth-label">Password</label>
                                <div class="auth-field">
                                    <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon" aria-hidden="true"><rect x="4.5" y="9" width="11" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 9V6.5a3.5 3.5 0 0 1 7 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <input type="password" name="password" id="login-password" required
                                        autocomplete="current-password" class="auth-input auth-input-password" placeholder="••••••••">
                                    <button type="button" class="auth-reveal" data-reveal="login-password" aria-label="Tampilkan password" aria-pressed="false">
                                        <svg viewBox="0 0 20 20" fill="none" class="auth-reveal-eye" aria-hidden="true"><path d="M2 10s3-5.5 8-5.5S18 10 18 10s-3 5.5-8 5.5S2 10 2 10Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.5"/></svg>
                                        <svg viewBox="0 0 20 20" fill="none" class="auth-reveal-eye-off" aria-hidden="true"><path d="M3 3l14 14M8.2 8.4a2.2 2.2 0 0 0 3.1 3.1M6.1 6.3C3.7 7.6 2 10 2 10s3 5.5 8 5.5c1.4 0 2.6-.4 3.7-1M9.2 4.6c.3 0 .5 0 .8 0 5 0 8 5.4 8 5.4s-.6 1.2-1.7 2.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </div>

                            <label class="auth-check">
                                <input type="checkbox" name="remember" class="auth-check-input">
                                <span class="auth-check-box" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none"><path d="M5 10.5 8.5 14 15 6.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span>Ingat aku di perangkat ini</span>
                            </label>

                            <button type="submit" class="auth-submit" data-auth-submit>
                                <span class="auth-submit-label">Masuk</span>
                                <span class="auth-submit-spinner" aria-hidden="true"></span>
                            </button>
                        </form>

                        <p class="auth-mobile-toggle auth-switch">
                            Belum punya akun?
                            <a href="{{ route('register') }}" data-auth-toggle="register">Daftar</a>
                        </p>
                    </div>
                </section>

                {{-- Sign up --}}
                <section class="auth-form form-sign-up" aria-labelledby="register-heading">
                    <div class="auth-form-inner">
                        <span class="auth-eyebrow">Daftar</span>
                        <h1 id="register-heading" class="auth-title">Mulai catat kas kelasmu.</h1>
                        <p class="auth-subtitle">Setelah daftar, masukkan kode undangan dari bendahara untuk gabung kelas.</p>

                        @if ($mode === 'register' && $errors->any())
                            <div class="auth-alert" role="alert" aria-live="polite">
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 6.5v4M10 13.5h.01M8.6 3.3 2.5 14a1.6 1.6 0 0 0 1.4 2.4h12.2A1.6 1.6 0 0 0 17.5 14L11.4 3.3a1.6 1.6 0 0 0-2.8 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="auth-form-body" data-auth-form>
                            @csrf

                            <div class="auth-group">
                                <label for="register-name" class="auth-label">Nama lengkap</label>
                                <div class="auth-field">
                                    <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon" aria-hidden="true"><circle cx="10" cy="6.5" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M4 16.5c0-3 2.7-5 6-5s6 2 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <input type="text" name="name" id="register-name" value="{{ $mode === 'register' ? old('name') : '' }}" required
                                        autocomplete="name" class="auth-input" placeholder="Nama sesuai daftar kelas">
                                </div>
                            </div>

                            <div class="auth-group">
                                <label for="register-email" class="auth-label">Email</label>
                                <div class="auth-field">
                                    <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon" aria-hidden="true"><path d="M3 6.5 10 11l7-4.5M4 4h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <input type="email" name="email" id="register-email" value="{{ $mode === 'register' ? old('email') : '' }}" required
                                        autocomplete="email" spellcheck="false" inputmode="email"
                                        class="auth-input" placeholder="nama@sekolah.id">
                                </div>
                            </div>

                            <div class="auth-group">
                                <label for="register-password" class="auth-label">Password</label>
                                <div class="auth-field">
                                    <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon" aria-hidden="true"><rect x="4.5" y="9" width="11" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 9V6.5a3.5 3.5 0 0 1 7 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <input type="password" name="password" id="register-password" required
                                        autocomplete="new-password" minlength="8" class="auth-input auth-input-password" placeholder="Minimal 8 karakter">
                                    <button type="button" class="auth-reveal" data-reveal="register-password" aria-label="Tampilkan password" aria-pressed="false">
                                        <svg viewBox="0 0 20 20" fill="none" class="auth-reveal-eye" aria-hidden="true"><path d="M2 10s3-5.5 8-5.5S18 10 18 10s-3 5.5-8 5.5S2 10 2 10Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.5"/></svg>
                                        <svg viewBox="0 0 20 20" fill="none" class="auth-reveal-eye-off" aria-hidden="true"><path d="M3 3l14 14M8.2 8.4a2.2 2.2 0 0 0 3.1 3.1M6.1 6.3C3.7 7.6 2 10 2 10s3 5.5 8 5.5c1.4 0 2.6-.4 3.7-1M9.2 4.6c.3 0 .5 0 .8 0 5 0 8 5.4 8 5.4s-.6 1.2-1.7 2.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <p class="auth-hint">Gunakan minimal 8 karakter.</p>
                            </div>

                            <div class="auth-group">
                                <label for="register-password_confirmation" class="auth-label">Konfirmasi password</label>
                                <div class="auth-field">
                                    <svg viewBox="0 0 20 20" fill="none" class="auth-field-icon" aria-hidden="true"><rect x="4.5" y="9" width="11" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 9V6.5a3.5 3.5 0 0 1 7 0V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <input type="password" name="password_confirmation" id="register-password_confirmation" required
                                        autocomplete="new-password" class="auth-input auth-input-password" placeholder="Ulangi password">
                                    <button type="button" class="auth-reveal" data-reveal="register-password_confirmation" aria-label="Tampilkan password" aria-pressed="false">
                                        <svg viewBox="0 0 20 20" fill="none" class="auth-reveal-eye" aria-hidden="true"><path d="M2 10s3-5.5 8-5.5S18 10 18 10s-3 5.5-8 5.5S2 10 2 10Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.5"/></svg>
                                        <svg viewBox="0 0 20 20" fill="none" class="auth-reveal-eye-off" aria-hidden="true"><path d="M3 3l14 14M8.2 8.4a2.2 2.2 0 0 0 3.1 3.1M6.1 6.3C3.7 7.6 2 10 2 10s3 5.5 8 5.5c1.4 0 2.6-.4 3.7-1M9.2 4.6c.3 0 .5 0 .8 0 5 0 8 5.4 8 5.4s-.6 1.2-1.7 2.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="auth-submit" data-auth-submit>
                                <span class="auth-submit-label">Daftar</span>
                                <span class="auth-submit-spinner" aria-hidden="true"></span>
                            </button>
                        </form>

                        <p class="auth-mobile-toggle auth-switch">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" data-auth-toggle="login">Masuk</a>
                        </p>
                    </div>
                </section>
            </div>

            {{-- Overlay --}}
            <div class="auth-overlay-wrap">
                <div class="overlay-content prompt-register">
                    <h2 class="overlay-title">Kas kelas yang rapi, tanpa drama.</h2>
                    <p class="overlay-copy">
                        Belum punya akun? Daftar dan mulai catat kas kelasmu dalam hitungan menit.
                    </p>

                    <ul class="overlay-points">
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="8" cy="7" r="2.6" stroke="currentColor" stroke-width="1.5"/><path d="M3 16.2c0-2.6 2.2-4.2 5-4.2s5 1.6 5 4.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M13.4 5.6a2.4 2.4 0 0 1 0 4.4M15.2 16.2c0-1.4-.4-2.5-1.1-3.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span><strong>Bendahara mencatat, siswa melihat.</strong> Satu sumber data untuk seluruh kelas.</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="2.6" y="5" width="14.8" height="10.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M2.6 8.6h14.8" stroke="currentColor" stroke-width="1.5"/><path d="M6 12.4h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span><strong>Tunai dan QRIS di satu tempat.</strong> Verifikasi bukti transfer tanpa rekap manual.</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3.4 16.6h13.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M6.4 16.6v-4.4M10 16.6V7.4M13.6 16.6v-6.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span><strong>Laporan otomatis tiap periode.</strong> Pemasukan, pengeluaran, dan tunggakan.</span>
                        </li>
                    </ul>

                    <a href="{{ route('register') }}" data-auth-toggle="register" class="overlay-cta">
                        Daftar
                    </a>
                </div>

                <div class="overlay-content prompt-login">
                    <h2 class="overlay-title">Sudah pakai OpenCash?</h2>
                    <p class="overlay-copy">
                        Masuk untuk lihat kas kelasmu yang sudah tercatat rapi.
                    </p>

                    <ul class="overlay-points">
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="3.2" stroke="currentColor" stroke-width="1.5"/><path d="M9.4 9.4 16 16M13.6 13.6l1.6-1.6M11.8 11.8l1.5-1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span><strong>Masuk sekali, langsung ke halamanmu.</strong> Siswa, bendahara, dan admin punya tampilan sendiri.</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 3.4h10v13.2l-2.5-1.4-2.5 1.4-2.5-1.4L5 16.6V3.4Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M7.6 7.4h4.8M7.6 10.4h4.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <span><strong>Tagihan dan riwayat pembayaran.</strong> Semua catatan kelasmu tersimpan di satu akun.</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7.2" stroke="currentColor" stroke-width="1.5"/><path d="M10 6.2V10l2.6 1.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span><strong>Status verifikasi QRIS terlihat jelas.</strong> Tidak perlu tanya ulang ke bendahara.</span>
                        </li>
                    </ul>

                    <a href="{{ route('login') }}" data-auth-toggle="login" class="overlay-cta">
                        Masuk
                    </a>
                </div>
            </div>
        </div>
    </main>

@endsection

@push('head')
<style>
    .auth-shell {
        position: relative;
        width: 100%;
        max-width: 1180px;
        min-height: calc(100dvh - 4rem);
        max-height: 860px;
        margin: 0 auto;
        border-radius: 1.5rem;
        overflow: hidden;
        background-color: #101935;
        border: 1px solid rgba(255, 255, 255, 0.07);
        box-shadow:
            0 60px 160px -40px rgba(62, 123, 255, 0.42),
            0 2px 0 0 rgba(255, 255, 255, 0.04) inset;
    }

    /* butiran halus supaya permukaan tidak terasa flat/digital */
    .auth-shell::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 40;
        pointer-events: none;
        opacity: 0.05;
        mix-blend-mode: overlay;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23n)'/%3E%3C/svg%3E");
    }

    .auth-forms-wrap {
        position: absolute;
        top: 0;
        right: 0;
        width: 50%;
        height: 100%;
        z-index: 10;
        will-change: transform;
        transition: transform 0.78s cubic-bezier(0.76, 0, 0.24, 1);
    }
    .auth-shell.active .auth-forms-wrap { transform: translateX(-100%); }

    .auth-form {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 2.5rem 3.25rem;
    }
    /* margin:auto menjaga konten tetap center tapi tidak terpotong saat harus scroll */
    .auth-form-inner {
        margin: auto 0;
        width: 100%;
        transition:
            opacity 0.34s ease,
            transform 0.62s cubic-bezier(0.22, 1, 0.36, 1);
    }

    /* form yang keluar cepat dibersihkan, form yang masuk menyusul setelah panel hampir sampai */
    .form-sign-in { z-index: 5; }
    .form-sign-in .auth-form-inner { opacity: 1; transform: none; transition-delay: 0.16s; }
    .form-sign-up { z-index: 1; pointer-events: none; }
    .form-sign-up .auth-form-inner { opacity: 0; transform: translateX(26px); transition-delay: 0s; }

    .auth-shell.active .form-sign-in { z-index: 1; pointer-events: none; }
    .auth-shell.active .form-sign-in .auth-form-inner { opacity: 0; transform: translateX(-26px); transition-delay: 0s; }
    .auth-shell.active .form-sign-up { z-index: 5; pointer-events: auto; }
    .auth-shell.active .form-sign-up .auth-form-inner { opacity: 1; transform: none; transition-delay: 0.16s; }

    /* masuk dengan stagger halus, tidak muncul sekaligus.
       class .is-entering dipasang ulang lewat JS tiap kali form bergantian,
       jadi animasinya replay di kedua arah (login <-> daftar). */
    .auth-form.is-entering .auth-form-inner > * {
        animation: auth-rise 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .auth-form.is-entering .auth-form-inner > *:nth-child(1) { animation-delay: 0.2s; }
    .auth-form.is-entering .auth-form-inner > *:nth-child(2) { animation-delay: 0.25s; }
    .auth-form.is-entering .auth-form-inner > *:nth-child(3) { animation-delay: 0.3s; }
    .auth-form.is-entering .auth-form-inner > *:nth-child(4) { animation-delay: 0.35s; }
    .auth-form.is-entering .auth-form-inner > *:nth-child(5) { animation-delay: 0.4s; }
    .auth-form.is-entering .auth-form-inner > *:nth-child(6) { animation-delay: 0.45s; }
    .auth-form.is-entering .auth-form-inner > *:nth-child(n + 7) { animation-delay: 0.5s; }

    @keyframes auth-rise {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: none; }
    }

    .auth-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #86b4ff;
    }
    .auth-title {
        margin-top: 0.6rem;
        font-size: clamp(1.65rem, 2.6vw, 2rem);
        font-weight: 600;
        line-height: 1.15;
        letter-spacing: -0.022em;
        color: #f5f7fc;
        text-wrap: balance;
    }
    .auth-subtitle {
        margin-top: 0.6rem;
        max-width: 34ch;
        font-size: 14.5px;
        line-height: 1.6;
        color: #92a0c0;
        text-wrap: pretty;
    }

    .auth-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        margin-top: 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(248, 113, 113, 0.28);
        background-color: rgba(248, 113, 113, 0.1);
        padding: 0.7rem 0.9rem;
        font-size: 13.5px;
        line-height: 1.5;
        color: #fca5a5;
    }
    .auth-alert svg { flex-shrink: 0; height: 1.05rem; width: 1.05rem; margin-top: 0.1rem; }

    .auth-form-body { margin-top: 1.6rem; display: flex; flex-direction: column; gap: 1rem; }
    .auth-group { display: flex; flex-direction: column; gap: 0.4rem; }
    .auth-label {
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.01em;
        color: #b9c4dc;
    }
    .auth-hint { margin-top: 0.1rem; font-size: 11.5px; color: #7d8aab; }

    .auth-field { position: relative; }
    .auth-field-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        height: 1.05rem;
        width: 1.05rem;
        color: #7d8aab;
        pointer-events: none;
        transition: color 0.2s ease;
    }
    .auth-field:focus-within .auth-field-icon { color: #86b4ff; }

    .auth-input {
        width: 100%;
        border-radius: 0.7rem;
        border: 1px solid rgba(255, 255, 255, 0.09);
        background-color: rgba(7, 11, 24, 0.55);
        padding: 0.72rem 1rem 0.72rem 2.6rem;
        font-size: 15px;
        color: #f5f7fc;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .auth-input-password { padding-right: 2.7rem; }
    .auth-input::placeholder { color: rgba(146, 160, 192, 0.6); }
    .auth-input:hover { border-color: rgba(255, 255, 255, 0.16); }
    .auth-input:focus {
        outline: none;
        background-color: rgba(7, 11, 24, 0.8);
        border-color: #3e7bff;
        box-shadow: 0 0 0 3px rgba(62, 123, 255, 0.22);
    }
    /* ring khusus keyboard — tidak muncul saat klik mouse */
    .auth-input:focus-visible { box-shadow: 0 0 0 3px rgba(62, 123, 255, 0.32); }
    .auth-input:disabled { opacity: 0.6; cursor: not-allowed; }

    /* autofill bawaan browser bikin kotak kuning/biru yang merusak tema */
    .auth-input:-webkit-autofill,
    .auth-input:-webkit-autofill:hover,
    .auth-input:-webkit-autofill:focus {
        -webkit-text-fill-color: #f5f7fc;
        box-shadow: 0 0 0 1000px #0d1428 inset, 0 0 0 3px rgba(62, 123, 255, 0.22);
        caret-color: #f5f7fc;
        transition: background-color 9999s ease-out 0s;
    }

    .auth-reveal {
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 1.9rem;
        width: 1.9rem;
        border-radius: 0.5rem;
        color: #7d8aab;
        transition: color 0.2s ease, background-color 0.2s ease;
    }
    .auth-reveal:hover { color: #f5f7fc; background-color: rgba(255, 255, 255, 0.06); }
    .auth-reveal:focus-visible { outline: 2px solid #3e7bff; outline-offset: 2px; }
    .auth-reveal svg { height: 1.05rem; width: 1.05rem; }
    .auth-reveal-eye-off { display: none; }
    .auth-reveal[aria-pressed="true"] .auth-reveal-eye { display: none; }
    .auth-reveal[aria-pressed="true"] .auth-reveal-eye-off { display: block; }

    .auth-check {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-top: 0.15rem;
        font-size: 13.5px;
        color: #92a0c0;
        cursor: pointer;
        user-select: none;
        width: fit-content;
    }
    .auth-check-input { position: absolute; opacity: 0; pointer-events: none; }
    .auth-check-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 1.1rem;
        width: 1.1rem;
        flex-shrink: 0;
        border-radius: 0.35rem;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background-color: rgba(7, 11, 24, 0.6);
        color: #070b18;
        transition: background-color 0.18s ease, border-color 0.18s ease;
    }
    .auth-check-box svg { height: 0.85rem; width: 0.85rem; opacity: 0; transition: opacity 0.15s ease; }
    .auth-check:hover .auth-check-box { border-color: rgba(255, 255, 255, 0.32); }
    .auth-check-input:checked + .auth-check-box { background-color: #86b4ff; border-color: #86b4ff; }
    .auth-check-input:checked + .auth-check-box svg { opacity: 1; }
    .auth-check-input:focus-visible + .auth-check-box { outline: 2px solid #3e7bff; outline-offset: 2px; }

    .auth-submit {
        position: relative;
        margin-top: 0.4rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        width: 100%;
        border-radius: 0.7rem;
        background-color: #3e7bff;
        padding: 0.78rem 1rem;
        font-size: 15px;
        font-weight: 500;
        color: #ffffff;
        transition: background-color 0.2s ease, transform 0.12s ease, box-shadow 0.2s ease;
        box-shadow: 0 10px 30px -12px rgba(62, 123, 255, 0.7);
    }
    .auth-submit:hover { background-color: #86b4ff; color: #070b18; }
    .auth-submit:active { transform: translateY(1px) scale(0.995); }
    .auth-submit:focus-visible { outline: 2px solid #86b4ff; outline-offset: 2px; }
    .auth-submit[data-loading="true"] { cursor: progress; opacity: 0.85; }
    .auth-submit[data-loading="true"] .auth-submit-label { opacity: 0.75; }
    .auth-submit-spinner {
        display: none;
        height: 1rem;
        width: 1rem;
        border-radius: 9999px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        animation: auth-spin 0.65s linear infinite;
    }
    .auth-submit[data-loading="true"] .auth-submit-spinner { display: inline-block; }
    @keyframes auth-spin { to { transform: rotate(360deg); } }

    .auth-switch { margin-top: 1.5rem; text-align: center; font-size: 13.5px; color: #92a0c0; }
    .auth-switch a { font-weight: 500; color: #86b4ff; transition: color 0.2s ease; }
    .auth-switch a:hover { color: #3e7bff; }
    .auth-mobile-toggle { display: none; }

    /* ---- Panel overlay ---- */
    .auth-overlay-wrap {
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        overflow: hidden;
        z-index: 20;
        background: linear-gradient(158deg, #3e7bff 0%, #1c3f91 62%, #162f6d 100%);
        will-change: transform;
        transition: transform 0.78s cubic-bezier(0.76, 0, 0.24, 1);
    }
    .auth-overlay-wrap::before {
        content: "";
        position: absolute;
        top: -30%;
        right: -25%;
        width: 65%;
        height: 65%;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 70%);
    }
    .auth-overlay-wrap::after {
        content: "";
        position: absolute;
        bottom: -22%;
        left: -20%;
        width: 58%;
        height: 58%;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(134, 180, 255, 0.38), transparent 70%);
    }
    .auth-shell.active .auth-overlay-wrap { transform: translateX(100%); }

    .overlay-content {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        padding: 3rem 2.75rem;
        text-align: left;
        opacity: 0;
        transform: translateX(-16px);
        pointer-events: none;
        transition:
            opacity 0.42s ease,
            transform 0.66s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .prompt-register { z-index: 5; opacity: 1; transform: none; pointer-events: auto; transition-delay: 0.2s; }
    .prompt-login { z-index: 1; }
    .auth-shell.active .prompt-register { z-index: 1; opacity: 0; transform: translateX(16px); pointer-events: none; transition-delay: 0s; }
    .auth-shell.active .prompt-login { z-index: 5; opacity: 1; transform: none; pointer-events: auto; transition-delay: 0.2s; }

    /* isi panel muncul bertahap, tidak menempel kaku saat panel selesai bergeser */
    .overlay-content.is-entering > * {
        animation: auth-rise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .overlay-content.is-entering > *:nth-child(1) { animation-delay: 0.16s; }
    .overlay-content.is-entering > *:nth-child(2) { animation-delay: 0.22s; }
    .overlay-content.is-entering > *:nth-child(3) { animation-delay: 0.28s; }
    .overlay-content.is-entering > *:nth-child(4) { animation-delay: 0.34s; }

    .overlay-title {
        max-width: 15ch;
        font-size: clamp(1.5rem, 2.3vw, 1.9rem);
        font-weight: 600;
        line-height: 1.16;
        letter-spacing: -0.02em;
        color: #ffffff;
        text-wrap: balance;
    }
    .overlay-copy {
        margin-top: 0.75rem;
        max-width: 30ch;
        font-size: 14.5px;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.82);
        text-wrap: pretty;
    }

    /* poin fitur sebagai pengganti kartu mock — isinya nyambung dengan alur login */
    .overlay-points {
        margin-top: 1.75rem;
        display: flex;
        width: 100%;
        max-width: 23rem;
        flex-direction: column;
        gap: 0.85rem;
    }
    .overlay-points li {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        font-size: 13.5px;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.82);
    }
    .overlay-points li strong { font-weight: 600; color: #ffffff; }
    .overlay-points svg {
        flex-shrink: 0;
        height: 1.15rem;
        width: 1.15rem;
        margin-top: 0.08rem;
        color: rgba(255, 255, 255, 0.92);
    }

    .overlay-cta {
        margin-top: 1.9rem;
        border-radius: 0.7rem;
        border: 1px solid rgba(255, 255, 255, 0.42);
        padding: 0.66rem 1.6rem;
        font-size: 14.5px;
        font-weight: 500;
        color: #ffffff;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.12s ease;
    }
    .overlay-cta:hover { background-color: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.7); }
    .overlay-cta:active { transform: translateY(1px); }
    .overlay-cta:focus-visible { outline: 2px solid #ffffff; outline-offset: 3px; }

    @media (max-width: 900px) {
        .auth-form { padding: 2.25rem 2rem; }
        .overlay-content { padding: 2.5rem 2rem; }
    }

    @media (max-width: 767px) {
        .auth-shell { min-height: 0; box-shadow: 0 40px 100px -30px rgba(62, 123, 255, 0.4); }
        .auth-forms-wrap { position: static; width: 100%; transform: none !important; }
        .auth-form { position: relative; inset: auto; padding: 2.25rem 1.5rem; display: none; overflow: visible; }
        .auth-form-inner { margin: 0; }
        .form-sign-in { display: flex; }
        .auth-shell.active .form-sign-in { display: none; }
        .auth-shell.active .form-sign-up { display: flex; }
        .auth-overlay-wrap { display: none; }
        .auth-mobile-toggle { display: block; }
    }

    @media (prefers-reduced-motion: reduce) {
        .auth-shell *,
        .auth-shell *::before,
        .auth-shell *::after {
            animation-duration: 0.001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.001ms !important;
        }
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
        var loginForm = shell.querySelector('.form-sign-in');
        var registerForm = shell.querySelector('.form-sign-up');
        var loginPrompt = shell.querySelector('.prompt-register');
        var registerPrompt = shell.querySelector('.prompt-login');

        // restart animasi masuk: hapus class, paksa reflow, pasang lagi
        function replay(el) {
            if (!el) return;
            el.classList.remove('is-entering');
            void el.offsetWidth;
            el.classList.add('is-entering');
        }

        function syncA11y(active) {
            // form yang tidak aktif jangan bisa di-tab / dibaca screen reader
            if (loginForm) loginForm.toggleAttribute('inert', active);
            if (registerForm) registerForm.toggleAttribute('inert', !active);
            if (loginPrompt) loginPrompt.setAttribute('aria-hidden', active ? 'true' : 'false');
            if (registerPrompt) registerPrompt.setAttribute('aria-hidden', active ? 'false' : 'true');
        }

        function setMode(mode, push) {
            var active = mode === 'register';
            if (shell.classList.contains('active') === active) return;

            shell.classList.toggle('active', active);
            syncA11y(active);
            replay(active ? registerForm : loginForm);
            replay(active ? registerPrompt : loginPrompt);

            if (push && window.history && window.history.pushState) {
                window.history.pushState({ authMode: mode }, '', active ? registerUrl : loginUrl);
            }
        }

        function modeFromLocation() {
            return window.location.pathname.indexOf('register') !== -1 ? 'register' : 'login';
        }

        // beri state ke entry awal supaya tombol back bisa kembali ke form login
        if (window.history && window.history.replaceState) {
            window.history.replaceState({ authMode: modeFromLocation() }, '', window.location.href);
        }

        document.querySelectorAll('[data-auth-toggle]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                setMode(el.getAttribute('data-auth-toggle'), true);
            });
        });

        window.addEventListener('popstate', function (e) {
            var mode = (e.state && e.state.authMode) ? e.state.authMode : modeFromLocation();
            setMode(mode, false);
        });

        // animasi masuk untuk mode yang dirender server
        syncA11y(shell.classList.contains('active'));
        replay(shell.classList.contains('active') ? registerForm : loginForm);
        replay(shell.classList.contains('active') ? registerPrompt : loginPrompt);

        // toggle lihat/sembunyikan password
        document.querySelectorAll('[data-reveal]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-reveal'));
                if (!input) return;

                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });
        });

        // state loading supaya tidak terasa "diam" setelah submit
        document.querySelectorAll('[data-auth-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                if (!form.checkValidity()) return;

                var btn = form.querySelector('[data-auth-submit]');
                if (!btn) return;

                btn.setAttribute('data-loading', 'true');
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
            });
        });
    })();
</script>
@endpush
