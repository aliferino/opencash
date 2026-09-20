@extends('layouts.app')

@section('title', 'Gabung Kelas — OpenCash')

@section('content')
    <main
        id="onboarding"
        class="relative flex min-h-[100dvh] flex-col overflow-hidden px-4 py-6 md:px-8 md:py-8"
        data-status-url="{{ route('onboarding.status') }}"
        data-dashboard-url="{{ route('dashboard') }}"
    >
        <div class="pointer-events-none absolute -right-28 -top-32 h-80 w-80 rounded-full bg-accent/20 blur-[120px]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-24 h-72 w-72 rounded-full bg-accent/10 blur-[110px]" aria-hidden="true"></div>

        <header class="relative z-10 mx-auto flex w-full max-w-5xl items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-accent text-white">
                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                        <path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-[17px] font-semibold tracking-tight text-ink">Open<span class="text-accent">Cash</span></span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden items-center gap-2.5 rounded-full border border-line bg-surface/60 py-1.5 pl-1.5 pr-4 sm:flex">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-tint text-[12px] font-semibold text-accent-bright">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block max-w-[14rem] truncate text-[13.5px] font-medium leading-tight text-ink">{{ auth()->user()->name }}</span>
                        <span class="block text-[11.5px] leading-tight text-muted">Belum terhubung ke kelas</span>
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ob-ghost">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M8 3.5H5.5A1.5 1.5 0 0 0 4 5v10a1.5 1.5 0 0 0 1.5 1.5H8M12.5 7l3 3-3 3M15.5 10H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        <section class="relative z-10 mx-auto flex w-full max-w-5xl flex-1 items-center py-8 md:py-12">
            <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-start lg:gap-8">
                <div class="ob-card ob-rise">
                    <span class="ob-eyebrow">Onboarding</span>
                    <h1 class="ob-title">Akunmu belum terhubung ke kelas.</h1>
                    <p class="ob-subtitle">
                        Ada dua cara masuk: masukkan kode undangan dari bendahara kelasmu, atau tunggu admin
                        menambahkan akunmu secara manual. Halaman ini memeriksa status keanggotaanmu berkala.
                    </p>

                    <form id="invite-form" method="POST" action="{{ route('onboarding.join') }}" class="ob-form">
                        @csrf

                        <div class="ob-group">
                            <label for="invite-code" class="ob-label">Kode undangan</label>
                            <div class="ob-field">
                                <svg viewBox="0 0 20 20" fill="none" class="ob-field-icon" aria-hidden="true"><path d="M12.2 3.6a4.2 4.2 0 0 1 4.2 4.2 4.2 4.2 0 0 1-4.2 4.2H10l-2.6 2.6H5.6l-.9 2.1H2.6l1-3.4 6.3-6.3a4.2 4.2 0 0 1 2.3-3.4Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><circle cx="12.6" cy="7.8" r="1.1" stroke="currentColor" stroke-width="1.5"/></svg>
                                <input
                                    type="text"
                                    name="invite_code"
                                    id="invite-code"
                                    value="{{ \Illuminate\Support\Str::upper(preg_replace('/[^A-Za-z0-9]/', '', old('invite_code', ''))) }}"
                                    required
                                    maxlength="12"
                                    autocomplete="off"
                                    autocapitalize="characters"
                                    spellcheck="false"
                                    aria-describedby="invite-code-hint"
                                    @error('invite_code') aria-invalid="true" @enderror
                                    class="ob-input"
                                    placeholder="A1B2C3"
                                />
                            </div>
                            @error('invite_code')
                                <p class="ob-error" role="alert">{{ $message }}</p>
                            @else
                                <p id="invite-code-hint" class="ob-hint">6 karakter huruf dan angka. Minta ke bendahara kelasmu.</p>
                            @enderror
                        </div>

                        <button type="submit" id="invite-submit" class="ob-submit">
                            <span class="ob-submit-label">Gabung Kelas</span>
                            <span class="ob-submit-spinner" aria-hidden="true"></span>
                        </button>
                    </form>

                    <div class="ob-divider"><span>atau</span></div>

                    <div class="ob-wait">
                        <h2 class="ob-wait-title">Tunggu ditambahkan admin</h2>
                        <p class="ob-wait-copy">
                            Belum punya kode? Minta admin atau bendahara memasukkan akunmu ke kelas. Begitu kamu
                            terdaftar, halaman ini otomatis mengarahkanmu ke dashboard.
                        </p>

                        <div class="ob-status-row">
                            <span id="ob-status" class="ob-status" data-state="waiting" role="status" aria-live="polite">
                                <span class="ob-status-dot" aria-hidden="true"></span>
                                <span id="ob-status-label">Menunggu ditambahkan</span>
                            </span>

                            <button type="button" id="ob-check" class="ob-ghost">
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M16.5 10a6.5 6.5 0 1 1-1.9-4.6M16.6 3.4v3.9h-3.9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Cek status
                            </button>
                        </div>

                        <p id="ob-last-checked" class="ob-hint ob-last"></p>
                    </div>
                </div>

                <aside class="ob-aside ob-rise ob-rise-1">
                    <h2 class="ob-aside-title">Cara masuk kelas</h2>

                    <ol class="ob-steps">
                        <li>
                            <span class="ob-step-num" aria-hidden="true">1</span>
                            <span class="ob-step-text">
                                <strong>Pakai kode undangan.</strong>
                                Masukkan kode dari bendahara di form sebelah. Satu kode dipakai seluruh siswa di kelas.
                            </span>
                        </li>
                        <li>
                            <span class="ob-step-num" aria-hidden="true">2</span>
                            <span class="ob-step-text">
                                <strong>Ditambahkan manual.</strong>
                                Admin atau bendahara bisa memasukkan akunmu langsung ke kelas, tanpa kode.
                            </span>
                        </li>
                    </ol>

                    <div class="ob-note">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7.2" stroke="currentColor" stroke-width="1.5"/><path d="M10 9v4.2M10 6.6h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <p>Kode undangan tidak sekali pakai. Kalau kode bocor, bendahara bisa membuat kode baru dari halaman Grup.</p>
                    </div>

                    <p class="ob-help">Bingung? Hubungi bendahara kelasmu atau admin sekolah.</p>
                </aside>
            </div>
        </section>
    </main>
@endsection

@push('head')
<style>
    .ob-card {
        border-radius: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.07);
        background-color: #101935;
        padding: 2rem 2.25rem;
        box-shadow:
            0 40px 120px -50px rgba(62, 123, 255, 0.5),
            0 1px 0 0 rgba(255, 255, 255, 0.04) inset;
    }

    .ob-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #86b4ff;
    }
    .ob-title {
        margin-top: 0.6rem;
        font-size: clamp(1.5rem, 2.4vw, 1.85rem);
        font-weight: 600;
        line-height: 1.18;
        letter-spacing: -0.022em;
        color: #f5f7fc;
        text-wrap: balance;
    }
    .ob-subtitle {
        margin-top: 0.6rem;
        max-width: 54ch;
        font-size: 14.5px;
        line-height: 1.65;
        color: #92a0c0;
        text-wrap: pretty;
    }

    .ob-form { margin-top: 1.6rem; display: flex; flex-direction: column; gap: 1rem; }
    .ob-group { display: flex; flex-direction: column; gap: 0.4rem; }
    .ob-label { font-size: 12px; font-weight: 500; letter-spacing: 0.01em; color: #b9c4dc; }
    .ob-hint { font-size: 11.5px; line-height: 1.5; color: #7d8aab; }
    .ob-error { font-size: 12px; line-height: 1.5; color: #fca5a5; }

    .ob-field { position: relative; }
    .ob-field-icon {
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
    .ob-field:focus-within .ob-field-icon { color: #86b4ff; }

    .ob-input {
        width: 100%;
        border-radius: 0.7rem;
        border: 1px solid rgba(255, 255, 255, 0.09);
        background-color: rgba(7, 11, 24, 0.55);
        padding: 0.72rem 1rem 0.72rem 2.6rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 16px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #f5f7fc;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .ob-input::placeholder { color: rgba(146, 160, 192, 0.5); }
    .ob-input:hover { border-color: rgba(255, 255, 255, 0.16); }
    .ob-input:focus {
        outline: none;
        background-color: rgba(7, 11, 24, 0.8);
        border-color: #3e7bff;
        box-shadow: 0 0 0 3px rgba(62, 123, 255, 0.22);
    }
    .ob-input:focus-visible { box-shadow: 0 0 0 3px rgba(62, 123, 255, 0.32); }
    .ob-input:disabled { opacity: 0.6; cursor: not-allowed; }
    .ob-input[aria-invalid="true"] { border-color: rgba(248, 113, 113, 0.55); }
    .ob-input:-webkit-autofill,
    .ob-input:-webkit-autofill:hover,
    .ob-input:-webkit-autofill:focus {
        -webkit-text-fill-color: #f5f7fc;
        box-shadow: 0 0 0 1000px #0d1428 inset, 0 0 0 3px rgba(62, 123, 255, 0.22);
        caret-color: #f5f7fc;
        transition: background-color 9999s ease-out 0s;
    }

    .ob-submit {
        position: relative;
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
        box-shadow: 0 10px 30px -12px rgba(62, 123, 255, 0.7);
        transition: background-color 0.2s ease, transform 0.12s ease, box-shadow 0.2s ease;
    }
    .ob-submit:hover { background-color: #86b4ff; color: #070b18; }
    .ob-submit:active { transform: translateY(1px) scale(0.995); }
    .ob-submit:focus-visible { outline: 2px solid #86b4ff; outline-offset: 2px; }
    .ob-submit[data-loading="true"] { cursor: progress; opacity: 0.85; }
    .ob-submit[data-loading="true"] .ob-submit-label { opacity: 0.75; }
    .ob-submit-spinner {
        display: none;
        height: 1rem;
        width: 1rem;
        border-radius: 9999px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        animation: ob-spin 0.65s linear infinite;
    }
    .ob-submit[data-loading="true"] .ob-submit-spinner { display: inline-block; }
    @keyframes ob-spin { to { transform: rotate(360deg); } }

    .ob-divider {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        margin-top: 1.6rem;
        font-size: 11px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #7d8aab;
    }
    .ob-divider::before,
    .ob-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background-color: rgba(255, 255, 255, 0.08);
    }

    .ob-wait {
        margin-top: 1.1rem;
        border-radius: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background-color: rgba(7, 11, 24, 0.4);
        padding: 1.1rem 1.25rem 1.25rem;
    }
    .ob-wait-title { font-size: 14.5px; font-weight: 600; color: #f5f7fc; }
    .ob-wait-copy { margin-top: 0.35rem; font-size: 13px; line-height: 1.6; color: #92a0c0; text-wrap: pretty; }

    .ob-status-row {
        margin-top: 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .ob-status {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background-color: rgba(255, 255, 255, 0.04);
        padding: 0.4rem 0.85rem;
        font-size: 12.5px;
        font-weight: 500;
        color: #b9c4dc;
        transition: color 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
    }
    .ob-status-dot {
        height: 0.5rem;
        width: 0.5rem;
        flex-shrink: 0;
        border-radius: 9999px;
        background-color: #92a0c0;
        animation: ob-pulse 2.2s ease-in-out infinite;
    }
    .ob-status[data-state="checking"] {
        color: #86b4ff;
        border-color: rgba(62, 123, 255, 0.4);
        background-color: rgba(62, 123, 255, 0.1);
    }
    .ob-status[data-state="checking"] .ob-status-dot { background-color: #86b4ff; }
    .ob-status[data-state="joined"] {
        color: #6ee7b7;
        border-color: rgba(16, 185, 129, 0.35);
        background-color: rgba(16, 185, 129, 0.12);
    }
    .ob-status[data-state="joined"] .ob-status-dot { background-color: #34d399; animation: none; }
    .ob-status[data-state="error"] {
        color: #fca5a5;
        border-color: rgba(248, 113, 113, 0.32);
        background-color: rgba(248, 113, 113, 0.1);
    }
    .ob-status[data-state="error"] .ob-status-dot { background-color: #f87171; animation: none; }

    @keyframes ob-pulse {
        0%, 100% { opacity: 0.35; transform: scale(0.85); }
        50% { opacity: 1; transform: scale(1.1); }
    }

    .ob-last { margin-top: 0.6rem; }

    .ob-ghost {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 0.55rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background-color: transparent;
        padding: 0.5rem 0.85rem;
        font-size: 13px;
        font-weight: 500;
        color: #92a0c0;
        transition: color 0.2s ease, border-color 0.2s ease, background-color 0.2s ease, transform 0.12s ease;
    }
    .ob-ghost:hover { color: #f5f7fc; border-color: rgba(255, 255, 255, 0.24); background-color: rgba(255, 255, 255, 0.05); }
    .ob-ghost:active { transform: translateY(1px); }
    .ob-ghost:focus-visible { outline: 2px solid #3e7bff; outline-offset: 2px; }
    .ob-ghost:disabled { opacity: 0.55; cursor: not-allowed; }
    .ob-ghost svg { height: 1rem; width: 1rem; flex-shrink: 0; }
    .ob-ghost[data-loading="true"] { cursor: progress; }
    .ob-ghost[data-loading="true"] svg { animation: ob-spin 0.7s linear infinite; }

    .ob-aside {
        border-radius: 1.25rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background-color: rgba(16, 25, 53, 0.6);
        padding: 1.5rem 1.4rem;
    }
    .ob-aside-title { font-size: 13px; font-weight: 600; color: #f5f7fc; }
    .ob-steps { margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem; }
    .ob-steps li { display: flex; gap: 0.7rem; }
    .ob-step-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 1.5rem;
        width: 1.5rem;
        flex-shrink: 0;
        border-radius: 9999px;
        background-color: rgba(62, 123, 255, 0.16);
        font-size: 12px;
        font-weight: 600;
        color: #86b4ff;
    }
    .ob-step-text { font-size: 13px; line-height: 1.6; color: #92a0c0; }
    .ob-step-text strong { display: block; font-weight: 600; color: #f5f7fc; }

    .ob-note {
        margin-top: 1.25rem;
        display: flex;
        gap: 0.6rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 1rem;
        font-size: 12.5px;
        line-height: 1.6;
        color: #92a0c0;
    }
    .ob-note svg { flex-shrink: 0; height: 1rem; width: 1rem; margin-top: 0.15rem; color: #86b4ff; }

    .ob-help { margin-top: 1rem; font-size: 12.5px; line-height: 1.6; color: #7d8aab; }

    .ob-rise { animation: ob-rise 620ms cubic-bezier(0.22, 1, 0.36, 1) both; }
    .ob-rise-1 { animation-delay: 90ms; }
    @keyframes ob-rise {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: none; }
    }

    @media (max-width: 900px) {
        .ob-card { padding: 1.75rem 1.6rem; }
    }

    @media (max-width: 767px) {
        .ob-status-row { justify-content: flex-start; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ob-rise { animation: none; }
        .ob-status-dot { animation: none; }
        .ob-submit-spinner,
        .ob-ghost[data-loading="true"] svg { animation-duration: 1.2s; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var shell = document.getElementById('onboarding');
        if (!shell) return;

        var statusUrl = shell.dataset.statusUrl;
        var dashboardUrl = shell.dataset.dashboardUrl;
        var pollMs = 8000;
        var redirectDelay = 900;

        // ---- Input kode undangan ----
        var codeInput = document.getElementById('invite-code');

        if (codeInput) {
            codeInput.addEventListener('input', function () {
                var caret = codeInput.selectionStart;
                var clean = codeInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

                if (clean !== codeInput.value) {
                    codeInput.value = clean;
                    if (typeof caret === 'number') {
                        var pos = Math.min(caret, clean.length);
                        codeInput.setSelectionRange(pos, pos);
                    }
                }
            });

            // dukung tautan berisi kode: /onboarding?code=A1B2C3
            var fromUrl = (new URLSearchParams(window.location.search).get('code') || '')
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '');

            if (fromUrl && !codeInput.value) {
                codeInput.value = fromUrl.slice(0, 12);
            }
        }

        // ---- State loading saat submit ----
        var form = document.getElementById('invite-form');
        var submitBtn = document.getElementById('invite-submit');

        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                if (!form.checkValidity()) return;

                submitBtn.setAttribute('data-loading', 'true');
                submitBtn.disabled = true;
                submitBtn.setAttribute('aria-busy', 'true');
            });
        }

        // ---- Polling status keanggotaan ----
        var pill = document.getElementById('ob-status');
        var statusLabel = document.getElementById('ob-status-label');
        var checkBtn = document.getElementById('ob-check');
        var lastChecked = document.getElementById('ob-last-checked');

        var busy = false;
        var finished = false;
        var timer = null;

        function setState(state, text) {
            pill.setAttribute('data-state', state);
            statusLabel.textContent = text;
        }

        function clockNow() {
            try {
                return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return '';
            }
        }

        function stopPolling() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        function check() {
            if (busy || finished || document.hidden) return;

            busy = true;
            checkBtn.disabled = true;
            checkBtn.setAttribute('data-loading', 'true');
            setState('checking', 'Memeriksa status...');

            fetch(statusUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('gagal');
                    return res.json();
                })
                .then(function (data) {
                    var time = clockNow();
                    if (time) lastChecked.textContent = 'Terakhir diperiksa pukul ' + time + '.';

                    if (!data.joined) {
                        setState('waiting', 'Menunggu ditambahkan');
                        return;
                    }

                    finished = true;
                    stopPolling();
                    setState('joined', data.group ? 'Terhubung ke ' + data.group : 'Terhubung');
                    window.setTimeout(function () {
                        window.location.assign(data.redirect || dashboardUrl);
                    }, redirectDelay);
                })
                .catch(function () {
                    setState('error', 'Gagal memeriksa, coba lagi');
                })
                .finally(function () {
                    busy = false;
                    checkBtn.disabled = false;
                    checkBtn.removeAttribute('data-loading');
                });
        }

        checkBtn.addEventListener('click', check);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopPolling();
            } else if (!finished) {
                check();
                timer = window.setInterval(check, pollMs);
            }
        });

        if (!document.hidden) {
            window.setTimeout(check, 1200);
            timer = window.setInterval(check, pollMs);
        }
    })();
</script>
@endpush
