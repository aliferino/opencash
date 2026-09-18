<div class="mt-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-ink">Anggota Grup</h2>
            <p class="mt-1 text-[14px] text-muted">Siswa dan bendahara yang tergabung di kelas Anda.</p>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-1 flex-wrap items-center gap-3">
            <div class="relative min-w-[220px] max-w-sm flex-1">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
                <input
                    type="text"
                    id="members-search"
                    placeholder="Cari nama atau email..."
                    class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
                />
            </div>

            <select id="members-role-filter" class="shrink-0 rounded-md border border-line bg-surface px-3 py-2.5 text-[13px] text-ink outline-none focus:border-accent">
                <option value="">Semua Peran</option>
                <option value="student">Siswa</option>
                <option value="treasurer">Bendahara</option>
            </select>
        </div>

        <button
            type="button"
            id="member-create-open"
            class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
            Anggota
        </button>
    </div>

    <div
        id="members-table"
        data-endpoint="{{ route('treasurer.group.members.index') }}"
        class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
    >
        <table class="w-full text-left text-[14px]">
            <thead>
                <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                    <th class="px-6 py-4 font-medium">Nama</th>
                    <th class="px-6 py-4 font-medium">Email</th>
                    <th class="px-6 py-4 font-medium">Peran</th>
                    <th class="px-6 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="members-table-body" class="divide-y divide-line">
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-muted">Memuat data...</td>
                </tr>
            </tbody>
        </table>

        <div class="flex items-center justify-between border-t border-line px-6 py-4">
            <p id="members-table-info" class="text-xs text-muted"></p>
            <div class="flex items-center gap-2">
                <button type="button" id="members-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
                <button type="button" id="members-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var container = document.getElementById('members-table');
        var body = document.getElementById('members-table-body');
        var info = document.getElementById('members-table-info');
        var prevBtn = document.getElementById('members-prev');
        var nextBtn = document.getElementById('members-next');
        var searchInput = document.getElementById('members-search');
        var roleFilter = document.getElementById('members-role-filter');
        var endpoint = container.dataset.endpoint;
        var page = 1;
        var searchTimer = null;
        var currentUserId = '{{ auth()->id() }}';

        var roleLabels = { treasurer: 'Bendahara', student: 'Siswa' };

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

        function renderRow(user) {
            var roleLabel = roleLabels[user.role] || '—';
            var isSelf = String(user.id) === currentUserId;
            var newRole = user.role === 'treasurer' ? 'student' : 'treasurer';
            var newRoleLabel = roleLabels[newRole];
            var isTreasurer = user.role === 'treasurer';
            var roleClass = isTreasurer ? 'bg-accent-tint text-accent-bright' : 'bg-white/5 text-muted';

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(user.name) + (isSelf ? '<span class="ml-2 text-xs text-muted">(Anda)</span>' : '') + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(user.email) + '</td>' +
                    '<td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium ' + roleClass + '">' + roleLabel + '</span></td>' +
                    '<td class="relative px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="member-actions-btn rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" ' +
                            'title="Aksi" ' +
                            'data-row="' + escapeHtml(JSON.stringify(user)) + '"' +
                        '>' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
                        '</button>' +
                        '<div class="member-actions-menu hidden absolute right-6 top-full z-20 mt-1 w-44 overflow-hidden rounded-md border border-line bg-surface shadow-lg">' +
                            '<button type="button" class="member-action-edit flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                '<i data-lucide="pencil" class="h-3.5 w-3.5" stroke-width="1.8"></i> Edit' +
                            '</button>' +
                            (isSelf
                                ? ''
                                : '<button type="button" class="member-action-role flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                    '<i data-lucide="user-cog" class="h-3.5 w-3.5" stroke-width="1.8"></i> Jadikan ' + newRoleLabel +
                                  '</button>' +
                                  '<button type="button" class="member-action-remove flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-red-400 transition-colors hover:bg-red-500/10">' +
                                    '<i data-lucide="user-minus" class="h-3.5 w-3.5" stroke-width="1.8"></i> Keluarkan' +
                                  '</button>') +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }

        function closeAllMenus() {
            document.querySelectorAll('.member-actions-menu').forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }

        function load(targetPage) {
            body.innerHTML = emptyRow('Memuat data...');

            var url = endpoint + '?page=' + targetPage;
            if (searchInput.value.trim()) url += '&search=' + encodeURIComponent(searchInput.value.trim());
            if (roleFilter.value) url += '&role=' + roleFilter.value;

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    page = json.current_page;

                    body.innerHTML = json.data.length
                        ? json.data.map(renderRow).join('')
                        : emptyRow('Belum ada anggota.');

                    info.textContent = 'Menampilkan ' + json.data.length + ' dari ' + json.total + ' anggota — halaman ' + json.current_page + ' dari ' + json.last_page;
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
            var toggleBtn = e.target.closest('.member-actions-btn');
            if (toggleBtn) {
                var menu = toggleBtn.nextElementSibling;
                var wasHidden = menu.classList.contains('hidden');
                closeAllMenus();
                if (wasHidden) menu.classList.remove('hidden');
                return;
            }

            var editBtn = e.target.closest('.member-action-edit');
            if (editBtn) {
                var editRow = JSON.parse(editBtn.closest('td').querySelector('.member-actions-btn').dataset.row);
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('member:edit', { detail: editRow }));
                return;
            }

            var roleBtn = e.target.closest('.member-action-role');
            if (roleBtn) {
                var roleRow = JSON.parse(roleBtn.closest('td').querySelector('.member-actions-btn').dataset.row);
                var targetRole = roleRow.role === 'treasurer' ? 'student' : 'treasurer';
                var targetLabel = roleLabels[targetRole];
                closeAllMenus();
                if (!confirm('Ubah peran "' + roleRow.name + '" menjadi ' + targetLabel + '?')) return;

                fetch('{{ url('treasurer/group/members') }}/' + roleRow.id + '/role', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ role: targetRole }),
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.json();
                    })
                    .then(function () { load(page); })
                    .catch(function (d) { alert((d && d.message) || 'Gagal mengubah peran anggota.'); });
                return;
            }

            var removeBtn = e.target.closest('.member-action-remove');
            if (removeBtn) {
                var removeRow = JSON.parse(removeBtn.closest('td').querySelector('.member-actions-btn').dataset.row);
                closeAllMenus();
                if (!confirm('Keluarkan "' + removeRow.name + '" dari grup? Akun akan dihapus.')) return;

                fetch('{{ url('treasurer/group/members') }}/' + removeRow.id, {
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
                    .then(function () { load(page); })
                    .catch(function (d) { alert((d && d.message) || 'Gagal mengeluarkan anggota.'); });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.member-actions-btn') && !e.target.closest('.member-actions-menu')) {
                closeAllMenus();
            }
        });

        window.addEventListener('members:refresh', function () { load(1); });

        load(page);
    })();
</script>
