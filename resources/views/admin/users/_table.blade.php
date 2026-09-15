<div class="mt-6 flex items-center justify-between gap-3">
    <div class="flex flex-1 items-center gap-3">
        <div class="relative max-w-sm flex-1">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
            <input
                type="text"
                id="users-search"
                placeholder="Cari nama pengguna..."
                class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
            />
        </div>

        <select
            id="users-role-filter"
            class="shrink-0 rounded-md border border-line bg-surface px-3 py-2.5 text-[13px] text-ink outline-none focus:border-accent"
        >
            <option value="">Semua Peran</option>
            <option value="admin">Admin</option>
            <option value="treasurer">Bendahara</option>
            <option value="student">Siswa</option>
        </select>
    </div>

    <button
        type="button"
        id="user-create-open"
        class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
    >
        <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
        Pengguna
    </button>
</div>

<div
    id="users-table"
    data-endpoint="{{ route('admin.users.index') }}"
    class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Nama</th>
                <th class="px-6 py-4 font-medium">Email</th>
                <th class="px-6 py-4 font-medium">Peran</th>
                <th class="px-6 py-4 font-medium">Grup</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="users-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-muted">Memuat data...</td>
            </tr>
        </tbody>
    </table>

    <div class="flex items-center justify-between border-t border-line px-6 py-4">
        <p id="users-table-info" class="text-xs text-muted"></p>
        <div class="flex items-center gap-2">
            <button type="button" id="users-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
            <button type="button" id="users-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var container = document.getElementById('users-table');
        var body = document.getElementById('users-table-body');
        var info = document.getElementById('users-table-info');
        var prevBtn = document.getElementById('users-prev');
        var nextBtn = document.getElementById('users-next');
        var searchInput = document.getElementById('users-search');
        var roleFilter = document.getElementById('users-role-filter');
        var endpoint = container.dataset.endpoint;
        var page = 1;
        var searchTimer = null;
        var currentUserId = '{{ auth()->id() }}';

        var roleLabels = { admin: 'Admin', treasurer: 'Bendahara', student: 'Siswa' };

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

        function renderRow(user) {
            var roleLabel = roleLabels[user.role] || '—';
            var groupName = user.group ? user.group.name : '—';
            var groupId = user.group ? user.group.id : '';
            var canDelete = String(user.id) !== currentUserId;

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(user.name) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(user.email) + '</td>' +
                    '<td class="px-6 py-4"><span class="rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">' + roleLabel + '</span></td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(groupName) + '</td>' +
                    '<td class="relative px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="user-actions-btn rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" ' +
                            'title="Aksi" ' +
                            'data-id="' + user.id + '" ' +
                            'data-name="' + escapeHtml(user.name) + '" ' +
                            'data-email="' + escapeHtml(user.email) + '" ' +
                            'data-role="' + escapeHtml(user.role || '') + '" ' +
                            'data-group-id="' + groupId + '" ' +
                            'data-group-name="' + escapeHtml(groupName) + '"' +
                        '>' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
                        '</button>' +
                        '<div class="user-actions-menu hidden absolute right-6 top-full z-20 mt-1 w-36 overflow-hidden rounded-md border border-line bg-surface shadow-lg">' +
                            '<button type="button" class="user-action-edit flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                '<i data-lucide="pencil" class="h-3.5 w-3.5" stroke-width="1.8"></i> Edit' +
                            '</button>' +
                            (canDelete
                                ? '<button type="button" class="user-action-delete flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-red-400 transition-colors hover:bg-red-500/10">' +
                                    '<i data-lucide="trash-2" class="h-3.5 w-3.5" stroke-width="1.8"></i> Hapus' +
                                  '</button>'
                                : '') +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }

        function closeAllMenus() {
            document.querySelectorAll('.user-actions-menu').forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }

        function load(targetPage) {
            body.innerHTML = emptyRow('Memuat data...');

            var url = endpoint + '?page=' + targetPage;
            if (searchInput.value.trim()) {
                url += '&search=' + encodeURIComponent(searchInput.value.trim());
            }
            if (roleFilter.value) {
                url += '&role=' + roleFilter.value;
            }

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    page = json.current_page;

                    body.innerHTML = json.data.length
                        ? json.data.map(renderRow).join('')
                        : emptyRow('Belum ada pengguna.');

                    info.textContent = 'Menampilkan ' + json.data.length + ' dari ' + json.total + ' pengguna — halaman ' + json.current_page + ' dari ' + json.last_page;
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
        roleFilter.addEventListener('change', function () { load(1); });

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { load(1); }, 350);
        });

        body.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.user-actions-btn');
            if (toggleBtn) {
                var menu = toggleBtn.nextElementSibling;
                var wasHidden = menu.classList.contains('hidden');
                closeAllMenus();
                if (wasHidden) menu.classList.remove('hidden');
                return;
            }

            var editBtn = e.target.closest('.user-action-edit');
            if (editBtn) {
                var actionsBtn = editBtn.closest('td').querySelector('.user-actions-btn');
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('user:manage', {
                    detail: {
                        id: actionsBtn.dataset.id,
                        name: actionsBtn.dataset.name,
                        email: actionsBtn.dataset.email,
                        role: actionsBtn.dataset.role,
                        groupName: actionsBtn.dataset.groupName,
                    },
                }));
                return;
            }

            var deleteBtn = e.target.closest('.user-action-delete');
            if (deleteBtn) {
                var delActionsBtn = deleteBtn.closest('td').querySelector('.user-actions-btn');
                closeAllMenus();
                if (!confirm('Hapus pengguna "' + delActionsBtn.dataset.name + '"? Tindakan ini tidak bisa dibatalkan.')) return;

                fetch(endpoint + '/' + delActionsBtn.dataset.id, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.json();
                    })
                    .then(function () { load(page); })
                    .catch(function (d) { alert((d && d.message) || 'Gagal menghapus pengguna.'); });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.user-actions-btn') && !e.target.closest('.user-actions-menu')) {
                closeAllMenus();
            }
        });

        window.addEventListener('users:refresh', function () { load(1); });

        load(page);
    })();
</script>