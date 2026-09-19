<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Tagihan</th>
                <th class="px-6 py-4 font-medium">Jatuh Tempo</th>
                <th class="px-6 py-4 font-medium text-right">Nominal</th>
                <th class="px-6 py-4 font-medium text-right">Terbayar</th>
                <th class="px-6 py-4 font-medium text-right">Sisa</th>
                <th class="px-6 py-4 font-medium">Status</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @forelse ($bills as $bill)
                <tr class="transition-colors hover:bg-white/5">
                    <td class="px-6 py-4">
                        <p class="font-medium text-ink">{{ $bill['description'] }}</p>
                        @if ($bill['payments']->isNotEmpty())
                            <p class="mt-0.5 text-xs text-muted">
                                {{ $bill['payments']->count() }} kali bayar &middot; terakhir
                                {{ $bill['payments']->sortByDesc('income_date')->first()?->income_date?->translatedFormat('d M Y') }}
                            </p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-muted">{{ $bill['due_date']?->translatedFormat('d M Y') ?? '—' }}</td>
                    <td class="px-6 py-4 text-right text-ink">Rp{{ number_format($bill['amount'], 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-right">
                        <span class="{{ $bill['paid'] > 0 ? 'text-emerald-400' : 'text-muted' }}">
                            Rp{{ number_format($bill['paid'], 0, ',', '.') }}
                        </span>
                        @if ($bill['pending'] > 0)
                            <p class="text-xs text-amber-400">+ Rp{{ number_format($bill['pending'], 0, ',', '.') }} menunggu</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if ($bill['remaining'] > 0)
                            <span class="font-medium text-red-400">Rp{{ number_format($bill['remaining'], 0, ',', '.') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if ($bill['status'] === 'paid')
                            <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-medium text-emerald-300">Lunas</span>
                        @elseif ($bill['status'] === 'partial')
                            <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-medium text-amber-300">Kurang bayar</span>
                        @elseif ($bill['status'] === 'pending')
                            <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-medium text-amber-300">Menunggu verifikasi</span>
                        @else
                            <span class="rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-medium text-red-300">Belum bayar</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if ($bill['remaining'] > 0)
                            <button
                                type="button"
                                class="bill-pay-btn inline-flex items-center gap-1.5 rounded-md bg-accent px-3 py-2 text-[13px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
                                data-id="{{ $bill['id'] }}"
                                data-description="{{ $bill['description'] }}"
                                data-amount="{{ $bill['amount'] }}"
                                data-paid="{{ $bill['paid'] }}"
                                data-pending="{{ $bill['pending'] }}"
                                data-remaining="{{ $bill['remaining'] }}"
                            >
                                <i data-lucide="upload" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                                Bayar
                            </button>
                        @else
                            <span class="text-xs text-muted">Selesai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-muted">Belum ada tagihan dari bendahara.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    (function () {
        function formatRupiah(value) {
            return 'Rp' + Number(value || 0).toLocaleString('id-ID');
        }

        document.querySelectorAll('.bill-pay-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                window.dispatchEvent(new CustomEvent('bill:pay', {
                    detail: {
                        id: btn.dataset.id,
                        description: btn.dataset.description,
                        amount: Number(btn.dataset.amount || 0),
                        paid: Number(btn.dataset.paid || 0),
                        pending: Number(btn.dataset.pending || 0),
                        remaining: Number(btn.dataset.remaining || 0),
                    },
                }));
            });
        });
    })();
</script>
