<div id="income-cash-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Catat Pembayaran Tunai</h2>
            <button type="button" id="income-cash-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <p class="mt-1 text-[13px] text-muted">Pembayaran tunai yang dicatat bendahara langsung berstatus terverifikasi.</p>

        <form id="income-cash-form" class="mt-5 space-y-4">
            <div>
                <label for="income-cash-student" class="text-[13px] font-medium text-ink">Siswa</label>
                <select id="income-cash-student" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                    <option value="">Memuat siswa...</option>
                </select>
            </div>

            <div>
                <label for="income-cash-schedule" class="text-[13px] font-medium text-ink">Tagihan</label>
                <select id="income-cash-schedule" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                    <option value="">Memuat tagihan...</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="income-cash-amount" class="text-[13px] font-medium text-ink">Nominal Kas (Rp)</label>
                    <input type="number" id="income-cash-amount" min="0" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="5000" />
                </div>

                <div>
                    <label for="income-cash-fine" class="text-[13px] font-medium text-ink">Denda (Rp)</label>
                    <input type="number" id="income-cash-fine" min="0" value="0" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="0" />
                </div>
            </div>

            <div>
                <label for="income-cash-date" class="text-[13px] font-medium text-ink">Tanggal Bayar</label>
                <input type="date" id="income-cash-date" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" />
            </div>

            <div id="income-cash-remaining-box" class="hidden rounded-xl border border-line bg-bg px-4 py-3 text-[13px]">
                <div class="flex items-center justify-between">
                    <span class="text-muted">Sisa yang belum dibayar</span>
                    <span id="income-cash-remaining" class="font-semibold text-ink">—</span>
                </div>
                <p id="income-cash-remaining-note" class="mt-1 text-[12px] text-muted"></p>
            </div>

            <p id="income-cash-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="income-cash-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="income-cash-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="income-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Detail Pemasukan</h2>
            <button type="button" id="income-detail-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <dl id="income-detail-body" class="mt-5 space-y-3 text-[14px]"></dl>
    </div>
</div>

<div id="income-verify-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Verifikasi Pembayaran</h2>
            <button type="button" id="income-verify-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <div class="mt-5 space-y-3 text-[14px]">
            <div class="flex items-center justify-between">
                <span class="text-muted">Siswa</span>
                <span id="income-verify-student" class="font-medium text-ink"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Tagihan</span>
                <span id="income-verify-schedule" class="text-right text-ink"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Dibayar</span>
                <span id="income-verify-amount" class="font-medium text-ink"></span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
                <label for="income-verify-amount-input" class="text-[13px] font-medium text-ink">Nominal Kas (Rp)</label>
                <input type="number" id="income-verify-amount-input" min="1" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" />
                <p class="mt-1.5 text-xs text-muted">Ubah kalau nominal di bukti transfer berbeda.</p>
            </div>

            <div>
                <label for="income-verify-fine" class="text-[13px] font-medium text-ink">Denda (Rp)</label>
                <input type="number" id="income-verify-fine" min="0" value="0" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="0" />
                <p class="mt-1.5 text-xs text-muted">Isi jika siswa terlambat membayar.</p>
            </div>
        </div>

        <p id="income-verify-hint" class="mt-3 text-[12.5px] text-muted"></p>

        <p id="income-verify-error" class="mt-4 hidden text-[13px] text-red-400"></p>

        <div class="mt-5 flex items-center justify-end gap-3">
            <button type="button" id="income-verify-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
            <button type="button" id="income-verify-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Verifikasi</button>
        </div>
    </div>
</div>

<div id="income-proof-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4">
    <div class="w-full max-w-lg rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Bukti Transfer</h2>
            <button type="button" id="income-proof-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>
        <p id="income-proof-caption" class="mt-1 text-[13px] text-muted"></p>
        <img id="income-proof-image" src="" alt="Bukti transfer" class="mt-4 max-h-[60vh] w-full rounded-md border border-line object-contain" />
        <p id="income-proof-empty" class="mt-4 hidden text-[14px] text-muted">Tidak ada bukti transfer untuk pembayaran ini.</p>
    </div>
</div>

<script>
    (function () {
        var cashModal = document.getElementById('income-cash-modal');
        var cashOpenBtn = document.getElementById('income-cash-open');
        var cashCloseBtn = document.getElementById('income-cash-close');
        var cashCancelBtn = document.getElementById('income-cash-cancel');
        var cashForm = document.getElementById('income-cash-form');
        var studentSelect = document.getElementById('income-cash-student');
        var scheduleSelect = document.getElementById('income-cash-schedule');
        var amountInput = document.getElementById('income-cash-amount');
        var fineInput = document.getElementById('income-cash-fine');
        var dateInput = document.getElementById('income-cash-date');
        var cashSubmit = document.getElementById('income-cash-submit');
        var cashError = document.getElementById('income-cash-error');
        var remainingBox = document.getElementById('income-cash-remaining-box');
        var remainingEl = document.getElementById('income-cash-remaining');
        var remainingNote = document.getElementById('income-cash-remaining-note');

        var detailModal = document.getElementById('income-detail-modal');
        var detailCloseBtn = document.getElementById('income-detail-close');
        var detailBody = document.getElementById('income-detail-body');

        var verifyModal = document.getElementById('income-verify-modal');
        var verifyCloseBtn = document.getElementById('income-verify-close');
        var verifyCancelBtn = document.getElementById('income-verify-cancel');
        var verifySubmit = document.getElementById('income-verify-submit');
        var verifyStudent = document.getElementById('income-verify-student');
        var verifySchedule = document.getElementById('income-verify-schedule');
        var verifyAmount = document.getElementById('income-verify-amount');
        var verifyAmountInput = document.getElementById('income-verify-amount-input');
        var verifyHint = document.getElementById('income-verify-hint');
        var verifyFine = document.getElementById('income-verify-fine');
        var verifyError = document.getElementById('income-verify-error');

        var proofModal = document.getElementById('income-proof-modal');
        var proofCloseBtn = document.getElementById('income-proof-close');
        var proofImage = document.getElementById('income-proof-image');
        var proofCaption = document.getElementById('income-proof-caption');
        var proofEmpty = document.getElementById('income-proof-empty');

        var baseUrl = '{{ url('treasurer/cash-incomes') }}';
        var studentsUrl = '{{ route('treasurer.group.members.index') }}';
        var schedulesUrl = '{{ route('cash-schedules.index') }}';
        var remainingUrl = '{{ route('treasurer.cash-incomes.remaining') }}';
        var csrf = '{{ csrf_token() }}';

        var students = [];
        var schedules = [];
        var currentVerifyId = null;
        var currentRemaining = null;

        var statusLabels = { pending: 'Menunggu Verifikasi', verified: 'Terverifikasi', rejected: 'Ditolak' };
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
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function todayValue() {
            return new Date().toISOString().slice(0, 10);
        }

        function show(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
        function hide(el) { el.classList.add('hidden'); el.classList.remove('flex'); }

        function loadStudents() {
            return fetch(studentsUrl + '?per_page=100', { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    students = json.data || json;
                    studentSelect.innerHTML = students.length
                        ? '<option value="">Pilih siswa</option>' + students.map(function (s) {
                            return '<option value="' + s.id + '">' + escapeHtml(s.name) + '</option>';
                        }).join('')
                        : '<option value="">Belum ada siswa di kelas ini</option>';
                })
                .catch(function () {
                    studentSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
                });
        }

        function loadSchedules() {
            return fetch(schedulesUrl, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    schedules = data || [];
                    scheduleSelect.innerHTML = schedules.length
                        ? '<option value="">Pilih tagihan</option>' + schedules.map(function (s) {
                            return '<option value="' + s.id + '" data-amount="' + s.amount + '">' + escapeHtml(s.description) + ' — ' + formatRupiah(s.amount) + '</option>';
                        }).join('')
                        : '<option value="">Belum ada tagihan</option>';
                })
                .catch(function () {
                    scheduleSelect.innerHTML = '<option value="">Gagal memuat tagihan</option>';
                });
        }

        scheduleSelect.addEventListener('change', function () {
            var opt = scheduleSelect.options[scheduleSelect.selectedIndex];
            var amount = opt ? opt.dataset.amount : '';
            if (amount) amountInput.value = amount;
            refreshRemaining();
        });

        // Ambil sisa tagihan siswa ini, lalu isi nominal otomatis dengan sisa
        // tersebut — bendahara tinggal mengubah kalau siswa bayar sebagian.
        function refreshRemaining() {
            currentRemaining = null;
            remainingBox.classList.add('hidden');
            amountInput.removeAttribute('max');

            if (!studentSelect.value || !scheduleSelect.value) return Promise.resolve();

            return fetch(remainingUrl + '?cash_schedule_id=' + encodeURIComponent(scheduleSelect.value) + '&student_id=' + encodeURIComponent(studentSelect.value), {
                headers: { Accept: 'application/json' },
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    currentRemaining = data;
                    amountInput.max = data.remaining;

                    if (data.remaining > 0) amountInput.value = data.remaining;

                    remainingEl.textContent = formatRupiah(data.remaining);
                    remainingEl.className = data.remaining > 0 ? 'font-semibold text-red-400' : 'font-semibold text-emerald-400';

                    var note = [];
                    if (data.paid > 0) note.push('sudah bayar ' + formatRupiah(data.paid));
                    if (data.pending > 0) note.push(formatRupiah(data.pending) + ' menunggu verifikasi');
                    if (data.remaining === 0) note.push('tagihan sudah lunas');
                    remainingNote.textContent = note.join(' · ');

                    remainingBox.classList.remove('hidden');
                })
                .catch(function () {
                    remainingNote.textContent = '';
                    remainingBox.classList.add('hidden');
                });
        }

        studentSelect.addEventListener('change', refreshRemaining);

        cashOpenBtn.addEventListener('click', function () {
            cashForm.reset();
            fineInput.value = 0;
            dateInput.value = todayValue();
            cashError.classList.add('hidden');
            currentRemaining = null;
            remainingBox.classList.add('hidden');
            amountInput.removeAttribute('max');
            show(cashModal);
            loadStudents();
            loadSchedules();
        });

        [cashCloseBtn, cashCancelBtn].forEach(function (btn) {
            btn.addEventListener('click', function () { hide(cashModal); });
        });

        cashModal.addEventListener('click', function (e) { if (e.target === cashModal) hide(cashModal); });

        cashForm.addEventListener('submit', function (e) {
            e.preventDefault();
            cashError.classList.add('hidden');

            var amount = Number(amountInput.value || 0);

            if (amount < 1) {
                cashError.textContent = 'Nominal harus lebih dari 0.';
                cashError.classList.remove('hidden');
                return;
            }

            if (currentRemaining && amount > currentRemaining.remaining) {
                cashError.textContent = 'Nominal melebihi sisa tagihan (' + formatRupiah(currentRemaining.remaining) + ').';
                cashError.classList.remove('hidden');
                return;
            }

            var payload = {
                student_id: studentSelect.value,
                cash_schedule_id: scheduleSelect.value,
                amount_paid: amount,
                fine_paid: fineInput.value || 0,
                income_date: dateInput.value,
            };

            cashSubmit.disabled = true;
            cashSubmit.textContent = 'Menyimpan...';

            fetch(baseUrl + '/cash', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function () {
                    hide(cashModal);
                    window.dispatchEvent(new CustomEvent('incomes:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan pembayaran.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    cashError.textContent = msg;
                    cashError.classList.remove('hidden');
                })
                .finally(function () {
                    cashSubmit.disabled = false;
                    cashSubmit.textContent = 'Simpan';
                });
        });

        window.addEventListener('income:detail', function (e) {
            var income = e.detail;
            var total = Number(income.amount_paid || 0) + Number(income.fine_paid || 0);
            var rows = [
                ['Siswa', income.student ? income.student.name : '—'],
                ['Tagihan', income.cash_schedule ? income.cash_schedule.description : '—'],
                ['Metode', methodLabels[income.payment_method] || income.payment_method || '—'],
                ['Tanggal Bayar', formatDate(income.income_date)],
                ['Nominal Kas', formatRupiah(income.amount_paid)],
                ['Denda', formatRupiah(income.fine_paid)],
                ['Total', formatRupiah(total)],
                ['Status', statusLabels[income.status] || income.status || '—'],
                ['Dicatat Oleh', income.treasurer ? income.treasurer.name : '—'],
                ['Catatan', income.notes ? escapeHtml(income.notes) : '—'],
            ];

            detailBody.innerHTML = rows.map(function (row) {
                return '<div class="flex items-start justify-between gap-4 border-b border-line pb-2.5 last:border-0 last:pb-0">' +
                    '<dt class="text-muted">' + row[0] + '</dt>' +
                    '<dd class="text-right font-medium text-ink">' + row[1] + '</dd>' +
                '</div>';
            }).join('');

            show(detailModal);
        });

        detailCloseBtn.addEventListener('click', function () { hide(detailModal); });
        detailModal.addEventListener('click', function (e) { if (e.target === detailModal) hide(detailModal); });

        window.addEventListener('income:proof', function (e) {
            var income = e.detail;
            proofCaption.textContent = (income.student ? income.student.name : '') + ' — ' + formatRupiah(Number(income.amount_paid || 0) + Number(income.fine_paid || 0));

            if (income.proof_image) {
                proofImage.src = income.proof_image_url;
                proofImage.classList.remove('hidden');
                proofEmpty.classList.add('hidden');
            } else {
                proofImage.removeAttribute('src');
                proofImage.classList.add('hidden');
                proofEmpty.classList.remove('hidden');
            }

            show(proofModal);
        });

        proofCloseBtn.addEventListener('click', function () { hide(proofModal); });
        proofModal.addEventListener('click', function (e) { if (e.target === proofModal) hide(proofModal); });

        window.addEventListener('income:verify', function (e) {
            var income = e.detail;
            currentVerifyId = income.id;
            verifyStudent.textContent = income.student ? income.student.name : '—';
            verifySchedule.textContent = income.cash_schedule ? income.cash_schedule.description : '—';
            verifyAmount.textContent = formatRupiah(Number(income.amount_paid || 0) + Number(income.fine_paid || 0));
            verifyAmountInput.value = income.amount_paid || 0;
            verifyFine.value = income.fine_paid || 0;
            verifyError.classList.add('hidden');
            verifyHint.textContent = '';
            show(verifyModal);

            // Sisa tagihan dihitung dari pembayaran lain, jadi bendahara tahu
            // batas maksimal nominal yang boleh dia verifikasi.
            if (income.cash_schedule && income.student) {
                fetch(remainingUrl + '?cash_schedule_id=' + encodeURIComponent(income.cash_schedule.id) + '&student_id=' + encodeURIComponent(income.student.id), {
                    headers: { Accept: 'application/json' },
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        var othersPaid = data.paid + data.pending;
                        var cap = Number(income.amount_paid || 0) + (Number(income.cash_schedule.amount || 0) - othersPaid);

                        verifyAmountInput.max = Math.max(cap, 0);
                        verifyHint.textContent = 'Sisa tagihan sebelum verifikasi ini: ' + formatRupiah(data.remaining) +
                            ' (nominal maksimal ' + formatRupiah(Math.max(cap, 0)) + ').';
                    })
                    .catch(function () {
                        verifyHint.textContent = '';
                    });
            }
        });

        [verifyCloseBtn, verifyCancelBtn].forEach(function (btn) {
            btn.addEventListener('click', function () { hide(verifyModal); });
        });

        verifyModal.addEventListener('click', function (e) { if (e.target === verifyModal) hide(verifyModal); });

        verifySubmit.addEventListener('click', function () {
            if (!currentVerifyId) return;
            verifyError.classList.add('hidden');

            verifySubmit.disabled = true;
            verifySubmit.textContent = 'Memproses...';

            fetch(baseUrl + '/' + currentVerifyId + '/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    status: 'verified',
                    amount_paid: Number(verifyAmountInput.value || 0),
                    fine_paid: verifyFine.value || 0,
                }),
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function () {
                    hide(verifyModal);
                    window.dispatchEvent(new CustomEvent('incomes:refresh'));
                })
                .catch(function (d) {
                    verifyError.textContent = (d && d.message) || 'Gagal memverifikasi pembayaran.';
                    verifyError.classList.remove('hidden');
                })
                .finally(function () {
                    verifySubmit.disabled = false;
                    verifySubmit.textContent = 'Verifikasi';
                });
        });
    })();
</script>
