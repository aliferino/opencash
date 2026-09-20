<div id="schedule-import-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Import Jadwal Tagihan</h2>
            <button type="button" id="schedule-import-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <p class="mt-1 text-[13px] text-muted">
            Tambah banyak jadwal tagihan sekaligus dari berkas Excel/CSV.
        </p>

        <div class="mt-5 rounded-xl border border-line bg-bg p-4">
            <p class="text-[13px] font-medium text-ink">Format kolom yang dibutuhkan</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left text-[12.5px]">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-wide text-muted">
                            <th class="py-1.5 pr-3 font-medium">Kolom</th>
                            <th class="py-1.5 pr-3 font-medium">Wajib</th>
                            <th class="py-1.5 font-medium">Contoh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr><td class="py-2 pr-3 text-ink">Deskripsi</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">Kas Minggu ke-3 Oktober</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Jatuh Tempo</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">18/10/2026</td></tr>
                        <tr><td class="py-2 pr-3 text-ink">Nominal</td><td class="py-2 pr-3 text-muted">ya</td><td class="py-2 text-muted">5000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-[12px] text-muted">
                Baris pertama berkas dipakai sebagai judul kolom. Nama kolom tidak peka huruf besar/kecil,
                dan spasi boleh diganti garis bawah (mis. <span class="font-mono text-ink">jatuh_tempo</span>).
                Judul kolom juga dicari otomatis, jadi baris kosong atau baris judul tambahan di atas tidak masalah.
            </p>
        </div>

        {{-- Contoh format: memperlihatkan susunan baris yang diharapkan, bukan
             cuma daftar kolom — supaya bendahara tahu persis isi berkasnya. --}}
        <div class="mt-4 rounded-xl border border-line bg-bg p-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-[13px] font-medium text-ink">Contoh isi berkas</p>
                <button
                    type="button"
                    id="schedule-example-copy"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-line px-2.5 py-1.5 text-[11.5px] font-medium text-muted transition-colors hover:border-accent hover:text-accent-bright"
                >
                    <i data-lucide="copy" class="h-3.5 w-3.5" stroke-width="1.8"></i>
                    Salin contoh
                </button>
            </div>

            <div class="mt-3 overflow-x-auto rounded-lg border border-line">
                <table class="w-full text-left text-[12px]">
                    <thead>
                        <tr class="border-b border-line bg-surface/60 text-[10.5px] uppercase tracking-wide text-muted">
                            <th class="px-3 py-2 font-medium">Deskripsi</th>
                            <th class="px-3 py-2 font-medium">Jatuh Tempo</th>
                            <th class="px-3 py-2 text-right font-medium">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line font-mono">
                        <tr>
                            <td class="px-3 py-2 text-ink">Kas Minggu ke-3 Oktober</td>
                            <td class="px-3 py-2 text-muted">18/10/2026</td>
                            <td class="px-3 py-2 text-right text-ink">5000</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 text-ink">Kas Minggu ke-4 Oktober</td>
                            <td class="px-3 py-2 text-muted">25/10/2026</td>
                            <td class="px-3 py-2 text-right text-ink">5000</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 text-ink">Kas Awal Semester</td>
                            <td class="px-3 py-2 text-muted">2026-11-05</td>
                            <td class="px-3 py-2 text-right text-ink">12500</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p id="schedule-example-copied" class="mt-2 hidden text-[11.5px] text-emerald-400"></p>

            <ul class="mt-3 space-y-1 text-[12px] text-muted">
                <li>&middot; Baris pertama wajib berisi judul kolom, baru baris berikutnya data.</li>
                <li>&middot; Tanggal boleh <span class="font-mono text-ink">dd/mm/yyyy</span> atau <span class="font-mono text-ink">yyyy-mm-dd</span>.</li>
                <li>&middot; Nominal ditulis polos tanpa titik/koma: <span class="font-mono text-ink">12500</span>. Kalau Excel mengubah <span class="font-mono text-ink">12.500</span> jadi desimal, pakai angka polos.</li>
                <li>&middot; Nominal = kas per siswa untuk tagihan itu (bukan total kelas).</li>
                <li>&middot; Dua tagihan <span class="text-ink">berbeda</span> boleh jatuh tempo di tanggal yang sama. Yang ditolak hanya deskripsi <span class="text-ink">sama</span> di tanggal yang sama.</li>
                <li>&middot; Kalau hasil <span class="text-ink">Salin contoh</span> ditempel ke Excel dan semua teks masuk ke kolom A, itu tetap terbaca — pemisah koma dipecah otomatis.</li>
            </ul>

            <p class="mt-3 text-[12px] text-muted">
                Kalau ada satu baris bermasalah, tidak ada baris yang disimpan — semua dilaporkan sekaligus.
            </p>
        </div>

        <form id="schedule-import-form" class="mt-5 space-y-4">
            <div>
                <label for="schedule-import-file" class="text-[13px] font-medium text-ink">Berkas</label>
                <input type="file" id="schedule-import-file" accept=".xlsx,.xls,.csv,.txt" required
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[13px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-[12px] file:font-medium file:text-white focus:border-accent" />
                <p class="mt-1.5 text-xs text-muted">Format .xlsx, .xls, atau .csv. Maksimal 4 MB.</p>
            </div>

            <div id="schedule-import-errors" class="hidden rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-[13px] text-red-300">
                <p id="schedule-import-errors-title" class="font-medium"></p>
                <ul id="schedule-import-errors-list" class="mt-1.5 list-disc space-y-0.5 pl-4"></ul>
            </div>

            <div id="schedule-import-success" class="hidden rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-[13px] text-emerald-300"></div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" id="schedule-import-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="schedule-import-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Import</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('schedule-import-modal');
        var openBtn = document.getElementById('schedule-import-open');
        var closeBtn = document.getElementById('schedule-import-close');
        var cancelBtn = document.getElementById('schedule-import-cancel');
        var form = document.getElementById('schedule-import-form');
        var fileInput = document.getElementById('schedule-import-file');
        var submitBtn = document.getElementById('schedule-import-submit');
        var errorsBox = document.getElementById('schedule-import-errors');
        var errorsTitle = document.getElementById('schedule-import-errors-title');
        var errorsList = document.getElementById('schedule-import-errors-list');
        var successBox = document.getElementById('schedule-import-success');

        var endpoint = '{{ route('treasurer.cash-schedules.import.store') }}';
        var csrf = '{{ csrf_token() }}';

        // Contoh format dalam bentuk CSV — bisa ditempel langsung ke Excel
        // atau disimpan sebagai berkas .csv lalu diimport.
        var EXAMPLE_CSV = [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Minggu ke-3 Oktober,18/10/2026,5000',
            'Kas Minggu ke-4 Oktober,25/10/2026,5000',
            'Kas Awal Semester,2026-11-05,12500',
        ].join('\n');

        if (!openBtn) return;

        function show(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
        function hide(el) { el.classList.add('hidden'); el.classList.remove('flex'); }

        function reset() {
            form.reset();
            errorsBox.classList.add('hidden');
            successBox.classList.add('hidden');
            errorsList.innerHTML = '';
        }

        openBtn.addEventListener('click', function () {
            reset();
            show(modal);
        });

        [closeBtn, cancelBtn].forEach(function (btn) {
            btn.addEventListener('click', function () { hide(modal); });
        });

        modal.addEventListener('click', function (e) { if (e.target === modal) hide(modal); });

        // ---- Salin contoh format ----
        var exampleCopyBtn = document.getElementById('schedule-example-copy');
        var exampleCopied = document.getElementById('schedule-example-copied');

        function showCopied(msg) {
            if (!exampleCopied) return;
            exampleCopied.textContent = msg;
            exampleCopied.classList.remove('hidden');
            window.setTimeout(function () { exampleCopied.classList.add('hidden'); }, 2500);
        }

        function legacyCopy(text) {
            var area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', 'readonly');
            area.style.position = 'fixed';
            area.style.top = '-1000px';
            document.body.appendChild(area);
            area.select();
            var ok = false;
            try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
            document.body.removeChild(area);
            return ok;
        }

        if (exampleCopyBtn) {
            exampleCopyBtn.addEventListener('click', function () {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(EXAMPLE_CSV)
                        .then(function () { showCopied('Contoh format disalin. Tempel ke Excel atau simpan sebagai .csv.'); })
                        .catch(function () {
                            showCopied(legacyCopy(EXAMPLE_CSV)
                                ? 'Contoh format disalin. Tempel ke Excel atau simpan sebagai .csv.'
                                : 'Gagal menyalin — salin manual dari tabel di atas.');
                        });
                    return;
                }

                showCopied(legacyCopy(EXAMPLE_CSV)
                    ? 'Contoh format disalin. Tempel ke Excel atau simpan sebagai .csv.'
                    : 'Gagal menyalin — salin manual dari tabel di atas.');
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorsBox.classList.add('hidden');
            successBox.classList.add('hidden');

            if (!fileInput.files.length) {
                errorsTitle.textContent = 'Pilih berkas terlebih dahulu.';
                show(errorsBox);
                return;
            }

            var payload = new FormData();
            payload.append('file', fileInput.files[0]);

            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengimport...';

            fetch(endpoint, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: payload,
            })
                .then(function (res) {
                    return res.json().then(function (d) { return { ok: res.ok, data: d }; });
                })
                .then(function (r) {
                    if (!r.ok) {
                        errorsTitle.textContent = r.data.message || 'Import gagal.';
                        (r.data.errors || []).forEach(function (msg) {
                            var li = document.createElement('li');
                            li.textContent = msg;
                            errorsList.appendChild(li);
                        });
                        if (r.data.total_errors > (r.data.errors || []).length) {
                            var more = document.createElement('li');
                            more.textContent = '...dan ' + (r.data.total_errors - r.data.errors.length) + ' baris lain.';
                            errorsList.appendChild(more);
                        }
                        show(errorsBox);
                        return;
                    }

                    successBox.textContent = r.data.message || 'Import berhasil.';
                    show(successBox);
                    fileInput.value = '';
                    window.dispatchEvent(new CustomEvent('schedules:refresh'));
                })
                .catch(function () {
                    errorsTitle.textContent = 'Gagal menghubungi server.';
                    show(errorsBox);
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Import';
                });
        });
    })();
</script>
