@extends('layouts.panel')

@section('title', 'Pengaturan Kas — OpenCash')

@section('panel')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Pengaturan Kas</h1>
        <p class="mt-1 text-[15px] text-muted">Atur nominal kas, denda, dan QRIS pembayaran untuk periode aktif kelas Anda.</p>
    </div>

    <div class="mt-6 max-w-xl space-y-6">
        <div class="rounded-2xl border border-line bg-surface p-6">
            <label for="setting-period" class="text-[13px] font-medium text-ink">Periode</label>
            <select id="setting-period" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                <option value="">Memuat periode...</option>
            </select>
            <p class="mt-1.5 text-xs text-muted">Pilih periode yang ingin diatur. Setiap periode punya nominal kas dan QRIS sendiri.</p>
        </div>

        <form id="amount-form" class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-[15px] font-semibold text-ink">Nominal Kas &amp; Denda</h2>

            <div class="mt-4 space-y-4">
                <div>
                    <label for="setting-cash" class="text-[13px] font-medium text-ink">Kas per Siswa (Rp)</label>
                    <input type="number" id="setting-cash" min="0" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: 5000" />
                </div>

                <div>
                    <label for="setting-fine" class="text-[13px] font-medium text-ink">Denda Keterlambatan (Rp)</label>
                    <input type="number" id="setting-fine" min="0" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: 1000" />
                </div>
            </div>

            <p id="amount-error" class="mt-4 hidden text-[13px] text-red-400"></p>
            <p id="amount-success" class="mt-4 hidden text-[13px] text-emerald-400"></p>

            <div class="mt-5 flex justify-end">
                <button type="submit" id="amount-submit" disabled class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18] disabled:cursor-not-allowed disabled:opacity-50">Simpan</button>
            </div>
        </form>

        <form id="qris-form" class="rounded-2xl border border-line bg-surface p-6">
            <h2 class="text-[15px] font-semibold text-ink">QRIS Pembayaran</h2>

            <div class="mt-4 flex items-start gap-4">
                <div id="qris-preview-wrap" class="hidden shrink-0">
                    <img id="qris-preview" src="" alt="QRIS" class="h-20 w-20 rounded-md border border-line object-cover" />
                </div>
                <div class="min-w-0 flex-1">
                    <input type="file" id="qris-file" accept="image/*" disabled class="w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent-tint file:px-3 file:py-1.5 file:text-accent-bright disabled:cursor-not-allowed disabled:opacity-50" />
                    <p id="qris-hint" class="mt-1.5 text-xs text-muted">Simpan nominal kas untuk periode ini terlebih dahulu sebelum mengunggah QRIS.</p>
                </div>
            </div>

            <p id="qris-error" class="mt-4 hidden text-[13px] text-red-400"></p>
            <p id="qris-success" class="mt-4 hidden text-[13px] text-emerald-400"></p>

            <div class="mt-5 flex justify-end">
                <button type="submit" id="qris-submit" disabled class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18] disabled:cursor-not-allowed disabled:opacity-50">Unggah QRIS</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var periodSelect = document.getElementById('setting-period');

            var amountForm = document.getElementById('amount-form');
            var cashInput = document.getElementById('setting-cash');
            var fineInput = document.getElementById('setting-fine');
            var amountSubmit = document.getElementById('amount-submit');
            var amountError = document.getElementById('amount-error');
            var amountSuccess = document.getElementById('amount-success');

            var qrisForm = document.getElementById('qris-form');
            var qrisFile = document.getElementById('qris-file');
            var qrisSubmit = document.getElementById('qris-submit');
            var qrisHint = document.getElementById('qris-hint');
            var qrisPreviewWrap = document.getElementById('qris-preview-wrap');
            var qrisPreview = document.getElementById('qris-preview');
            var qrisError = document.getElementById('qris-error');
            var qrisSuccess = document.getElementById('qris-success');

            var settingsUrl = '{{ route('treasurer.group-settings.index') }}';
            var periodsUrl = '{{ route('treasurer.periods.index') }}';

            var settings = [];
            var currentSettingId = null;

            function hide(el) { el.classList.add('hidden'); }
            function show(el) { el.classList.remove('hidden'); }

            function resetMessages() {
                hide(amountError); hide(amountSuccess);
                hide(qrisError); hide(qrisSuccess);
            }

            function findSettingForPeriod(periodId) {
                return settings.find(function (s) { return String(s.period_id) === String(periodId); }) || null;
            }

            function applySettingToForm(setting) {
                resetMessages();
                currentSettingId = setting ? setting.id : null;

                cashInput.value = setting ? setting.cash_amount : '';
                fineInput.value = setting ? setting.fine_amount : '';
                amountSubmit.disabled = false;

                if (setting) {
                    qrisFile.disabled = false;
                    qrisSubmit.disabled = false;
                    qrisHint.textContent = 'Format gambar, maksimal 2MB.';
                    if (setting.qris_image) {
                        qrisPreview.src = '/storage/' + setting.qris_image;
                        show(qrisPreviewWrap);
                    } else {
                        hide(qrisPreviewWrap);
                    }
                } else {
                    qrisFile.disabled = true;
                    qrisSubmit.disabled = true;
                    qrisHint.textContent = 'Simpan nominal kas untuk periode ini terlebih dahulu sebelum mengunggah QRIS.';
                    hide(qrisPreviewWrap);
                }
            }

            function loadSettings(selectAfterPeriodId) {
                fetch(settingsUrl, { headers: { Accept: 'application/json' } })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        settings = data;
                        var periodId = selectAfterPeriodId || periodSelect.value;
                        applySettingToForm(findSettingForPeriod(periodId));
                    });
            }

            function loadPeriods() {
                fetch(periodsUrl, { headers: { Accept: 'application/json' } })
                    .then(function (res) { return res.json(); })
                    .then(function (periods) {
                        if (!periods.length) {
                            periodSelect.innerHTML = '<option value="">Belum ada periode — minta admin menambahkan</option>';
                            return;
                        }

                        periodSelect.innerHTML = periods.map(function (p) {
                            return '<option value="' + p.id + '">' + p.name + ' (' + p.interval_days + ' hari)</option>';
                        }).join('');

                        loadSettings(periodSelect.value);
                    })
                    .catch(function () {
                        periodSelect.innerHTML = '<option value="">Gagal memuat periode</option>';
                    });
            }

            periodSelect.addEventListener('change', function () {
                applySettingToForm(findSettingForPeriod(periodSelect.value));
            });

            amountForm.addEventListener('submit', function (e) {
                e.preventDefault();
                resetMessages();

                if (!periodSelect.value) {
                    amountError.textContent = 'Pilih periode terlebih dahulu.';
                    show(amountError);
                    return;
                }

                var payload = {
                    cash_amount: cashInput.value,
                    fine_amount: fineInput.value,
                };

                var url = currentSettingId ? settingsUrl + '/' + currentSettingId : settingsUrl;
                var method = currentSettingId ? 'PUT' : 'POST';
                if (!currentSettingId) payload.period_id = periodSelect.value;

                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(payload),
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.json();
                    })
                    .then(function () {
                        amountSuccess.textContent = 'Pengaturan berhasil disimpan.';
                        show(amountSuccess);
                        loadSettings(periodSelect.value);
                    })
                    .catch(function (d) {
                        var msg = (d && d.message) || 'Gagal menyimpan pengaturan.';
                        if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                        amountError.textContent = msg;
                        show(amountError);
                    });
            });

            qrisForm.addEventListener('submit', function (e) {
                e.preventDefault();
                resetMessages();

                if (!currentSettingId) return;
                if (!qrisFile.files.length) {
                    qrisError.textContent = 'Pilih gambar QRIS terlebih dahulu.';
                    show(qrisError);
                    return;
                }

                var formData = new FormData();
                formData.append('qris_image', qrisFile.files[0]);

                qrisSubmit.disabled = true;
                qrisSubmit.textContent = 'Mengunggah...';

                fetch('{{ url('treasurer/group-settings') }}/' + currentSettingId + '/qris', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData,
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.json();
                    })
                    .then(function () {
                        qrisSuccess.textContent = 'QRIS berhasil diunggah.';
                        show(qrisSuccess);
                        qrisFile.value = '';
                        loadSettings(periodSelect.value);
                    })
                    .catch(function (d) {
                        var msg = (d && d.message) || 'Gagal mengunggah QRIS.';
                        if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                        qrisError.textContent = msg;
                        show(qrisError);
                    })
                    .finally(function () {
                        qrisSubmit.disabled = false;
                        qrisSubmit.textContent = 'Unggah QRIS';
                    });
            });

            loadPeriods();
        })();
    </script>
@endsection