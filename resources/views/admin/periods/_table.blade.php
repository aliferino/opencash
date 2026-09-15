<div class="mt-6 flex items-center justify-between gap-3">
    <div class="relative max-w-sm flex-1">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
        <input
            type="text"
            id="periods-search"
            placeholder="Cari nama periode..."
            class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
        />
    </div>

    <button
        type="button"
        id="period-create-open"
        class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
    >
        <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
        Periode
    </button>
</div>

<div
    id="periods-table"
    data-endpoint="{{ route('admin.periods.index') }}"
    class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Nama Periode</th>
                <th class="px-6 py-4 font-medium">Interval</th>
                <th class="px-6 py-4 font-medium">Dipakai</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="periods-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="4" class="px-6 py-10 text-center text-muted">Memuat data...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    (function () {
        var container = document.getElementById('periods-table');
        var body = document.getElementById('periods-table-body');
        var searchInput = document.getElementById('periods-search');
        var endpoint = container.dataset.endpoint;
        var searchTimer = null;

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function emptyRow(text) {
            return '<tr><td colspan="4" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function renderRow(period) {
            var usageCount = period.group_settings_count || 0;

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(period.name) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(period.interval_days) + ' hari</td>' +
                    '<td class="px-6 py-4"><span class="rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">' + usageCount + ' pengaturan</span></td>' +
                    '<td class="relative px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="period-actions-btn rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" ' +
                            'title="Aksi" ' +
                            'data-id="' + period.id + '" ' +
                            'data-name="' + escapeHtml(period.name) + '" ' +
                            'data-interval-days="' + period.interval_days + '"' +
                        '>' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
                        '</button>' +
                        '<div class="period-actions-menu hidden absolute right-6 top-full z-20 mt-1 w-36 overflow-hidden rounded-md border border-line bg-surface shadow-lg">' +
                            '<button type="button" class="period-action-edit flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                '<i data-lucide="pencil" class="h-3.5 w-3.5" stroke-width="1.8"></i> Edit' +
                            '</button>' +
                            '<button type="button" class="period-action-delete flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-red-400 transition-colors hover:bg-red-500/10">' +
                                '<i data-lucide="trash-2" class="h-3.5 w-3.5" stroke-width="1.8"></i> Hapus' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }

        function closeAllMenus() {
            document.querySelectorAll('.period-actions-menu').forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }

        function load() {
            body.innerHTML = emptyRow('Memuat data...');

            var url = endpoint;
            if (searchInput.value.trim()) {
                url += '?search=' + encodeURIComponent(searchInput.value.trim());
            }

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (periods) {
                    body.innerHTML = periods.length
                        ? periods.map(renderRow).join('')
                        : emptyRow('Belum ada periode.');

                    if (window.lucideRefresh) window.lucideRefresh();
                })
                .catch(function () {
                    body.innerHTML = emptyRow('Gagal memuat data.');
                });
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(load, 350);
        });

        body.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.period-actions-btn');
            if (toggleBtn) {
                var menu = toggleBtn.nextElementSibling;
                var wasHidden = menu.classList.contains('hidden');
                closeAllMenus();
                if (wasHidden) menu.classList.remove('hidden');
                return;
            }

            var editBtn = e.target.closest('.period-action-edit');
            if (editBtn) {
                var actionsBtn = editBtn.closest('td').querySelector('.period-actions-btn');
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('period:manage', {
                    detail: {
                        id: actionsBtn.dataset.id,
                        name: actionsBtn.dataset.name,
                        intervalDays: actionsBtn.dataset.intervalDays,
                    },
                }));
                return;
            }

            var deleteBtn = e.target.closest('.period-action-delete');
            if (deleteBtn) {
                var delActionsBtn = deleteBtn.closest('td').querySelector('.period-actions-btn');
                closeAllMenus();
                if (!confirm('Hapus periode "' + delActionsBtn.dataset.name + '"? Tindakan ini tidak bisa dibatalkan.')) return;

                fetch(endpoint + '/' + delActionsBtn.dataset.id, {
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
                    .catch(function (d) { alert((d && d.message) || 'Gagal menghapus periode.'); });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.period-actions-btn') && !e.target.closest('.period-actions-menu')) {
                closeAllMenus();
            }
        });

        window.addEventListener('periods:refresh', load);

        load();
    })();
</script>
