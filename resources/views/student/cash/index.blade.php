@extends('layouts.panel')

@section('title', 'Kas Kelas — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Kas Kelas</h1>
        <p class="mt-1 text-[15px] text-muted">Ke mana uang kas kelasmu pergi — semuanya bisa kamu lihat.</p>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-3">
        <div class="rounded-2xl border border-accent/40 bg-accent-tint p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent text-white">
                <i data-lucide="wallet" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Saldo Kas Sekarang</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ \App\Support\CashLedger::rupiah($balance['balance']) }}</p>
            @if ($balance['balance'] < 0)
                <p class="mt-1 text-[12px] text-red-300">Pengeluaran melebihi pemasukan terverifikasi</p>
            @endif
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                <i data-lucide="arrow-down-circle" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Pemasukan</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($balance['income'], 0, ',', '.') }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-500/10 text-red-400">
                <i data-lucide="arrow-up-circle" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Pengeluaran</p>
            <p class="mt-1 text-3xl font-semibold text-ink">Rp{{ number_format($balance['expense'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">Rincian pengeluaran</h2>
            <p class="mt-1 text-[13px] text-muted">Semua pengeluaran kas kelas beserta nota yang diunggah bendahara.</p>

            <div class="mt-5 space-y-2">
                @forelse ($expenses as $expense)
                    <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-line bg-bg px-4 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-medium text-ink">{{ $expense->description }}</p>
                            <p class="mt-0.5 text-xs text-muted">
                                {{ $expense->expense_date?->translatedFormat('d M Y') ?? '—' }}
                                &middot; dicatat {{ $expense->treasurer?->name ?? '—' }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-[14px] font-medium text-red-400">Rp{{ number_format($expense->amount, 0, ',', '.') }}</span>

                            @if ($expense->proof_image)
                                <button type="button"
                                    class="expense-proof-btn inline-flex items-center gap-1.5 rounded-md border border-line px-2.5 py-1.5 text-[12px] text-muted transition-colors hover:border-accent hover:text-accent-bright"
                                    data-url="{{ Storage::disk('public')->url($expense->proof_image) }}"
                                    data-caption="{{ $expense->description }}">
                                    <i data-lucide="image" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                                    Nota
                                </button>
                            @else
                                <span class="text-[12px] text-muted">tanpa nota</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-[14px] text-muted">Bendahara belum mencatat pengeluaran apa pun.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">Ke mana uangnya pergi</h2>
            <p class="mt-1 text-[13px] text-muted">Diringkas per keterangan.</p>

            <div class="mt-5 space-y-4">
                @forelse ($expenseByCategory as $row)
                    @php
                        $share = $balance['expense'] > 0 ? round($row['total'] / $balance['expense'] * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-[13.5px] text-ink">{{ $row['description'] }}</p>
                            <span class="shrink-0 text-[13px] text-muted">Rp{{ number_format($row['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-white/8">
                            <span class="block h-full rounded-full bg-accent" style="width: {{ $share }}%"></span>
                        </div>
                        <p class="mt-1 text-[11px] text-muted">{{ $row['count'] }} catatan &middot; {{ $share }}% dari pengeluaran</p>
                    </div>
                @empty
                    <p class="py-6 text-center text-[13px] text-muted">Belum ada pengeluaran.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div id="expense-proof-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4">
        <div class="w-full max-w-lg rounded-2xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-ink">Nota Pengeluaran</h2>
                <button type="button" id="expense-proof-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                    <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
                </button>
            </div>
            <p id="expense-proof-caption" class="mt-1 text-[13px] text-muted"></p>
            <img id="expense-proof-image" src="" alt="Nota pengeluaran" class="mt-4 max-h-[60vh] w-full rounded-md border border-line object-contain" />
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('expense-proof-modal');
            var image = document.getElementById('expense-proof-image');
            var caption = document.getElementById('expense-proof-caption');
            var closeBtn = document.getElementById('expense-proof-close');

            function close() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                image.src = '';
            }

            document.querySelectorAll('.expense-proof-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    image.src = btn.dataset.url;
                    caption.textContent = btn.dataset.caption || '';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
            });
        })();
    </script>
@endsection
