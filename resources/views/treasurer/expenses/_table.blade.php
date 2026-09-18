<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <div class="relative min-w-[220px] max-w-sm flex-1">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
        <input
            type="text"
            id="expenses-search"
            placeholder="Cari keterangan atau bendahara..."
            class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
        />
    </div>

    <button
        type="button"
        id="expense-create-open"
        class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
    >
        <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
        Pengeluaran
    </button>
</div>

<div
    id="expenses-table"
    data-endpoint="{{ route('cash-expenses.index') }}"
    class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Tanggal</th>
                <th class="px-6 py-4 font-medium">Keterangan</th>
                <th class="px-6 py-4 font-medium">Dicatat Oleh</th>
                <th class="px-6 py-4 font-medium">Nominal</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="expenses-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-muted">Memuat data...</td>
            </tr>
        </tbody>
    </table>

    <div class="flex items-center justify-between border-t border-line px-6 py-4">
        <p id="expenses-table-info" class="text-xs text-muted"></p>
        <div class="flex items-center gap-2">
            <button type="button" id="expenses-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
            <button type="button" id="expenses-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var container = document.getElementById('expenses-table');
        var body = document.getElementById('expenses-table-body');
        var info = document.getElementById('expenses-table-info');
        var prevBtn = document.getElementById('expenses-prev');
        var nextBtn = document.getElementById('expenses-next');
        var searchInput = document.getElementById('expenses-search');
        var endpoint = container.dataset.endpoint;
        var destroyBase = '{{ url('treasurer/cash-expenses') }}';
        var page = 1;
        var searchTimer = null;

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
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function emptyRow(text) {
            return '<tr><td colspan="5" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function renderRow(expense) {
            var treasurer = expense.treasurer ? expense.treasurer.name : '—';

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 text-muted">' + formatDate(expense.expense_date) + '</td>' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(expense.description) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(treasurer) + '</td>' +
                    '<td class="px-6 py-4 text-ink">' + formatRupiah(expense.amount) + '</td>' +
                    '<td class="relative px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="expense-actions-btn rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" ' +
                            'title="Aksi" ' +
                            'data-row="' + escapeHtml(JSON.stringify(expense)) + '"' +
                        '>' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
                        '</button>' +
                        '<div class="expense-actions-menu hidden absolute right-6 top-full z-20 mt-1 w-40 overflow-hidden rounded-md border border-line bg-surface shadow-lg">' +
                            '<button type="button" class="expense-action-detail flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                '<i data-lucide="eye" class="h-3.5 w-3.5" stroke-width="1.8"></i> Detail' +
                            '</button>' +
                            (expense.proof_image
                                ? '<button type="button" class="expense-action-proof flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                    '<i data-lucide="image" class="h-3.5 w-3.5" stroke-width="1.8"></i> Lihat Nota' +
                                  '</button>'
                                : '') +
                            '<button type="button" class="expense-action-delete flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-red-400 transition-colors hover:bg-red-500/10">' +
                                '<i data-lucide="trash-2" class="h-3.5 w-3.5" stroke-width="1.8"></i> Hapus' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }

        function closeAllMenus() {
            document.querySelectorAll('.expense-actions-menu').forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }

        function load(targetPage) {
            body.innerHTML = emptyRow('Memuat data...');

            var url = endpoint + '?page=' + targetPage;
            if (searchInput.value.trim()) url += '&search=' + encodeURIComponent(searchInput.value.trim());

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    page = json.current_page;

                    body.innerHTML = json.data.length
                        ? json.data.map(renderRow).join('')
                        : emptyRow('Belum ada pengeluaran.');

                    info.textContent = 'Menampilkan ' + json.data.length + ' dari ' + json.total + ' pengeluaran — halaman ' + json.current_page + ' dari ' + json.last_page;
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
            var toggleBtn = e.target.closest('.expense-actions-btn');
            if (toggleBtn) {
                var menu = toggleBtn.nextElementSibling;
                var wasHidden = menu.classList.contains('hidden');
                closeAllMenus();
                if (wasHidden) menu.classList.remove('hidden');
                return;
            }

            var detailBtn = e.target.closest('.expense-action-detail');
            if (detailBtn) {
                var detailRow = JSON.parse(detailBtn.closest('td').querySelector('.expense-actions-btn').dataset.row);
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('expense:detail', { detail: detailRow }));
                return;
            }

            var proofBtn = e.target.closest('.expense-action-proof');
            if (proofBtn) {
                var proofRow = JSON.parse(proofBtn.closest('td').querySelector('.expense-actions-btn').dataset.row);
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('expense:proof', { detail: proofRow }));
                return;
            }

            var deleteBtn = e.target.closest('.expense-action-delete');
            if (deleteBtn) {
                var delRow = JSON.parse(deleteBtn.closest('td').querySelector('.expense-actions-btn').dataset.row);
                closeAllMenus();
                if (!confirm('Hapus pengeluaran "' + delRow.description + '"? Tindakan ini tidak bisa dibatalkan.')) return;

                fetch(destroyBase + '/' + delRow.id, {
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
                    .catch(function (d) { alert((d && d.message) || 'Gagal menghapus pengeluaran.'); });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.expense-actions-btn') && !e.target.closest('.expense-actions-menu')) {
                closeAllMenus();
            }
        });

        window.addEventListener('expenses:refresh', function () { load(1); });

        load(page);
    })();
</script>
