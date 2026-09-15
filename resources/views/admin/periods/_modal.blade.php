<div id="period-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 id="period-modal-title" class="text-lg font-semibold text-ink">Tambah Periode</h2>
            <button type="button" id="period-modal-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <form id="period-modal-form" class="mt-5 space-y-4">
            <div>
                <label for="period-modal-name" class="text-[13px] font-medium text-ink">Nama Periode</label>
                <input type="text" id="period-modal-name" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: Mingguan" />
            </div>

            <div>
                <label for="period-modal-interval" class="text-[13px] font-medium text-ink">Interval (hari)</label>
                <input type="number" id="period-modal-interval" min="1" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: 7" />
            </div>

            <p id="period-modal-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="period-modal-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="period-modal-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('period-modal');
        var openCreateBtn = document.getElementById('period-create-open');
        var closeBtn = document.getElementById('period-modal-close');
        var cancelBtn = document.getElementById('period-modal-cancel');
        var titleEl = document.getElementById('period-modal-title');
        var submitBtn = document.getElementById('period-modal-submit');
        var form = document.getElementById('period-modal-form');
        var nameInput = document.getElementById('period-modal-name');
        var intervalInput = document.getElementById('period-modal-interval');
        var errorEl = document.getElementById('period-modal-error');
        var baseUrl = '{{ route('admin.periods.index') }}';

        var mode = 'create';
        var currentId = null;

        function showError(msg) {
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
        }

        function clearMessages() {
            errorEl.classList.add('hidden');
        }

        function openCreate() {
            mode = 'create';
            currentId = null;
            titleEl.textContent = 'Tambah Periode';
            submitBtn.textContent = 'Simpan';
            form.reset();
            clearMessages();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function openEdit(detail) {
            mode = 'edit';
            currentId = detail.id;
            titleEl.textContent = 'Edit Periode';
            submitBtn.textContent = 'Simpan Perubahan';
            nameInput.value = detail.name;
            intervalInput.value = detail.intervalDays;
            clearMessages();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentId = null;
        }

        openCreateBtn.addEventListener('click', openCreate);
        window.addEventListener('period:manage', function (e) { openEdit(e.detail); });
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearMessages();

            var payload = {
                name: nameInput.value,
                interval_days: intervalInput.value,
            };

            var url = mode === 'create' ? baseUrl : baseUrl + '/' + currentId;
            var method = mode === 'create' ? 'POST' : 'PUT';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payload),
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function () {
                    close();
                    window.dispatchEvent(new CustomEvent('periods:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan periode.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    showError(msg);
                });
        });
    })();
</script>
