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

    <button
        type="button"
        id="schedule-create-open"
        class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
    >
        <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
        Tagihan
    </button>
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
                <th class="px-6 py-4 font-medium">Nominal</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="schedules-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="4" class="px-6 py-10 text-center text-muted">Memuat data...</td>
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
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function emptyRow(text) {
            return '<tr><td colspan="4" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function renderRow(schedule) {
            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(schedule.description) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + formatDate(schedule.due_date) + '</td>' +
                    '<td class="px-6 py-4 text-ink">' + formatRupiah(schedule.amount) + '</td>' +
                    '<td class="relative px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="schedule-actions-btn rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" ' +
                            'title="Aksi" ' +
                            'data-id="' + schedule.id + '" ' +
                            'data-description="' + escapeHtml(schedule.description) + '" ' +
                            'data-due-date="' + String(schedule.due_date).slice(0, 10) + '" ' +
                            'data-amount="' + schedule.amount + '"' +
                        '>' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
                        '</button>' +
                        '<div class="schedule-actions-menu hidden absolute right-6 top-full z-20 mt-1 w-36 overflow-hidden rounded-md border border-line bg-surface shadow-lg">' +
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

        body.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.schedule-actions-btn');
            if (toggleBtn) {
                var menu = toggleBtn.nextElementSibling;
                var wasHidden = menu.classList.contains('hidden');
                closeAllMenus();
                if (wasHidden) menu.classList.remove('hidden');
                return;
            }

            var editBtn = e.target.closest('.schedule-action-edit');
            if (editBtn) {
                var actionsBtn = editBtn.closest('td').querySelector('.schedule-actions-btn');
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('schedule:manage', {
                    detail: {
                        id: actionsBtn.dataset.id,
                        description: actionsBtn.dataset.description,
                        dueDate: actionsBtn.dataset.dueDate,
                        amount: actionsBtn.dataset.amount,
                    },
                }));
                return;
            }

            var deleteBtn = e.target.closest('.schedule-action-delete');
            if (deleteBtn) {
                var delActionsBtn = deleteBtn.closest('td').querySelector('.schedule-actions-btn');
                closeAllMenus();
                if (!confirm('Hapus tagihan "' + delActionsBtn.dataset.description + '"? Tindakan ini tidak bisa dibatalkan.')) return;

                fetch('{{ url('treasurer/cash-schedules') }}/' + delActionsBtn.dataset.id, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.status === 204 ? null : res.json();
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
