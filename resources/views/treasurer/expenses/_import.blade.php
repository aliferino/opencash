<div id="expense-import-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-lg rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Import Pengeluaran</h2>
            <button type="button" id="expense-import-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <p class="mt-1 text-[13px] text-muted">
            Tambah banyak pengeluaran sekaligus dari berkas Excel/CSV.
        </p>

        <div class="mt-5 rounded-xl border border-line bg-bg p-4">
            <p class="text-[13px] font-medium text-ink">Format kolom yang dibutuhkan</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left text-[12.5px]">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-wide text-muted">
                            <th class="py-1.5 pr-3 font-medium">Kolom</th>
                            <th class="py-1.5 pr-3 font-medium">Wajib</th>
                            <th class="py-1.5 font-medium">Contoh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr><td class="py-2 pr-3 text-ink">Tanggal</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">18/09/2026</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Keterangan</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">Beli spidol papan tulis</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Nominal</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">2000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-[12px] text-muted">
                Pengeluaran hasil import tidak punya foto nota. Kalau perlu nota sebagai bukti, catat lewat tombol "+ Pengeluaran".
            </p>
        </div>

        <a href="{{ route('treasurer.cash-expenses.import.template') }}"
           class="mt-4 inline-flex items-center gap-2 text-[13.5px] font-medium text-accent-bright transition-colors hover:text-accent">
            <i data-lucide="download" class="h-4 w-4" stroke-width="1.8"></i>
            Unduh template Excel
        </a>

        <form id="expense-import-form" class="mt-5 space-y-4">
            <div>
                <label for="expense-import-file" class="text-[13px] font-medium text-ink">Berkas</label>
                <input type="file" id="expense-import-file" accept=".xlsx,.xls,.csv,.txt" required
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[13px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-[12px] file:font-medium file:text-white focus:border-accent" />
                <p class="mt-1.5 text-xs text-muted">Format .xlsx, .xls, atau .csv. Maksimal 4 MB.</p>
            </div>

            <div id="expense-import-errors" class="hidden rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-[13px] text-red-300">
                <p id="expense-import-errors-title" class="font-medium"></p>
                <ul id="expense-import-errors-list" class="mt-1.5 list-disc space-y-0.5 pl-4"></ul>
            </div>

            <div id="expense-import-success" class="hidden rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-[13px] text-emerald-300"></div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" id="expense-import-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="expense-import-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Import</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('expense-import-modal');
        var openBtn = document.getElementById('expense-import-open');
        var closeBtn = document.getElementById('expense-import-close');
        var cancelBtn = document.getElementById('expense-import-cancel');
        var form = document.getElementById('expense-import-form');
        var fileInput = document.getElementById('expense-import-file');
        var submitBtn = document.getElementById('expense-import-submit');
        var errorsBox = document.getElementById('expense-import-errors');
        var errorsTitle = document.getElementById('expense-import-errors-title');
        var errorsList = document.getElementById('expense-import-errors-list');
        var successBox = document.getElementById('expense-import-success');

        var endpoint = '{{ route('treasurer.cash-expenses.import.store') }}';
        var csrf = '{{ csrf_token() }}';

        if (!openBtn) return;

        function show(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
        function hide(el) { el.classList.add('hidden'); el.classList.remove('flex'); }

        function reset() {
            form.reset();
            errorsBox.classList.add('hidden');
            successBox.classList.add('hidden');
            errorsList.innerHTML = '';
        }

        openBtn.addEventListener('click', function () {
            reset();
            show(modal);
        });

        [closeBtn, cancelBtn].forEach(function (btn) {
            btn.addEventListener('click', function () { hide(modal); });
        });

        modal.addEventListener('click', function (e) { if (e.target === modal) hide(modal); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorsBox.classList.add('hidden');
            successBox.classList.add('hidden');

            if (!fileInput.files.length) {
                errorsTitle.textContent = 'Pilih berkas terlebih dahulu.';
                show(errorsBox);
                return;
            }

            var payload = new FormData();
            payload.append('file', fileInput.files[0]);

            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengimport...';

            fetch(endpoint, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: payload,
            })
                .then(function (res) {
                    return res.json().then(function (d) { return { ok: res.ok, data: d }; });
                })
                .then(function (r) {
                    if (!r.ok) {
                        errorsTitle.textContent = r.data.message || 'Import gagal.';
                        (r.data.errors || []).forEach(function (msg) {
                            var li = document.createElement('li');
                            li.textContent = msg;
                            errorsList.appendChild(li);
                        });
                        if (r.data.total_errors > (r.data.errors || []).length) {
                            var li = document.createElement('li');
                            li.textContent = '...dan ' + (r.data.total_errors - r.data.errors.length) + ' baris lain.';
                            errorsList.appendChild(li);
                        }
                        show(errorsBox);
                        return;
                    }

                    successBox.textContent = r.data.message || 'Import berhasil.';
                    show(successBox);
                    fileInput.value = '';
                    window.dispatchEvent(new CustomEvent('expenses:refresh'));
                })
                .catch(function () {
                    errorsTitle.textContent = 'Gagal menghubungi server.';
                    show(errorsBox);
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Import';
                });
        });
    })();
</script>
