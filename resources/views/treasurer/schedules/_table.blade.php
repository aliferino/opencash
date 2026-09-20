<div class="mt-6 flex items-center justify-between gap-3">
    <div class="relative max-w-sm flex-1">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
        <input
            type="text"
            id="schedules-search"
            placeholder="Cari deskripsi tagihan..."
            class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
        />
    </div>

    <div class="flex shrink-0 items-center gap-2">
        <button
            type="button"
            id="schedule-import-open"
            class="inline-flex items-center gap-2 rounded-md border border-line bg-surface px-4 py-2.5 text-[14px] font-medium text-ink transition-colors hover:border-accent hover:text-accent-bright"
        >
            <i data-lucide="upload" class="h-4 w-4" stroke-width="1.8"></i>
            Import Excel
        </button>

        <button
            type="button"
            id="schedule-create-open"
            class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
            Tagihan
        </button>
    </div>
</div>

<div
    id="schedules-table"
    data-endpoint="{{ route('cash-schedules.index') }}"
    class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Deskripsi</th>
                <th class="px-6 py-4 font-medium">Jatuh Tempo</th>
                <th class="px-6 py-4 font-medium text-right">Nominal</th>
                <th class="px-6 py-4 font-medium">Terkumpul</th>
                <th class="px-6 py-4 font-medium">Siswa</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="schedules-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-muted">Memuat data...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    (function () {
        var container = document.getElementById('schedules-table');
        var body = document.getElementById('schedules-table-body');
        var searchInput = document.getElementById('schedules-search');
        var endpoint = container.dataset.endpoint;
        var searchTimer = null;
        var allSchedules = [];

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

        function emptyRow(text) {
            return '<tr><td colspan="6" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function progressCell(progress) {
            if (!progress || !progress.target) {
                return '<span class="text-muted">Belum ada siswa</span>';
            }

            var percent = Math.min(100, Math.round(progress.paid / progress.target * 100));
            var complete = progress.remaining === 0;
            var studentCount = (progress.paid_students || 0) + (progress.partial_students || 0) + (progress.unpaid_students || 0);

            return '' +
                '<div class="min-w-[10rem]">' +
                    '<div class="flex items-center justify-between gap-2 text-[13px]">' +
                        '<span class="' + (complete ? 'text-emerald-400' : 'text-ink') + '">' + formatRupiah(progress.paid) + '</span>' +
                        '<span class="text-muted">' + percent + '%</span>' +
                    '</div>' +
                    '<div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-white/8">' +
                        '<span class="block h-full rounded-full ' + (complete ? 'bg-emerald-400' : 'bg-accent') + '" style="width: ' + percent + '%"></span>' +
                    '</div>' +
                    '<p class="mt-1 text-[11px] text-muted">dari target ' + formatRupiah(progress.target) +
                        ' (' + studentCount + ' siswa)' +
                        (progress.pending > 0 ? ' &middot; ' + formatRupiah(progress.pending) + ' menunggu' : '') +
                    '</p>' +
                '</div>';
        }

        function studentCell(progress) {
            if (!progress || !progress.target) return '<span class="text-muted">—</span>';

            var parts = [];
            if (progress.paid_students > 0) parts.push('<span class="text-emerald-400">' + progress.paid_students + ' lunas</span>');
            if (progress.partial_students > 0) parts.push('<span class="text-amber-400">' + progress.partial_students + ' kurang</span>');
            if (progress.unpaid_students > 0) parts.push('<span class="text-red-400">' + progress.unpaid_students + ' belum</span>');

            return '<div class="space-y-0.5 text-[12.5px]">' + (parts.length ? parts.map(function (p) { return '<div>' + p + '</div>'; }).join('') : '<span class="text-muted">—</span>') + '</div>';
        }

        function renderRow(schedule) {
            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(schedule.description) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + formatDate(schedule.due_date) + '</td>' +
                    '<td class="px-6 py-4 text-right text-ink">' + formatRupiah(schedule.amount) + '</td>' +
                    '<td class="px-6 py-4">' + progressCell(schedule.progress) + '</td>' +
                    '<td class="px-6 py-4">' + studentCell(schedule.progress) + '</td>' +
                    '<td class="relative px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="schedule-actions-btn rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" ' +
                            'title="Aksi" ' +
                            'data-id="' + schedule.id + '" ' +
                            'data-description="' + escapeHtml(schedule.description) + '" ' +
                            'data-due-date="' + String(schedule.due_date).slice(0, 10) + '" ' +
                            'data-amount="' + schedule.amount + '" ' +
                            'data-progress="' + escapeHtml(JSON.stringify(schedule.progress || null)) + '"' +
                        '>' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
                        '</button>' +
                        '<div class="schedule-actions-menu hidden absolute right-6 top-full z-20 mt-1 w-40 overflow-hidden rounded-md border border-line bg-surface shadow-lg">' +
                            '<button type="button" class="schedule-action-detail flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                '<i data-lucide="users" class="h-3.5 w-3.5" stroke-width="1.8"></i> Rincian' +
                            '</button>' +
                            '<button type="button" class="schedule-action-edit flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                '<i data-lucide="pencil" class="h-3.5 w-3.5" stroke-width="1.8"></i> Edit' +
                            '</button>' +
                            '<button type="button" class="schedule-action-delete flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-red-400 transition-colors hover:bg-red-500/10">' +
                                '<i data-lucide="trash-2" class="h-3.5 w-3.5" stroke-width="1.8"></i> Hapus' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }

        function closeAllMenus() {
            document.querySelectorAll('.schedule-actions-menu').forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }

        function renderList(schedules) {
            body.innerHTML = schedules.length
                ? schedules.map(renderRow).join('')
                : emptyRow('Belum ada jadwal tagihan.');

            if (window.lucideRefresh) window.lucideRefresh();
        }

        function applySearch() {
            var term = searchInput.value.trim().toLowerCase();
            if (!term) return renderList(allSchedules);

            renderList(allSchedules.filter(function (schedule) {
                return String(schedule.description).toLowerCase().indexOf(term) !== -1;
            }));
        }

        function load() {
            body.innerHTML = emptyRow('Memuat data...');

            fetch(endpoint, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (schedules) {
                    allSchedules = schedules;
                    applySearch();
                })
                .catch(function () {
                    body.innerHTML = emptyRow('Gagal memuat data.');
                });
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(applySearch, 250);
        });

        function actionsBtnOf(el) {
            return el.closest('td').querySelector('.schedule-actions-btn');
        }

        body.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.schedule-actions-btn');
            if (toggleBtn) {
                var menu = toggleBtn.nextElementSibling;
                var wasHidden = menu.classList.contains('hidden');
                closeAllMenus();
                if (wasHidden) menu.classList.remove('hidden');
                return;
            }

            var detailBtn = e.target.closest('.schedule-action-detail');
            if (detailBtn) {
                var btn = actionsBtnOf(detailBtn);
                var progress = null;
                try { progress = JSON.parse(btn.dataset.progress || 'null'); } catch (err) { progress = null; }
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('schedule:detail', {
                    detail: {
                        description: btn.dataset.description,
                        dueDate: btn.dataset.dueDate,
                        amount: btn.dataset.amount,
                        progress: progress,
                    },
                }));
                return;
            }

            var editBtn = e.target.closest('.schedule-action-edit');
            if (editBtn) {
                var editActions = actionsBtnOf(editBtn);
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('schedule:manage', {
                    detail: {
                        id: editActions.dataset.id,
                        description: editActions.dataset.description,
                        dueDate: editActions.dataset.dueDate,
                        amount: editActions.dataset.amount,
                    },
                }));
                return;
            }

            var deleteBtn = e.target.closest('.schedule-action-delete');
            if (deleteBtn) {
                var delActions = actionsBtnOf(deleteBtn);
                closeAllMenus();
                if (!confirm('Hapus tagihan "' + delActions.dataset.description + '"? Tindakan ini tidak bisa dibatalkan.')) return;

                fetch('{{ url('treasurer/cash-schedules') }}/' + delActions.dataset.id, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.status(204) ? null : res.json();
                    })
                    .then(function () { load(); })
                    .catch(function (d) { alert((d && d.message) || 'Gagal menghapus tagihan.'); });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.schedule-actions-btn') && !e.target.closest('.schedule-actions-menu')) {
                closeAllMenus();
            }
        });

        window.addEventListener('schedules:refresh', load);

        load();
    })();
</script>
