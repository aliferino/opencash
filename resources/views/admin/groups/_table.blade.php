<div class="mt-6 max-w-sm">
    <div class="relative">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
        <input
            type="text"
            id="groups-search"
            placeholder="Cari nama kelas..."
            class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
        />
    </div>
</div>

<div
    id="groups-table"
    data-endpoint="{{ route('admin.groups.index') }}"
    class="mt-4 overflow-hidden rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Nama Kelas</th>
                <th class="px-6 py-4 font-medium">Kode Undangan</th>
                <th class="px-6 py-4 font-medium">Anggota</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="groups-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="4" class="px-6 py-10 text-center text-muted">Memuat data...</td>
            </tr>
        </tbody>
    </table>

    <div class="flex items-center justify-between border-t border-line px-6 py-4">
        <p id="groups-table-info" class="text-xs text-muted"></p>
        <div class="flex items-center gap-2">
            <button type="button" id="groups-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
            <button type="button" id="groups-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var container = document.getElementById('groups-table');
        var body = document.getElementById('groups-table-body');
        var info = document.getElementById('groups-table-info');
        var prevBtn = document.getElementById('groups-prev');
        var nextBtn = document.getElementById('groups-next');
        var searchInput = document.getElementById('groups-search');
        var endpoint = container.dataset.endpoint;
        var page = 1;
        var searchTimer = null;

        function emptyRow(text) {
            return '<tr><td colspan="4" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function renderRow(group) {
            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + group.name + '</td>' +
                    '<td class="px-6 py-4 font-mono text-[13px] text-muted">' + group.invite_code + '</td>' +
                    '<td class="px-6 py-4"><span class="rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">' + group.users_count + ' anggota</span></td>' +
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

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    page = json.current_page;

                    body.innerHTML = json.data.length
                        ? json.data.map(renderRow).join('')
                        : emptyRow('Belum ada kelas.');

                    info.textContent = 'Menampilkan ' + json.data.length + ' dari ' + json.total + ' kelas — halaman ' + json.current_page + ' dari ' + json.last_page;
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

        window.addEventListener('groups:refresh', function () { load(1); });

        load(page);
    })();
</script>