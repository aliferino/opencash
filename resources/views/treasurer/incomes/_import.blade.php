<div id="income-import-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-lg rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Import Pemasukan</h2>
            <button type="button" id="income-import-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <p class="mt-1 text-[13px] text-muted">
            Tambah banyak pembayaran sekaligus dari berkas Excel/CSV. Pembayaran hasil import dicatat sebagai <strong class="text-ink">tunai</strong> dan langsung terverifikasi.
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
                        <tr><td class="py-2 pr-3 text-ink">Email Siswa</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">udin@sekolah.id</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Deskripsi Tagihan</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">Kas 18 September</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Nominal</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">5000</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Denda</td><td class="py-2 pr-3 text-muted">tidak</td><td class="py-2 text-muted">0</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Tanggal Bayar</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">18/09/2026</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-[12px] text-muted">
                Deskripsi tagihan harus sama persis dengan yang ada di Jadwal Tagihan. Nominal boleh sebagian (cicilan) — sisa tagihan dihitung otomatis.
            </p>
        </div>

        <a href="{{ route('treasurer.cash-incomes.import.template') }}"
           class="mt-4 inline-flex items-center gap-2 text-[13.5px] font-medium text-accent-bright transition-colors hover:text-accent">
            <i data-lucide="download" class="h-4 w-4" stroke-width="1.8"></i>
            Unduh template Excel
        </a>

        <form id="income-import-form" class="mt-5 space-y-4">
            <div>
                <label for="income-import-file" class="text-[13px] font-medium text-ink">Berkas</label>
                <input type="file" id="income-import-file" accept=".xlsx,.xls,.csv,.txt" required
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[13px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-[12px] file:font-medium file:text-white focus:border-accent" />
                <p class="mt-1.5 text-xs text-muted">Format .xlsx, .xls, atau .csv. Maksimal 4 MB.</p>
            </div>

            <div id="income-import-errors" class="hidden rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-[13px] text-red-300">
                <p id="income-import-errors-title" class="font-medium"></p>
                <ul id="income-import-errors-list" class="mt-1.5 list-disc space-y-0.5 pl-4"></ul>
            </div>

            <div id="income-import-success" class="hidden rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-[13px] text-emerald-300"></div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" id="income-import-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="income-import-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Import</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('income-import-modal');
        var openBtn = document.getElementById('income-import-open');
        var closeBtn = document.getElementById('income-import-close');
        var cancelBtn = document.getElementById('income-import-cancel');
        var form = document.getElementById('income-import-form');
        var fileInput = document.getElementById('income-import-file');
        var submitBtn = document.getElementById('income-import-submit');
        var errorsBox = document.getElementById('income-import-errors');
        var errorsTitle = document.getElementById('income-import-errors-title');
        var errorsList = document.getElementById('income-import-errors-list');
        var successBox = document.getElementById('income-import-success');

        var endpoint = '{{ route('treasurer.cash-incomes.import.store') }}';
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
                    window.dispatchEvent(new CustomEvent('incomes:refresh'));
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
