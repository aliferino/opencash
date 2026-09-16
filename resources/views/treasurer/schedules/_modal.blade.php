<div id="schedule-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 id="schedule-modal-title" class="text-lg font-semibold text-ink">Tambah Tagihan</h2>
            <button type="button" id="schedule-modal-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <form id="schedule-modal-form" class="mt-5 space-y-4">
            <div>
                <label for="schedule-modal-description" class="text-[13px] font-medium text-ink">Deskripsi</label>
                <input type="text" id="schedule-modal-description" required maxlength="255" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: Kas Minggu ke-3 Oktober" />
            </div>

            <div>
                <label for="schedule-modal-due-date" class="text-[13px] font-medium text-ink">Jatuh Tempo</label>
                <input type="date" id="schedule-modal-due-date" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" />
            </div>

            <div>
                <label for="schedule-modal-amount" class="text-[13px] font-medium text-ink">Nominal</label>
                <input type="number" id="schedule-modal-amount" min="0" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: 5000" />
            </div>

            <p id="schedule-modal-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="schedule-modal-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="schedule-modal-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('schedule-modal');
        var openCreateBtn = document.getElementById('schedule-create-open');
        var closeBtn = document.getElementById('schedule-modal-close');
        var cancelBtn = document.getElementById('schedule-modal-cancel');
        var titleEl = document.getElementById('schedule-modal-title');
        var submitBtn = document.getElementById('schedule-modal-submit');
        var form = document.getElementById('schedule-modal-form');
        var descriptionInput = document.getElementById('schedule-modal-description');
        var dueDateInput = document.getElementById('schedule-modal-due-date');
        var amountInput = document.getElementById('schedule-modal-amount');
        var errorEl = document.getElementById('schedule-modal-error');
        var baseUrl = '{{ url('treasurer/cash-schedules') }}';

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
            titleEl.textContent = 'Tambah Tagihan';
            submitBtn.textContent = 'Simpan';
            form.reset();
            clearMessages();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function openEdit(detail) {
            mode = 'edit';
            currentId = detail.id;
            titleEl.textContent = 'Edit Tagihan';
            submitBtn.textContent = 'Simpan Perubahan';
            descriptionInput.value = detail.description;
            dueDateInput.value = detail.dueDate;
            amountInput.value = detail.amount;
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
        window.addEventListener('schedule:manage', function (e) { openEdit(e.detail); });
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearMessages();

            var payload = {
                description: descriptionInput.value,
                due_date: dueDateInput.value,
                amount: amountInput.value,
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
                    window.dispatchEvent(new CustomEvent('schedules:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan tagihan.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    showError(msg);
                });
        });
    })();
</script>
