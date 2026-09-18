<div id="expense-create-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Tambah Pengeluaran</h2>
            <button type="button" id="expense-create-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <form id="expense-create-form" class="mt-5 space-y-4">
            <div>
                <label for="expense-description" class="text-[13px] font-medium text-ink">Keterangan</label>
                <textarea id="expense-description" required rows="3" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: Beli spidol dan penghapus papan tulis"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="expense-amount" class="text-[13px] font-medium text-ink">Nominal (Rp)</label>
                    <input type="number" id="expense-amount" min="0" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="25000" />
                </div>

                <div>
                    <label for="expense-date" class="text-[13px] font-medium text-ink">Tanggal</label>
                    <input type="date" id="expense-date" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" />
                </div>
            </div>

            <div>
                <label for="expense-proof" class="text-[13px] font-medium text-ink">Foto Nota</label>
                <input type="file" id="expense-proof" accept="image/*" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent-tint file:px-3 file:py-1.5 file:text-accent-bright" />
                <p class="mt-1.5 text-xs text-muted">Format gambar, maksimal 2MB.</p>
            </div>

            <p id="expense-create-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="expense-create-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="expense-create-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="expense-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Detail Pengeluaran</h2>
            <button type="button" id="expense-detail-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <dl id="expense-detail-body" class="mt-5 space-y-3 text-[14px]"></dl>
    </div>
</div>

<div id="expense-proof-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4">
    <div class="w-full max-w-lg rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Foto Nota</h2>
            <button type="button" id="expense-proof-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>
        <p id="expense-proof-caption" class="mt-1 text-[13px] text-muted"></p>
        <img id="expense-proof-image" src="" alt="Foto nota" class="mt-4 max-h-[60vh] w-full rounded-md border border-line object-contain" />
        <p id="expense-proof-empty" class="mt-4 hidden text-[14px] text-muted">Tidak ada foto nota untuk pengeluaran ini.</p>
    </div>
</div>

<script>
    (function () {
        var createModal = document.getElementById('expense-create-modal');
        var createOpenBtn = document.getElementById('expense-create-open');
        var createCloseBtn = document.getElementById('expense-create-close');
        var createCancelBtn = document.getElementById('expense-create-cancel');
        var createForm = document.getElementById('expense-create-form');
        var descriptionInput = document.getElementById('expense-description');
        var amountInput = document.getElementById('expense-amount');
        var dateInput = document.getElementById('expense-date');
        var proofInput = document.getElementById('expense-proof');
        var createSubmit = document.getElementById('expense-create-submit');
        var createError = document.getElementById('expense-create-error');

        var detailModal = document.getElementById('expense-detail-modal');
        var detailCloseBtn = document.getElementById('expense-detail-close');
        var detailBody = document.getElementById('expense-detail-body');

        var proofModal = document.getElementById('expense-proof-modal');
        var proofCloseBtn = document.getElementById('expense-proof-close');
        var proofImage = document.getElementById('expense-proof-image');
        var proofCaption = document.getElementById('expense-proof-caption');
        var proofEmpty = document.getElementById('expense-proof-empty');

        var storeUrl = '{{ url('treasurer/cash-expenses') }}';
        var csrf = '{{ csrf_token() }}';

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatRupiah(value) {
            return 'Rp' + Number(value || 0).toLocaleString('id-ID');
        }

        function formatDate(value) {
            if (!value) return '—';
            var d = new Date(value);
            if (isNaN(d.getTime())) return value;
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function todayValue() {
            return new Date().toISOString().slice(0, 10);
        }

        function show(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
        function hide(el) { el.classList.add('hidden'); el.classList.remove('flex'); }

        createOpenBtn.addEventListener('click', function () {
            createForm.reset();
            dateInput.value = todayValue();
            createError.classList.add('hidden');
            show(createModal);
        });

        [createCloseBtn, createCancelBtn].forEach(function (btn) {
            btn.addEventListener('click', function () { hide(createModal); });
        });

        createModal.addEventListener('click', function (e) { if (e.target === createModal) hide(createModal); });

        createForm.addEventListener('submit', function (e) {
            e.preventDefault();
            createError.classList.add('hidden');

            if (!proofInput.files.length) {
                createError.textContent = 'Pilih foto nota terlebih dahulu.';
                createError.classList.remove('hidden');
                return;
            }

            var formData = new FormData();
            formData.append('description', descriptionInput.value);
            formData.append('amount', amountInput.value);
            formData.append('expense_date', dateInput.value);
            formData.append('proof_image', proofInput.files[0]);

            createSubmit.disabled = true;
            createSubmit.textContent = 'Menyimpan...';

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: formData,
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function () {
                    hide(createModal);
                    window.dispatchEvent(new CustomEvent('expenses:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan pengeluaran.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    createError.textContent = msg;
                    createError.classList.remove('hidden');
                })
                .finally(function () {
                    createSubmit.disabled = false;
                    createSubmit.textContent = 'Simpan';
                });
        });

        window.addEventListener('expense:detail', function (e) {
            var expense = e.detail;
            var rows = [
                ['Tanggal', formatDate(expense.expense_date)],
                ['Keterangan', escapeHtml(expense.description)],
                ['Nominal', formatRupiah(expense.amount)],
                ['Dicatat Oleh', expense.treasurer ? expense.treasurer.name : '—'],
                ['Foto Nota', expense.proof_image ? 'Ada' : 'Tidak ada'],
            ];

            detailBody.innerHTML = rows.map(function (row) {
                return '<div class="flex items-start justify-between gap-4 border-b border-line pb-2.5 last:border-0 last:pb-0">' +
                    '<dt class="text-muted">' + row[0] + '</dt>' +
                    '<dd class="text-right font-medium text-ink">' + row[1] + '</dd>' +
                '</div>';
            }).join('');

            show(detailModal);
        });

        detailCloseBtn.addEventListener('click', function () { hide(detailModal); });
        detailModal.addEventListener('click', function (e) { if (e.target === detailModal) hide(detailModal); });

        window.addEventListener('expense:proof', function (e) {
            var expense = e.detail;
            proofCaption.textContent = formatDate(expense.expense_date) + ' — ' + formatRupiah(expense.amount);

            if (expense.proof_image) {
                proofImage.src = '/storage/' + expense.proof_image;
                proofImage.classList.remove('hidden');
                proofEmpty.classList.add('hidden');
            } else {
                proofImage.removeAttribute('src');
                proofImage.classList.add('hidden');
                proofEmpty.classList.remove('hidden');
            }

            show(proofModal);
        });

        proofCloseBtn.addEventListener('click', function () { hide(proofModal); });
        proofModal.addEventListener('click', function (e) { if (e.target === proofModal) hide(proofModal); });
    })();
</script>
