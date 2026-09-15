<div class="mt-6 flex items-center justify-between gap-3">
    <div class="relative max-w-sm flex-1">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
        <input
            type="text"
            id="audits-search"
            placeholder="Cari nama atau email pengguna..."
            class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
        />
    </div>
</div>

<div
    id="audits-table"
    data-endpoint="{{ route('admin.audits.index') }}"
    class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Waktu</th>
                <th class="px-6 py-4 font-medium">Pengguna</th>
                <th class="px-6 py-4 font-medium">Perubahan</th>
                <th class="px-6 py-4 font-medium">Diubah Oleh</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="audits-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-muted">Memuat data...</td>
            </tr>
        </tbody>
    </table>

    <div class="flex items-center justify-between border-t border-line px-6 py-4">
        <p id="audits-table-info" class="text-xs text-muted"></p>
        <div class="flex items-center gap-2">
            <button type="button" id="audits-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
            <button type="button" id="audits-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var container = document.getElementById('audits-table');
        var body = document.getElementById('audits-table-body');
        var info = document.getElementById('audits-table-info');
        var prevBtn = document.getElementById('audits-prev');
        var nextBtn = document.getElementById('audits-next');
        var searchInput = document.getElementById('audits-search');
        var endpoint = container.dataset.endpoint;
        var page = 1;
        var searchTimer = null;

        var fieldLabels = { name: 'Nama', email: 'Email', role: 'Peran', group_id: 'Grup', password: 'Password' };

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function emptyRow(text) {
            return '<tr><td colspan="5" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function fieldLabel(key) {
            return fieldLabels[key] || key;
        }

        function summarizeChanges(newValues) {
            var keys = Object.keys(newValues || {});
            if (!keys.length) return '—';
            return keys.map(fieldLabel).join(', ');
        }

        function formatDate(value) {
            if (!value) return '—';
            var date = new Date(value);
            if (isNaN(date.getTime())) return value;
            return date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function renderRow(audit) {
            var userName = audit.user ? audit.user.name : 'Pengguna terhapus';
            var userEmail = audit.user ? audit.user.email : '';
            var updatedByName = audit.updatedBy ? audit.updatedBy.name : '—';

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 text-muted">' + formatDate(audit.created_at) + '</td>' +
                    '<td class="px-6 py-4">' +
                        '<p class="font-medium text-ink">' + escapeHtml(userName) + '</p>' +
                        (userEmail ? '<p class="text-xs text-muted">' + escapeHtml(userEmail) + '</p>' : '') +
                    '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(summarizeChanges(audit.new_values)) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(updatedByName) + '</td>' +
                    '<td class="px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="audit-detail-btn inline-flex items-center gap-1.5 rounded-md border border-line px-3 py-1.5 text-[13px] text-ink transition-colors hover:bg-white/5" ' +
                            'data-audit="' + escapeHtml(JSON.stringify(audit)) + '"' +
                        '>' +
                            '<i data-lucide="eye" class="h-3.5 w-3.5" stroke-width="1.8"></i> Detail' +
                        '</button>' +
                    '</td>' +
                '</tr>';
        }

        function load(targetPage) {
            body.innerHTML = emptyRow('Memuat data...');

            var url = endpoint + '?page=' + targetPage;
            if (searchInput.value.trim()) {
                url += '&search=' + encodeURIComponent(searchInput.value.trim());
            }

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    page = json.current_page;

                    body.innerHTML = json.data.length
                        ? json.data.map(renderRow).join('')
                        : emptyRow('Belum ada log aktivitas.');

                    info.textContent = 'Menampilkan ' + json.data.length + ' dari ' + json.total + ' log — halaman ' + json.current_page + ' dari ' + json.last_page;
                    prevBtn.disabled = !json.prev_page_url;
                    nextBtn.disabled = !json.next_page_url;

                    if (window.lucideRefresh) window.lucideRefresh();
                })
                .catch(function () {
                    body.innerHTML = emptyRow('Gagal memuat data.');
                });
        }

        prevBtn.addEventListener('click', function () { load(page - 1); });
        nextBtn.addEventListener('click', function () { load(page + 1); });

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { load(1); }, 350);
        });

        body.addEventListener('click', function (e) {
            var detailBtn = e.target.closest('.audit-detail-btn');
            if (detailBtn) {
                window.dispatchEvent(new CustomEvent('audit:view', {
                    detail: JSON.parse(detailBtn.dataset.audit),
                }));
            }
        });

        load(page);
    })();
</script>
