<header class="sticky top-0 z-40 bg-bg/70 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <span class="flex h-7 w-7 items-center justify-center rounded-md bg-accent text-white">
                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                    <path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="text-[17px] font-semibold tracking-tight text-ink">OpenCash</span>
        </a>

        <nav class="hidden items-center gap-8 text-[15px] md:flex">
            <a
                href="{{ route('home') }}"
                class="transition-colors hover:text-accent-bright {{ request()->routeIs('home') ? 'font-medium text-accent-bright' : 'text-muted' }}"
            >
                Beranda
            </a>
            <a
                href="{{ route('about') }}"
                class="transition-colors hover:text-accent-bright {{ request()->routeIs('about') ? 'font-medium text-accent-bright' : 'text-muted' }}"
            >
                Tentang
            </a>
            <a
                href="{{ route('works') }}"
                class="transition-colors hover:text-accent-bright {{ request()->routeIs('works') ? 'font-medium text-accent-bright' : 'text-muted' }}"
            >
                Karya
            </a>
        </nav>

        <div class="hidden md:block">
            <a
                href="{{ route('login') }}"
                class="rounded-md bg-white/5 px-4 py-2 text-[15px] font-medium text-ink transition-colors hover:bg-white/10"
            >
                Masuk
            </a>
        </div>

        <button
            type="button"
            id="navbar-toggle"
            class="inline-flex h-9 w-9 items-center justify-center rounded-md text-ink md:hidden"
            aria-controls="navbar-menu"
            aria-expanded="false"
            aria-label="Buka menu"
        >
            <svg id="navbar-icon-open" viewBox="0 0 20 20" fill="none" class="h-5 w-5">
                <path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <svg id="navbar-icon-close" viewBox="0 0 20 20" fill="none" class="hidden h-5 w-5">
                <path d="M5 5l10 10M15 5 5 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <div id="navbar-menu" class="hidden bg-bg/95 px-6 py-4 backdrop-blur-md md:hidden">
        <nav class="flex flex-col gap-4 text-[15px]">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'font-medium text-accent-bright' : 'text-muted' }}">Beranda</a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'font-medium text-accent-bright' : 'text-muted' }}">Tentang</a>
            <a href="{{ route('works') }}" class="{{ request()->routeIs('works') ? 'font-medium text-accent-bright' : 'text-muted' }}">Karya</a>
            <a href="{{ route('login') }}" class="font-medium text-accent-bright">Masuk</a>
        </nav>
    </div>
</header>

<script>
    (function () {
        var toggle = document.getElementById('navbar-toggle');
        var menu = document.getElementById('navbar-menu');
        var iconOpen = document.getElementById('navbar-icon-open');
        var iconClose = document.getElementById('navbar-icon-close');

        if (!toggle || !menu) return;

        toggle.addEventListener('click', function () {
            var isOpen = menu.classList.contains('hidden') === false;

            menu.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden');
            iconClose.classList.toggle('hidden');
            toggle.setAttribute('aria-expanded', String(!isOpen));
        });
    })();
</script>