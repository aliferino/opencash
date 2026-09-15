<div class="mt-6 flex items-center justify-between gap-3">
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

<div
    id="users-table"
    data-endpoint="{{ route('admin.users.index') }}"
    class="mt-4 overflow-hidden rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Nama</th>
                <th class="px-6 py-4 font-medium">Email</th>
                <th class="px-6 py-4 font-medium">Peran</th>
                <th class="px-6 py-4 font-medium">Kelas</th>
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

        var roleLabels = { admin: 'Admin', treasurer: 'Bendahara', student: 'Siswa' };

        function emptyRow(text) {
            return '<tr><td colspan="5" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function renderRow(user) {
            var roleLabel = roleLabels[user.role] || '—';
            var groupName = user.group ? user.group.name : '—';

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + user.name + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + user.email + '</td>' +
                    '<td class="px-6 py-4"><span class="rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">' + roleLabel + '</span></td>' +
                    '<td class="px-6 py-4 text-muted">' + groupName + '</td>' +
                    '<td class="px-6 py-4 text-right">' +
                        '<button type="button" class="rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" title="Kelola">' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
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

        window.addEventListener('users:refresh', function () { load(1); });

        load(page);
    })();
</script>