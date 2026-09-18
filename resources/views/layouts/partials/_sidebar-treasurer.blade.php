<aside class="sticky top-0 flex h-screen w-64 shrink-0 flex-col border-r border-line bg-surface/40">
    <div class="flex h-20 items-center gap-2.5 px-6">
        <a href="{{ route('treasurer.dashboard') }}" class="flex items-center gap-2.5">
            <span class="flex h-7 w-7 items-center justify-center rounded-md bg-accent text-white">
                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                    <path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="text-[17px] font-semibold tracking-tight text-ink">Open<span class="text-accent">Cash</span></span>
        </a>
    </div>

    <nav class="flex-1 space-y-1 px-4 py-2">
        <a href="{{ route('treasurer.dashboard') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition-colors {{ request()->routeIs('treasurer.dashboard') ? 'bg-accent-tint font-medium text-accent-bright' : 'text-muted hover:bg-white/5 hover:text-ink' }}">
            <i data-lucide="layout-dashboard" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
            Dashboard
        </a>

        <a href="{{ route('treasurer.group-settings.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition-colors {{ request()->routeIs('treasurer.group-settings.*') ? 'bg-accent-tint font-medium text-accent-bright' : 'text-muted hover:bg-white/5 hover:text-ink' }}">
            <i data-lucide="settings-2" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
            Pengaturan Kas
        </a>

        <a href="{{ route('cash-schedules.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition-colors {{ request()->routeIs('cash-schedules.*') ? 'bg-accent-tint font-medium text-accent-bright' : 'text-muted hover:bg-white/5 hover:text-ink' }}">
            <i data-lucide="calendar-clock" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
            Jadwal Tagihan
        </a>

        <a href="{{ route('treasurer.group.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition-colors {{ request()->routeIs('treasurer.group.*') ? 'bg-accent-tint font-medium text-accent-bright' : 'text-muted hover:bg-white/5 hover:text-ink' }}">
            <i data-lucide="users" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
            Grup
        </a>

        <a href="{{ route('cash-incomes.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition-colors {{ request()->routeIs('cash-incomes.*') ? 'bg-accent-tint font-medium text-accent-bright' : 'text-muted hover:bg-white/5 hover:text-ink' }}">
            <i data-lucide="arrow-down-circle" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
            Pemasukan
        </a>

        <a href="{{ route('cash-expenses.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition-colors {{ request()->routeIs('cash-expenses.*') ? 'bg-accent-tint font-medium text-accent-bright' : 'text-muted hover:bg-white/5 hover:text-ink' }}">
            <i data-lucide="arrow-up-circle" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
            Pengeluaran
        </a>

        <a href="{{ route('treasurer.reports.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition-colors {{ request()->routeIs('treasurer.reports.*') ? 'bg-accent-tint font-medium text-accent-bright' : 'text-muted hover:bg-white/5 hover:text-ink' }}">
            <i data-lucide="bar-chart-3" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
            Laporan
        </a>
    </nav>

    <div class="border-t border-line p-4">
        <div class="flex items-center gap-3 rounded-md px-2 py-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-tint text-[13px] font-semibold text-accent-bright">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-[14px] font-medium text-ink">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-muted">Bendahara</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-[15px] text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="log-out" class="h-[18px] w-[18px] shrink-0" stroke-width="1.8"></i>
                Keluar
            </button>
        </form>
    </div>
</aside>