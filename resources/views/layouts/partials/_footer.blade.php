<footer class="bg-bg">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-10 md:grid-cols-[1.3fr_1fr_1fr]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-accent text-white">
                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                            <path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="text-[17px] font-semibold tracking-tight text-ink">OpenCash</span>
                </a>
                <p class="mt-4 max-w-xs text-[15px] leading-relaxed text-muted">
                    Kas kelas yang tercatat rapi dan bisa dilihat semua siswa, bukan cuma bendahara.
                </p>
            </div>

            <div>
                <p class="text-[15px] font-medium text-ink">Jelajahi</p>
                <ul class="mt-4 space-y-3 text-[15px] text-muted">
                    <li><a href="{{ route('home') }}" class="hover:text-accent-bright">Beranda</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-accent-bright">Tentang &amp; fitur</a></li>
                    <li><a href="{{ route('works') }}" class="hover:text-accent-bright">Karya</a></li>
                </ul>
            </div>

            <div>
                <p class="text-[15px] font-medium text-ink">Bantuan</p>
                <ul class="mt-4 space-y-3 text-[15px] text-muted">
                    <li><a href="{{ route('login') }}" class="hover:text-accent-bright">Masuk</a></li>
                    <li><a href="mailto:halo@opencash.app" class="hover:text-accent-bright">halo@opencash.app</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-14 flex flex-col gap-2 text-sm text-muted/80 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} OpenCash.</p>
            <p>Dibuat untuk bendahara kelas di seluruh Indonesia.</p>
        </div>
    </div>
</footer>