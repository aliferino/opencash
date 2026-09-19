<div id="schedule-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="flex max-h-[88vh] w-full max-w-3xl flex-col rounded-2xl border border-line bg-surface">
        <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5">
            <div class="min-w-0">
                <h2 id="schedule-detail-title" class="truncate text-lg font-semibold text-ink">Rincian Tagihan</h2>
                <p id="schedule-detail-subtitle" class="mt-0.5 text-[13px] text-muted"></p>
            </div>
            <button type="button" id="schedule-detail-close" class="shrink-0 rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <div id="schedule-detail-body" class="min-h-0 flex-1 overflow-y-auto px-6 py-5"></div>
    </div>
</div>

<script>
    (function () {
        var detailModal = document.getElementById('schedule-detail-modal');
        var detailTitle = document.getElementById('schedule-detail-title');
        var detailSubtitle = document.getElementById('schedule-detail-subtitle');
        var detailBody = document.getElementById('schedule-detail-body');
        var detailClose = document.getElementById('schedule-detail-close');

        function formatRupiah(value) {
            return 'Rp' + Number(value || 0).toLocaleString('id-ID');
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function hide() {
            detailModal.classList.add('hidden');
            detailModal.classList.remove('flex');
        }

        // Kartu ringkasan berwarna, sama seperti grid konten di halaman siswa.
        function summaryCard(label, value, sub, tone) {
            var tones = {
                accent: ['bg-accent-tint', 'text-accent-bright'],
                emerald: ['bg-emerald-500/10', 'text-emerald-400'],
                amber: ['bg-amber-500/10', 'text-amber-400'],
                red: ['bg-red-500/10', 'text-red-400'],
            };
            var t = tones[tone] || tones.accent;

            return '' +
                '<div class="rounded-xl border border-line bg-bg p-4">' +
                    '<span class="flex h-8 w-8 items-center justify-center rounded-full ' + t[0] + ' ' + t[1] + '">' +
                        '<i data-lucide="circle-dot" class="h-3.5 w-3.5" stroke-width="1.8"></i>' +
                    '</span>' +
                    '<p class="mt-3 text-[12px] text-muted">' + label + '</p>' +
                    '<p class="mt-0.5 text-lg font-semibold text-ink">' + value + '</p>' +
                    (sub ? '<p class="mt-0.5 text-[11px] text-muted">' + sub + '</p>' : '') +
                '</div>';
        }

        function statusBadge(status) {
            var map = {
                paid: ['Lunas', 'bg-emerald-500/15 text-emerald-300'],
                partial: ['Kurang bayar', 'bg-amber-500/15 text-amber-300'],
                pending: ['Menunggu verifikasi', 'bg-amber-500/15 text-amber-300'],
                unpaid: ['Belum bayar', 'bg-red-500/15 text-red-300'],
            };
            var b = map[status] || map.unpaid;
            return '<span class="rounded-full px-2.5 py-1 text-[11.5px] font-medium ' + b[1] + '">' + b[0] + '</span>';
        }

        function render(detail) {
            var progress = detail.progress;

            detailTitle.textContent = detail.description;
            detailSubtitle.textContent = 'Jatuh tempo ' + (detail.dueDate || '—') +
                ' · nominal ' + formatRupiah(detail.amount) + ' per siswa';

            if (!progress || !progress.target) {
                detailBody.innerHTML = '<p class="py-10 text-center text-[14px] text-muted">Belum ada siswa di kelas ini.</p>';
                if (window.lucideRefresh) window.lucideRefresh();
                return;
            }

            var percent = Math.min(100, Math.round(progress.paid / progress.target * 100));
            var studentCount = (progress.paid_students || 0) + (progress.partial_students || 0) + (progress.unpaid_students || 0);

            var html = '' +
                '<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">' +
                    summaryCard('Terkumpul', formatRupiah(progress.paid), percent + '% dari target', 'emerald') +
                    summaryCard('Target', formatRupiah(progress.target), studentCount + ' siswa', 'accent') +
                    summaryCard('Sisa', formatRupiah(progress.remaining), 'belum masuk kas', 'red') +
                    summaryCard('Menunggu', formatRupiah(progress.pending), 'belum diverifikasi', 'amber') +
                '</div>' +
                '<div class="mt-5 h-2 w-full overflow-hidden rounded-full bg-white/8">' +
                    '<span class="block h-full rounded-full ' + (progress.remaining === 0 ? 'bg-emerald-400' : 'bg-accent') + '" style="width: ' + percent + '%"></span>' +
                '</div>' +
                '<div class="mt-6 flex flex-wrap items-center gap-3 text-[12.5px]">' +
                    '<span class="inline-flex items-center gap-1.5 text-emerald-400"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>' + progress.paid_students + ' lunas</span>' +
                    '<span class="text-line">|</span>' +
                    '<span class="inline-flex items-center gap-1.5 text-amber-400"><span class="h-2 w-2 rounded-full bg-amber-400"></span>' + progress.partial_students + ' kurang bayar</span>' +
                    '<span class="text-line">|</span>' +
                    '<span class="inline-flex items-center gap-1.5 text-red-400"><span class="h-2 w-2 rounded-full bg-red-400"></span>' + progress.unpaid_students + ' belum bayar</span>' +
                '</div>' +
                '<div class="mt-5 overflow-hidden rounded-xl border border-line">' +
                    '<table class="w-full text-left text-[13.5px]">' +
                        '<thead><tr class="border-b border-line bg-bg text-[11px] uppercase tracking-wide text-muted">' +
                            '<th class="px-4 py-3 font-medium">Siswa</th>' +
                            '<th class="px-4 py-3 font-medium text-right">Nominal</th>' +
                            '<th class="px-4 py-3 font-medium text-right">Terbayar</th>' +
                            '<th class="px-4 py-3 font-medium">Status</th>' +
                            '<th class="px-4 py-3 font-medium text-right">Sisa</th>' +
                        '</tr></thead>' +
                        '<tbody class="divide-y divide-line">' +
                            progress.students.map(function (row) {
                                return '<tr>' +
                                    '<td class="px-4 py-3 text-ink">' + escapeHtml(row.student_name) + '</td>' +
                                    '<td class="px-4 py-3 text-right text-muted">' + formatRupiah(row.amount) + '</td>' +
                                    '<td class="px-4 py-3 text-right">' +
                                        '<span class="text-emerald-400">' + formatRupiah(row.paid) + '</span>' +
                                        (row.pending > 0 ? ' <span class="text-amber-400">(+' + formatRupiah(row.pending) + ')</span>' : '') +
                                    '</td>' +
                                    '<td class="px-4 py-3">' + statusBadge(row.status) + '</td>' +
                                    '<td class="px-4 py-3 text-right">' +
                                        (row.remaining > 0
                                            ? '<span class="font-medium text-red-400">' + formatRupiah(row.remaining) + '</span>'
                                            : '<span class="text-muted">—</span>') +
                                    '</td>' +
                                '</tr>';
                            }).join('') +
                        '</tbody>' +
                    '</table>' +
                '</div>';

            detailBody.innerHTML = html;

            if (window.lucideRefresh) window.lucideRefresh();
        }

        window.addEventListener('schedule:detail', function (e) {
            render(e.detail);
            detailModal.classList.remove('hidden');
            detailModal.classList.add('flex');
        });

        detailClose.addEventListener('click', hide);
        detailModal.addEventListener('click', function (e) { if (e.target === detailModal) hide(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !detailModal.classList.contains('hidden')) hide();
        });
    })();
</script>

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
