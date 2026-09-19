<div id="bill-pay-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Bayar Tagihan</h2>
            <button type="button" id="bill-pay-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <p id="bill-pay-description" class="mt-1 text-[14px] font-medium text-ink"></p>

        <dl class="mt-4 space-y-2 rounded-xl border border-line bg-bg px-4 py-3 text-[13.5px]">
            <div class="flex items-center justify-between">
                <dt class="text-muted">Nominal tagihan</dt>
                <dd id="bill-pay-total" class="font-medium text-ink"></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-muted">Sudah dibayar</dt>
                <dd id="bill-pay-paid" class="text-emerald-400"></dd>
            </div>
            <div id="bill-pay-pending-row" class="flex items-center justify-between">
                <dt class="text-muted">Menunggu verifikasi</dt>
                <dd id="bill-pay-pending" class="text-amber-400"></dd>
            </div>
            <div class="flex items-center justify-between border-t border-line pt-2">
                <dt class="text-muted">Sisa yang harus dibayar</dt>
                <dd id="bill-pay-remaining" class="font-semibold text-red-400"></dd>
            </div>
        </dl>

        <form id="bill-pay-form" class="mt-5 space-y-4">
            <div>
                <label for="bill-pay-amount" class="text-[13px] font-medium text-ink">Nominal yang dibayar (Rp)</label>
                <input type="number" id="bill-pay-amount" min="1" required
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" />
                <p class="mt-1.5 text-xs text-muted">Boleh dibayar sebagian. Isi sesuai jumlah yang kamu transfer.</p>
            </div>

            <div>
                <label for="bill-pay-proof" class="text-[13px] font-medium text-ink">Bukti transfer</label>
                <input type="file" id="bill-pay-proof" accept="image/*" required
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[13px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-[12px] file:font-medium file:text-white focus:border-accent" />
                <p class="mt-1.5 text-xs text-muted">Screenshot bukti transfer QRIS. Maksimal 2 MB.</p>
            </div>

            <div>
                <label for="bill-pay-notes" class="text-[13px] font-medium text-ink">Catatan <span class="text-muted">(opsional)</span></label>
                <input type="text" id="bill-pay-notes" maxlength="255"
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent"
                    placeholder="Contoh: transfer dari rekening orang tua" />
            </div>

            <p id="bill-pay-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" id="bill-pay-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="bill-pay-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Kirim Bukti</button>
            </div>
        </form>
    </div>
</div>

<div id="bill-qris-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4">
    <div class="w-full max-w-sm rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">QRIS Kelas</h2>
            <button type="button" id="bill-qris-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        @if ($qrisImage)
            <img src="{{ Storage::disk('public')->url($qrisImage) }}" alt="QRIS kelas" class="mt-4 w-full rounded-md border border-line object-contain" />
            <p class="mt-3 text-[13px] text-muted">Scan, transfer sesuai nominal, lalu unggah bukti transfernya.</p>
        @else
            <p class="mt-4 text-[14px] text-muted">Bendahara belum mengunggah gambar QRIS untuk kelas ini. Hubungi bendahara untuk pembayaran tunai.</p>
        @endif
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('bill-pay-modal');
        var qrisModal = document.getElementById('bill-qris-modal');
        var closeBtn = document.getElementById('bill-pay-close');
        var cancelBtn = document.getElementById('bill-pay-cancel');
        var qrisCloseBtn = document.getElementById('bill-qris-close');
        var form = document.getElementById('bill-pay-form');
        var descEl = document.getElementById('bill-pay-description');
        var totalEl = document.getElementById('bill-pay-total');
        var paidEl = document.getElementById('bill-pay-paid');
        var pendingEl = document.getElementById('bill-pay-pending');
        var pendingRow = document.getElementById('bill-pay-pending-row');
        var remainingEl = document.getElementById('bill-pay-remaining');
        var amountInput = document.getElementById('bill-pay-amount');
        var proofInput = document.getElementById('bill-pay-proof');
        var notesInput = document.getElementById('bill-pay-notes');
        var errorEl = document.getElementById('bill-pay-error');
        var submitBtn = document.getElementById('bill-pay-submit');

        var endpoint = '{{ route('student.cash-incomes.store-qris') }}';
        var csrf = '{{ csrf_token() }}';
        var hasQris = @json((bool) $qrisImage);
        var current = null;

        function formatRupiah(value) {
            return 'Rp' + Number(value || 0).toLocaleString('id-ID');
        }

        function show(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
        function hide(el) { el.classList.add('hidden'); el.classList.remove('flex'); }

        function openModal(detail) {
            current = detail;

            descEl.textContent = detail.description;
            totalEl.textContent = formatRupiah(detail.amount);
            paidEl.textContent = formatRupiah(detail.paid);
            pendingEl.textContent = formatRupiah(detail.pending);
            pendingRow.style.display = detail.pending > 0 ? '' : 'none';
            remainingEl.textContent = formatRupiah(detail.remaining);

            amountInput.value = detail.remaining;
            amountInput.max = detail.remaining;
            notesInput.value = '';
            proofInput.value = '';
            errorEl.classList.add('hidden');

            show(modal);
        }

        window.addEventListener('bill:pay', function (e) {
            openModal(e.detail);

            // tampilkan QRIS sekali per pembukaan supaya siswa bisa langsung scan
            if (hasQris) show(qrisModal);
        });

        [closeBtn, cancelBtn].forEach(function (btn) {
            btn.addEventListener('click', function () { hide(modal); });
        });

        qrisCloseBtn.addEventListener('click', function () { hide(qrisModal); });

        modal.addEventListener('click', function (e) { if (e.target === modal) hide(modal); });
        qrisModal.addEventListener('click', function (e) { if (e.target === qrisModal) hide(qrisModal); });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (!qrisModal.classList.contains('hidden')) return hide(qrisModal);
            if (!modal.classList.contains('hidden')) hide(modal);
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!current) return;

            errorEl.classList.add('hidden');

            var amount = Number(amountInput.value || 0);

            if (amount < 1) {
                errorEl.textContent = 'Nominal harus lebih dari 0.';
                errorEl.classList.remove('hidden');
                return;
            }

            if (amount > current.remaining) {
                errorEl.textContent = 'Nominal melebihi sisa tagihan (' + formatRupiah(current.remaining) + ').';
                errorEl.classList.remove('hidden');
                return;
            }

            var payload = new FormData();
            payload.append('cash_schedule_id', current.id);
            payload.append('amount_paid', amount);
            payload.append('proof_image', proofInput.files[0]);
            payload.append('notes', notesInput.value);

            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: payload,
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function () {
                    hide(modal);
                    hide(qrisModal);
                    window.location.reload();
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal mengirim bukti pembayaran.';
                    if (d && d.errors) {
                        var first = Object.values(d.errors)[0];
                        if (first && first[0]) msg = first[0];
                    }
                    errorEl.textContent = msg;
                    errorEl.classList.remove('hidden');
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kirim Bukti';
                });
        });
    })();
</script>
