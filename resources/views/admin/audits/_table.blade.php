<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-1 flex-wrap items-center gap-3">
        <div class="relative min-w-[220px] max-w-sm flex-1">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
            <input
                type="text"
                id="audits-search"
                placeholder="Cari objek, pelaku, atau grup..."
                class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
            />
        </div>

        <select id="audits-type-filter" class="shrink-0 rounded-md border border-line bg-surface px-3 py-2.5 text-[13px] text-ink outline-none focus:border-accent">
            <option value="">Semua Objek</option>
            <option value="user">Pengguna</option>
            <option value="group">Grup</option>
        </select>

        <select id="audits-action-filter" class="shrink-0 rounded-md border border-line bg-surface px-3 py-2.5 text-[13px] text-ink outline-none focus:border-accent">
            <option value="">Semua Aksi</option>
            <option value="created">Dibuat</option>
            <option value="registered">Daftar Sendiri</option>
            <option value="updated">Diubah</option>
            <option value="role_changed">Peran Diubah</option>
            <option value="group_changed">Pindah Grup</option>
            <option value="joined">Masuk Grup</option>
            <option value="invite_code_refreshed">Kode Undangan Diperbarui</option>
            <option value="deleted">Dihapus</option>
        </select>
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
                <th class="px-6 py-4 font-medium">Aksi</th>
                <th class="px-6 py-4 font-medium">Objek</th>
                <th class="px-6 py-4 font-medium">Pelaku</th>
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
        var typeFilter = document.getElementById('audits-type-filter');
        var actionFilter = document.getElementById('audits-action-filter');
        var endpoint = container.dataset.endpoint;
        var page = 1;
        var searchTimer = null;

        var actionLabels = {
            created: 'Dibuat',
            registered: 'Daftar Sendiri',
            updated: 'Diubah',
            role_changed: 'Peran Diubah',
            group_changed: 'Pindah Grup',
            joined: 'Masuk Grup',
            invite_code_refreshed: 'Kode Undangan Diperbarui',
            deleted: 'Dihapus',
        };

        var actionClasses = {
            created: 'bg-emerald-500/15 text-emerald-300',
            registered: 'bg-emerald-500/15 text-emerald-300',
            updated: 'bg-accent-tint text-accent-bright',
            role_changed: 'bg-amber-500/15 text-amber-300',
            group_changed: 'bg-amber-500/15 text-amber-300',
            joined: 'bg-emerald-500/15 text-emerald-300',
            invite_code_refreshed: 'bg-amber-500/15 text-amber-300',
            deleted: 'bg-red-500/15 text-red-300',
        };

        var subjectLabels = { user: 'Pengguna', group: 'Grup' };

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

        function formatDate(value) {
            if (!value) return '—';
            var date = new Date(value);
            if (isNaN(date.getTime())) return value;
            return date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function summarize(audit) {
            var newValues = audit.new_values || {};
            var oldValues = audit.old_values || {};
            var keys = Object.keys(newValues).length ? Object.keys(newValues) : Object.keys(oldValues);

            if (!keys.length) return '—';

            return keys.map(function (key) {
                if (key === 'password') return 'Password';
                if (key === 'invite_code') return 'Kode Undangan';
                if (key === 'role') {
                    var roleNames = { admin: 'Admin', treasurer: 'Bendahara', student: 'Siswa' };
                    var from = oldValues[key] ? roleNames[oldValues[key]] || oldValues[key] : null;
                    var to = newValues[key] ? roleNames[newValues[key]] || newValues[key] : null;
                    return from && to ? 'Peran: ' + from + ' → ' + to : 'Peran';
                }
                if (key === 'group_id') {
                    var from = oldValues[key] ? 'Grup #' + oldValues[key] : 'Tanpa grup';
                    var to = newValues[key] ? 'Grup #' + newValues[key] : 'Tanpa grup';
                    return from + ' → ' + to;
                }
                if (key === 'name') return 'Nama';
                if (key === 'email') return 'Email';
                return key;
            }).join(', ');
        }

        function renderRow(audit) {
            var actionLabel = actionLabels[audit.action] || audit.action;
            var actionClass = actionClasses[audit.action] || 'bg-white/5 text-muted';
            var subjectType = subjectLabels[audit.subject_type] || audit.subject_type;
            var actorName = audit.actor_name || 'Sistem';
            var actorNote = String(audit.actor_id) === '{{ auth()->id() }}' ? ' (Anda)' : '';

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 text-muted">' + formatDate(audit.created_at) + '</td>' +
                    '<td class="px-6 py-4"><span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium ' + actionClass + '">' + escapeHtml(actionLabel) + '</span></td>' +
                    '<td class="px-6 py-4">' +
                        '<p class="font-medium text-ink">' + escapeHtml(audit.subject_name || '—') + '</p>' +
                        '<p class="text-xs text-muted">' + escapeHtml(subjectType) + (audit.group_name ? ' · ' + escapeHtml(audit.group_name) : '') + '</p>' +
                    '</td>' +
                    '<td class="px-6 py-4">' +
                        '<p class="text-ink">' + escapeHtml(actorName) + escapeHtml(actorNote) + '</p>' +
                        '<p class="text-xs text-muted">' + escapeHtml(summarize(audit)) + '</p>' +
                    '</td>' +
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
            if (searchInput.value.trim()) url += '&search=' + encodeURIComponent(searchInput.value.trim());
            if (typeFilter.value) url += '&subject_type=' + typeFilter.value;
            if (actionFilter.value) url += '&action=' + actionFilter.value;

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
        typeFilter.addEventListener('change', function () { load(1); });
        actionFilter.addEventListener('change', function () { load(1); });

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
