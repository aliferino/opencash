<div class="mt-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-ink">Rincian Arus Kas</h2>
            <p class="mt-1 text-[14px] text-muted">Gabungan pemasukan dan pengeluaran kas kelas Anda.</p>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-1 flex-wrap items-center gap-3">
            <div class="relative min-w-[220px] max-w-sm flex-1">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
                <input
                    type="text"
                    id="ledger-search"
                    placeholder="Cari siswa, tagihan, atau keterangan..."
                    class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
                />
            </div>

            <select id="ledger-type-filter" class="shrink-0 rounded-md border border-line bg-surface px-3 py-2.5 text-[13px] text-ink outline-none focus:border-accent">
                <option value="all">Semua Arus Kas</option>
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
            </select>
        </div>
    </div>

    <div
        id="ledger-table"
        data-incomes-endpoint="{{ route('treasurer.reports.incomes') }}"
        data-expenses-endpoint="{{ route('treasurer.reports.expenses') }}"
        class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
    >
        <table class="w-full text-left text-[14px]">
            <thead>
                <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                    <th class="px-6 py-4 font-medium">Tanggal</th>
                    <th class="px-6 py-4 font-medium">Jenis</th>
                    <th class="px-6 py-4 font-medium">Keterangan</th>
                    <th class="px-6 py-4 font-medium">Catatan</th>
                    <th class="px-6 py-4 font-medium text-right">Nominal</th>
                </tr>
            </thead>
            <tbody id="ledger-table-body" class="divide-y divide-line">
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-muted">Memuat data...</td>
                </tr>
            </tbody>
        </table>

        <div class="flex items-center justify-between border-t border-line px-6 py-4">
            <p id="ledger-table-info" class="text-xs text-muted"></p>
            <div class="flex items-center gap-2">
                <button type="button" id="ledger-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
                <button type="button" id="ledger-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var container = document.getElementById('ledger-table');
        var body = document.getElementById('ledger-table-body');
        var info = document.getElementById('ledger-table-info');
        var prevBtn = document.getElementById('ledger-prev');
        var nextBtn = document.getElementById('ledger-next');
        var searchInput = document.getElementById('ledger-search');
        var typeFilter = document.getElementById('ledger-type-filter');

        var incomesEndpoint = container.dataset.incomesEndpoint;
        var expensesEndpoint = container.dataset.expensesEndpoint;

        var page = 1;
        var searchTimer = null;
        var PER_PAGE = 10;

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

        function incomeToRow(income) {
            var total = Number(income.amount_paid || 0) + Number(income.fine_paid || 0);

            return {
                date: income.income_date,
                type: 'income',
                title: income.student ? income.student.name : '—',
                note: (income.cash_schedule ? income.cash_schedule.description : '—')
                    + (Number(income.fine_paid) > 0 ? ' · denda ' + formatRupiah(income.fine_paid) : ''),
                amount: total,
            };
        }

        function expenseToRow(expense) {
            return {
                date: expense.expense_date,
                type: 'expense',
                title: expense.description,
                note: expense.treasurer ? expense.treasurer.name : '—',
                amount: Number(expense.amount || 0),
            };
        }

        function renderRow(row) {
            var isIncome = row.type === 'income';
            var badge = isIncome
                ? '<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-medium text-emerald-300"><i data-lucide="arrow-down" class="h-3 w-3" stroke-width="2"></i>Pemasukan</span>'
                : '<span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-medium text-red-300"><i data-lucide="arrow-up" class="h-3 w-3" stroke-width="2"></i>Pengeluaran</span>';

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 text-muted">' + formatDate(row.date) + '</td>' +
                    '<td class="px-6 py-4">' + badge + '</td>' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(row.title) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(row.note) + '</td>' +
                    '<td class="px-6 py-4 text-right font-medium ' + (isIncome ? 'text-emerald-300' : 'text-red-300') + '">' +
                        (isIncome ? '+' : '−') + formatRupiah(row.amount) +
                    '</td>' +
                '</tr>';
        }

        function buildUrl(endpoint, targetPage) {
            var url = endpoint + '?page=' + targetPage + '&per_page=' + PER_PAGE;
            if (searchInput.value.trim()) url += '&search=' + encodeURIComponent(searchInput.value.trim());
            return url;
        }

        function buildIncomeUrl(targetPage) {
            // Rincian arus kas hanya menghitung pemasukan yang sudah terverifikasi
            // supaya totalnya konsisten dengan kartu ringkasan di atas.
            return buildUrl(incomesEndpoint, targetPage) + '&status=verified';
        }

        function fetchJson(url) {
            return fetch(url, { headers: { Accept: 'application/json' } }).then(function (res) { return res.json(); });
        }

        function load(targetPage) {
            body.innerHTML = emptyRow('Memuat data...');
            var type = typeFilter.value;

            var requests;
            if (type === 'income') {
                requests = [fetchJson(buildIncomeUrl(targetPage))];
            } else if (type === 'expense') {
                requests = [fetchJson(buildUrl(expensesEndpoint, targetPage))];
            } else {
                requests = [
                    fetchJson(buildIncomeUrl(targetPage)),
                    fetchJson(buildUrl(expensesEndpoint, targetPage)),
                ];
            }

            Promise.all(requests)
                .then(function (responses) {
                    var rows = [];
                    var total = 0;
                    var lastPage = 1;

                    if (type === 'income') {
                        rows = responses[0].data.map(incomeToRow);
                        total = responses[0].total;
                        lastPage = responses[0].last_page;
                    } else if (type === 'expense') {
                        rows = responses[0].data.map(expenseToRow);
                        total = responses[0].total;
                        lastPage = responses[0].last_page;
                    } else {
                        var incomePage = responses[0];
                        var expensePage = responses[1];

                        rows = incomePage.data.map(incomeToRow)
                            .concat(expensePage.data.map(expenseToRow))
                            .sort(function (a, b) { return String(b.date).localeCompare(String(a.date)); });

                        total = incomePage.total + expensePage.total;
                        lastPage = Math.max(incomePage.last_page, expensePage.last_page);
                    }

                    page = targetPage;

                    body.innerHTML = rows.length
                        ? rows.map(renderRow).join('')
                        : emptyRow('Belum ada arus kas pada filter ini.');

                    info.textContent = 'Menampilkan ' + rows.length + ' dari ' + total + ' transaksi — halaman ' + page + ' dari ' + lastPage;
                    prevBtn.disabled = page <= 1;
                    nextBtn.disabled = page >= lastPage;

                    if (window.lucideRefresh) window.lucideRefresh();
                })
                .catch(function () {
                    body.innerHTML = emptyRow('Gagal memuat data.');
                });
        }

        prevBtn.addEventListener('click', function () { load(page - 1); });
        nextBtn.addEventListener('click', function () { load(page + 1); });
        typeFilter.addEventListener('change', function () { load(1); });

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { load(1); }, 350);
        });

        load(1);
    })();
</script>
