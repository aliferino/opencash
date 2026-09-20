<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-1 flex-wrap items-center gap-3">
        <div class="relative min-w-[220px] max-w-sm flex-1">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
            <input
                type="text"
                id="incomes-search"
                placeholder="Cari siswa atau tagihan..."
                class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
            />
        </div>

        <select id="incomes-status-filter" class="shrink-0 rounded-md border border-line bg-surface px-3 py-2.5 text-[13px] text-ink outline-none focus:border-accent">
            <option value="">Semua Status</option>
            <option value="pending">Menunggu Verifikasi</option>
            <option value="verified">Terverifikasi</option>
            <option value="rejected">Ditolak</option>
        </select>

        <select id="incomes-method-filter" class="shrink-0 rounded-md border border-line bg-surface px-3 py-2.5 text-[13px] text-ink outline-none focus:border-accent">
            <option value="">Semua Metode</option>
            <option value="cash">Tunai</option>
            <option value="qris">QRIS</option>
        </select>
    </div>

    <div class="flex shrink-0 items-center gap-2">
        <button
            type="button"
            id="income-cash-open"
            class="flex items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
        >
            <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
            Catat Tunai
        </button>
    </div>
</div>

<div
    id="incomes-table"
    data-endpoint="{{ route('cash-incomes.index') }}"
    class="mt-4 overflow-visible rounded-2xl border border-line bg-surface"
>
    <table class="w-full text-left text-[14px]">
        <thead>
            <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                <th class="px-6 py-4 font-medium">Siswa</th>
                <th class="px-6 py-4 font-medium">Tagihan</th>
                <th class="px-6 py-4 font-medium">Metode</th>
                <th class="px-6 py-4 font-medium">Tanggal</th>
                <th class="px-6 py-4 font-medium">Nominal</th>
                <th class="px-6 py-4 font-medium">Status</th>
                <th class="px-6 py-4 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="incomes-table-body" class="divide-y divide-line">
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-muted">Memuat data...</td>
            </tr>
        </tbody>
    </table>

    <div class="flex items-center justify-between border-t border-line px-6 py-4">
        <p id="incomes-table-info" class="text-xs text-muted"></p>
        <div class="flex items-center gap-2">
            <button type="button" id="incomes-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
            <button type="button" id="incomes-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var container = document.getElementById('incomes-table');
        var body = document.getElementById('incomes-table-body');
        var info = document.getElementById('incomes-table-info');
        var prevBtn = document.getElementById('incomes-prev');
        var nextBtn = document.getElementById('incomes-next');
        var searchInput = document.getElementById('incomes-search');
        var statusFilter = document.getElementById('incomes-status-filter');
        var methodFilter = document.getElementById('incomes-method-filter');
        var endpoint = container.dataset.endpoint;
        var page = 1;
        var searchTimer = null;

        var statusLabels = { pending: 'Menunggu', verified: 'Terverifikasi', rejected: 'Ditolak' };
        var statusClasses = {
            pending: 'bg-amber-500/15 text-amber-300',
            verified: 'bg-emerald-500/15 text-emerald-300',
            rejected: 'bg-red-500/15 text-red-300',
        };
        var methodLabels = { cash: 'Tunai', qris: 'QRIS' };

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
            return '<tr><td colspan="7" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function statusBadge(status) {
            var cls = statusClasses[status] || 'bg-white/5 text-muted';
            return '<span class="rounded-full px-2.5 py-1 text-xs font-medium ' + cls + '">' + (statusLabels[status] || status || '—') + '</span>';
        }

        function methodBadge(method) {
            var icon = method === 'qris' ? 'qr-code' : 'banknote';
            return '<span class="inline-flex items-center gap-1.5 text-muted"><i data-lucide="' + icon + '" class="h-3.5 w-3.5" stroke-width="1.8"></i>' + (methodLabels[method] || method || '—') + '</span>';
        }

        function renderRow(income) {
            var student = income.student ? income.student.name : '—';
            var schedule = income.cash_schedule ? income.cash_schedule.description : '—';
            var total = Number(income.amount_paid || 0) + Number(income.fine_paid || 0);
            var fineNote = Number(income.fine_paid) > 0
                ? '<p class="text-xs text-amber-300">+ denda ' + formatRupiah(income.fine_paid) + '</p>'
                : '';
            var canVerify = income.can_verify;

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(student) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(schedule) + '</td>' +
                    '<td class="px-6 py-4">' + methodBadge(income.payment_method) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + formatDate(income.income_date) + '</td>' +
                    '<td class="px-6 py-4 text-ink">' + formatRupiah(total) + fineNote + '</td>' +
                    '<td class="px-6 py-4">' + statusBadge(income.status) + '</td>' +
                    '<td class="relative px-6 py-4 text-right">' +
                        '<button ' +
                            'type="button" ' +
                            'class="income-actions-btn rounded-md p-2 text-muted transition-colors hover:bg-white/5 hover:text-ink" ' +
                            'title="Aksi" ' +
                            'data-row="' + escapeHtml(JSON.stringify(income)) + '"' +
                        '>' +
                            '<i data-lucide="ellipsis" class="h-4 w-4" stroke-width="1.8"></i>' +
                        '</button>' +
                        '<div class="income-actions-menu hidden absolute right-6 top-full z-20 mt-1 w-40 overflow-hidden rounded-md border border-line bg-surface shadow-lg">' +
                            '<button type="button" class="income-action-detail flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                '<i data-lucide="eye" class="h-3.5 w-3.5" stroke-width="1.8"></i> Detail' +
                            '</button>' +
                            (income.proof_image
                                ? '<button type="button" class="income-action-proof flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-ink transition-colors hover:bg-white/5">' +
                                    '<i data-lucide="image" class="h-3.5 w-3.5" stroke-width="1.8"></i> Lihat Bukti' +
                                  '</button>'
                                : '') +
                            (canVerify
                                ? '<button type="button" class="income-action-verify flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-emerald-300 transition-colors hover:bg-emerald-500/10">' +
                                    '<i data-lucide="check" class="h-3.5 w-3.5" stroke-width="1.8"></i> Verifikasi' +
                                  '</button>' +
                                  '<button type="button" class="income-action-reject flex w-full items-center gap-2 px-3.5 py-2.5 text-left text-[13px] text-red-400 transition-colors hover:bg-red-500/10">' +
                                    '<i data-lucide="x" class="h-3.5 w-3.5" stroke-width="1.8"></i> Tolak' +
                                  '</button>'
                                : '') +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }

        function closeAllMenus() {
            document.querySelectorAll('.income-actions-menu').forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }

        function load(targetPage) {
            body.innerHTML = emptyRow('Memuat data...');

            var url = endpoint + '?page=' + targetPage;
            if (searchInput.value.trim()) url += '&search=' + encodeURIComponent(searchInput.value.trim());
            if (statusFilter.value) url += '&status=' + statusFilter.value;
            if (methodFilter.value) url += '&payment_method=' + methodFilter.value;

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    page = json.current_page;

                    body.innerHTML = json.data.length
                        ? json.data.map(renderRow).join('')
                        : emptyRow('Belum ada pemasukan.');

                    info.textContent = 'Menampilkan ' + json.data.length + ' dari ' + json.total + ' pemasukan — halaman ' + json.current_page + ' dari ' + json.last_page;
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
        statusFilter.addEventListener('change', function () { load(1); });
        methodFilter.addEventListener('change', function () { load(1); });

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { load(1); }, 350);
        });

        body.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.income-actions-btn');
            if (toggleBtn) {
                var menu = toggleBtn.nextElementSibling;
                var wasHidden = menu.classList.contains('hidden');
                closeAllMenus();
                if (wasHidden) menu.classList.remove('hidden');
                return;
            }

            var detailBtn = e.target.closest('.income-action-detail');
            if (detailBtn) {
                var detailRow = JSON.parse(detailBtn.closest('td').querySelector('.income-actions-btn').dataset.row);
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('income:detail', { detail: detailRow }));
                return;
            }

            var proofBtn = e.target.closest('.income-action-proof');
            if (proofBtn) {
                var proofRow = JSON.parse(proofBtn.closest('td').querySelector('.income-actions-btn').dataset.row);
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('income:proof', { detail: proofRow }));
                return;
            }

            var verifyBtn = e.target.closest('.income-action-verify');
            if (verifyBtn) {
                var verifyRow = JSON.parse(verifyBtn.closest('td').querySelector('.income-actions-btn').dataset.row);
                closeAllMenus();
                window.dispatchEvent(new CustomEvent('income:verify', { detail: verifyRow }));
                return;
            }

            var rejectBtn = e.target.closest('.income-action-reject');
            if (rejectBtn) {
                var rejectRow = JSON.parse(rejectBtn.closest('td').querySelector('.income-actions-btn').dataset.row);
                var rejectName = rejectRow.student ? rejectRow.student.name : 'siswa ini';
                closeAllMenus();
                if (!confirm('Tolak pembayaran dari "' + rejectName + '"? Pastikan bukti transfer tidak valid.')) return;

                fetch('{{ url('treasurer/cash-incomes') }}/' + rejectRow.id + '/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ status: 'rejected' }),
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.json();
                    })
                    .then(function () { load(page); })
                    .catch(function (d) { alert((d && d.message) || 'Gagal menolak pembayaran.'); });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.income-actions-btn') && !e.target.closest('.income-actions-menu')) {
                closeAllMenus();
            }
        });

        window.addEventListener('incomes:refresh', function () { load(1); });

        load(page);
    })();
</script>
