<div class="mt-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-ink">Semua pembayaran</h2>
            <p class="mt-1 text-[13px] text-muted">Termasuk yang masih menunggu verifikasi dan yang ditolak.</p>
        </div>

        <div class="relative min-w-[220px] max-w-xs flex-1">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
            <input type="text" id="history-search" placeholder="Cari tagihan..."
                class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent" />
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-surface">
        <table class="w-full text-left text-[14px]">
            <thead>
                <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                    <th class="px-6 py-4 font-medium">Tanggal Bayar</th>
                    <th class="px-6 py-4 font-medium">Tagihan</th>
                    <th class="px-6 py-4 font-medium">Metode</th>
                    <th class="px-6 py-4 font-medium text-right">Nominal</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium">Dicatat Oleh</th>
                </tr>
            </thead>
            <tbody id="history-table-body" class="divide-y divide-line">
                @forelse ($payments as $payment)
                    @php
                        $total = (int) $payment->amount_paid + (int) $payment->fine_paid;
                        $searchKey = strtolower(($payment->cashSchedule?->description ?? '').' '.$payment->payment_method);
                    @endphp
                    <tr class="history-row transition-colors hover:bg-white/5" data-search="{{ $searchKey }}">
                        <td class="px-6 py-4 text-muted">{{ $payment->income_date?->translatedFormat('d M Y') ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-ink">{{ $payment->cashSchedule?->description ?? '—' }}</p>
                            @if ($payment->cashSchedule?->due_date)
                                <p class="text-xs text-muted">Jatuh tempo {{ $payment->cashSchedule->due_date->translatedFormat('d M Y') }}</p>
                            @endif
                            @if ($payment->notes)
                                <p class="text-xs text-muted italic">{{ $payment->notes }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 text-muted">
                                <i data-lucide="{{ $payment->payment_method === 'qris' ? 'qr-code' : 'banknote' }}" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                                {{ $payment->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-ink">
                            Rp{{ number_format($total, 0, ',', '.') }}
                            @if ((int) $payment->fine_paid > 0)
                                <p class="text-xs text-amber-300">termasuk denda Rp{{ number_format($payment->fine_paid, 0, ',', '.') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($payment->status === 'verified')
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-medium text-emerald-300">Terverifikasi</span>
                            @elseif ($payment->status === 'pending')
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-medium text-amber-300">Menunggu</span>
                            @else
                                <span class="rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-medium text-red-300">Ditolak</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-muted">{{ $payment->treasurer?->name ?? 'Mandiri (QRIS)' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-muted">Belum ada pembayaran tercatat.</td>
                    </tr>
                @endforelse
                <tr id="history-empty-filter" class="hidden">
                    <td colspan="6" class="px-6 py-10 text-center text-muted">Tidak ada pembayaran yang cocok dengan pencarian.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('history-search');
        var rows = Array.prototype.slice.call(document.querySelectorAll('.history-row'));
        var emptyFilter = document.getElementById('history-empty-filter');
        var timer = null;

        function apply() {
            var term = input.value.trim().toLowerCase();
            var visible = 0;

            rows.forEach(function (row) {
                var match = !term || row.dataset.search.indexOf(term) !== -1;
                row.classList.toggle('hidden', !match);
                if (match) visible++;
            });

            emptyFilter.classList.toggle('hidden', !(term && visible === 0));
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(apply, 200);
        });
    })();
</script>
