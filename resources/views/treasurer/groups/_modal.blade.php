<div id="group-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="flex max-h-[88vh] w-full max-w-lg flex-col rounded-2xl border border-line bg-surface">
        <div class="flex shrink-0 items-center justify-between border-b border-line px-6 py-5">
            <h2 class="text-lg font-semibold text-ink">Detail Grup</h2>
            <button type="button" id="group-detail-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <div id="group-detail-body" class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
        <form id="group-form" class="space-y-4">
            <div>
                <label for="group-name" class="text-[13px] font-medium text-ink">Nama Grup</label>
                <input
                    type="text"
                    id="group-name"
                    required
                    maxlength="255"
                    value="{{ $group->name }}"
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent"
                />
            </div>

            <div>
                <label for="group-invite-code" class="text-[13px] font-medium text-ink">Kode Undangan</label>
                <div class="mt-1.5 flex gap-2">
                    <input
                        type="text"
                        id="group-invite-code"
                        readonly
                        value="{{ $group->invite_code }}"
                        class="w-full rounded-md border border-line bg-bg px-3 py-2.5 font-mono text-[14px] text-muted outline-none"
                    />
                    <button
                        type="button"
                        id="group-invite-copy"
                        class="flex shrink-0 items-center gap-1.5 rounded-md border border-line px-3.5 py-2.5 text-[13px] font-medium text-muted transition-colors hover:text-ink"
                        title="Salin kode"
                    >
                        <i data-lucide="copy" class="h-4 w-4" stroke-width="1.8"></i>
                    </button>
                    <button
                        type="button"
                        id="group-invite-refresh"
                        class="flex shrink-0 items-center gap-1.5 rounded-md border border-line px-3.5 py-2.5 text-[13px] font-medium text-muted transition-colors hover:text-ink"
                        title="Buat kode baru"
                    >
                        <i data-lucide="refresh-cw" class="h-4 w-4" stroke-width="1.8"></i>
                    </button>
                </div>
                <p class="mt-1.5 text-xs text-muted">Bagikan kode ini agar siswa bisa masuk lewat halaman onboarding. Membuat kode baru akan menonaktifkan kode lama.</p>
            </div>

            <div class="space-y-1 border-t border-line pt-4 text-[13px]">
                <p class="flex items-center justify-between text-muted">
                    <span>Dibuat</span>
                    <span class="text-ink">{{ optional($group->created_at)->format('d M Y, H:i') ?? '—' }}</span>
                </p>
                <p class="flex items-center justify-between text-muted">
                    <span>Diperbarui</span>
                    <span id="group-updated-at" class="text-ink">{{ optional($group->updated_at)->format('d M Y, H:i') ?? '—' }}</span>
                </p>
            </div>

            <p id="group-error" class="hidden text-[13px] text-red-400"></p>
            <p id="group-success" class="hidden text-[13px] text-emerald-400"></p>

            {{-- QRIS Pembayaran — sengaja di dalam modal supaya grid & tabel anggota
                 di halaman Grup tetap fokus. Di sini HANYA input berkasnya;
                 pratinjau, zoom, dan simpan ke perangkat ada di halaman Grup. --}}
            <div class="border-t border-line pt-4">
                <h3 class="text-[14px] font-semibold text-ink">QRIS Pembayaran</h3>
                <p class="mt-0.5 text-[12.5px] text-muted">Gambar QRIS kelas yang dipakai siswa untuk transfer mandiri.</p>

                <div class="mt-3">
                    <label for="group-qris-file" class="text-[13px] font-medium text-ink">Gambar QRIS</label>
                    <input type="file" id="group-qris-file" accept="image/*"
                        class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[13px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-[12px] file:font-medium file:text-white focus:border-accent" />
                    <p id="group-qris-hint" class="mt-1.5 text-xs text-muted">
                        {{ $group->qris_image
                            ? 'Biarkan kosong kalau tidak ingin mengganti gambar QRIS yang sekarang.'
                            : 'Belum ada gambar QRIS. Format gambar, maksimal 2 MB.' }}
                    </p>
                </div>
            </div>

            <button type="submit" id="group-submit" class="w-full rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
        </form>
        </div>
    </div>
</div>

<div id="member-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 id="member-modal-title" class="text-lg font-semibold text-ink">Tambah Anggota</h2>
            <button type="button" id="member-modal-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <form id="member-modal-form" class="mt-5 space-y-4">
            <div>
                <label for="member-modal-name" class="text-[13px] font-medium text-ink">Nama</label>
                <input type="text" id="member-modal-name" required maxlength="255" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Nama lengkap" />
            </div>

            <div>
                <label for="member-modal-email" class="text-[13px] font-medium text-ink">Email</label>
                <input type="email" id="member-modal-email" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="nama@email.com" />
            </div>

            <div id="member-modal-password-wrap">
                <label for="member-modal-password" class="text-[13px] font-medium text-ink">Password</label>
                <input type="password" id="member-modal-password" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Minimal 8 karakter" />
                <p id="member-modal-password-hint" class="mt-1 hidden text-xs text-muted">Kosongkan jika tidak ingin mengubah password.</p>
            </div>

            <div>
                <label for="member-modal-role" class="text-[13px] font-medium text-ink">Peran</label>
                <select id="member-modal-role" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                    <option value="student">Siswa</option>
                    <option value="treasurer">Bendahara</option>
                </select>
            </div>

            <p id="member-modal-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="member-modal-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="member-modal-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var csrf = '{{ csrf_token() }}';
        var membersBase = '{{ url('treasurer/group/members') }}';
        var groupUrl = '{{ route('treasurer.group.index') }}';
        var groupRefreshInviteUrl = '{{ route('treasurer.group.invite-code.refresh') }}';

        // ---- Form informasi grup (di dalam modal) ----
        var groupDetailModal = document.getElementById('group-detail-modal');
        var groupDetailOpen = document.getElementById('group-detail-open');
        var groupDetailClose = document.getElementById('group-detail-close');
        var nameLabel = document.getElementById('group-name-label');
        var groupForm = document.getElementById('group-form');
        var nameInput = document.getElementById('group-name');
        var codeInput = document.getElementById('group-invite-code');
        var updatedAtEl = document.getElementById('group-updated-at');
        var groupError = document.getElementById('group-error');
        var groupSuccess = document.getElementById('group-success');
        var groupSubmit = document.getElementById('group-submit');
        var copyBtn = document.getElementById('group-invite-copy');
        var refreshBtn = document.getElementById('group-invite-refresh');
        var qrisFileInput = document.getElementById('group-qris-file');

        function openGroupDetail() {
            groupError.classList.add('hidden');
            groupSuccess.classList.add('hidden');
            groupDetailModal.classList.remove('hidden');
            groupDetailModal.classList.add('flex');
        }

        function closeGroupDetail() {
            groupDetailModal.classList.add('hidden');
            groupDetailModal.classList.remove('flex');
            groupError.classList.add('hidden');
            groupSuccess.classList.add('hidden');
        }

        groupDetailOpen.addEventListener('click', openGroupDetail);
        groupDetailClose.addEventListener('click', closeGroupDetail);
        groupDetailModal.addEventListener('click', function (e) { if (e.target === groupDetailModal) closeGroupDetail(); });

        function showGroupError(msg) {
            groupSuccess.classList.add('hidden');
            groupError.textContent = msg;
            groupError.classList.remove('hidden');
        }

        function showGroupSuccess(msg) {
            groupError.classList.add('hidden');
            groupSuccess.textContent = msg;
            groupSuccess.classList.remove('hidden');
        }

        // ---- Simpan grup: nama + (opsional) gambar QRIS dalam SATU tombol ----
        // Kalau ada berkas QRIS dipilih, payload dikirim sebagai FormData
        // (POST + _method=PUT) supaya Laravel tetap cocok dengan route PUT.
        groupForm.addEventListener('submit', function (e) {
            e.preventDefault();
            groupError.classList.add('hidden');
            groupSuccess.classList.add('hidden');

            var hasNewQris = qrisFileInput && qrisFileInput.files.length > 0;
            var payload;
            var headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf };

            if (hasNewQris) {
                payload = new FormData();
                payload.append('_method', 'PUT');
                payload.append('name', nameInput.value);
                payload.append('qris_image', qrisFileInput.files[0]);
            } else {
                payload = JSON.stringify({ name: nameInput.value });
                headers['Content-Type'] = 'application/json';
            }

            groupSubmit.disabled = true;
            groupSubmit.textContent = 'Menyimpan...';

            fetch(groupUrl, {
                method: 'POST',
                headers: headers,
                body: payload,
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function (data) {
                    if (data.updated_at_human) updatedAtEl.textContent = data.updated_at_human;
                    if (data.name) nameLabel.textContent = data.name;

                    if (hasNewQris) {
                        // pratinjau di halaman Grup ikut diperbarui tanpa reload
                        window.dispatchEvent(new CustomEvent('qris:updated', {
                            detail: { url: data.qris_url },
                        }));
                        qrisFileInput.value = '';
                    }

                    closeGroupDetail();
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan grup.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    showGroupError(msg);
                })
                .finally(function () {
                    groupSubmit.disabled = false;
                    groupSubmit.textContent = 'Simpan';
                });
        });

        copyBtn.addEventListener('click', function () {
            var value = codeInput.value;
            if (!value) return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(function () {
                    showGroupSuccess('Kode undangan disalin.');
                }).catch(function () {
                    codeInput.select();
                    document.execCommand('copy');
                    showGroupSuccess('Kode undangan disalin.');
                });
            } else {
                codeInput.select();
                document.execCommand('copy');
                showGroupSuccess('Kode undangan disalin.');
            }
        });

        refreshBtn.addEventListener('click', function () {
            if (!confirm('Buat kode undangan baru? Kode lama tidak akan berlaku lagi.')) return;

            refreshBtn.disabled = true;

            fetch(groupRefreshInviteUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function (data) {
                    codeInput.value = data.invite_code;
                    showGroupSuccess('Kode undangan berhasil diperbarui.');
                })
                .catch(function (d) {
                    showGroupError((d && d.message) || 'Gagal memperbarui kode undangan.');
                })
                .finally(function () {
                    refreshBtn.disabled = false;
                });
        });

        // ---- Modal anggota ----
        var modal = document.getElementById('member-modal');
        var openBtn = document.getElementById('member-create-open');
        var closeBtn = document.getElementById('member-modal-close');
        var cancelBtn = document.getElementById('member-modal-cancel');
        var modalTitle = document.getElementById('member-modal-title');
        var form = document.getElementById('member-modal-form');
        var nameEl = document.getElementById('member-modal-name');
        var emailEl = document.getElementById('member-modal-email');
        var passwordEl = document.getElementById('member-modal-password');
        var passwordWrap = document.getElementById('member-modal-password-wrap');
        var passwordHint = document.getElementById('member-modal-password-hint');
        var roleEl = document.getElementById('member-modal-role');
        var submitBtn = document.getElementById('member-modal-submit');
        var errorEl = document.getElementById('member-modal-error');

        var mode = 'create';
        var currentId = null;

        function showError(msg) {
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            errorEl.classList.add('hidden');
            form.reset();
            currentId = null;
        }

        openBtn.addEventListener('click', function () {
            mode = 'create';
            currentId = null;
            modalTitle.textContent = 'Tambah Anggota';
            submitBtn.textContent = 'Simpan';
            form.reset();
            errorEl.classList.add('hidden');
            passwordWrap.classList.remove('hidden');
            passwordEl.setAttribute('required', 'required');
            passwordEl.setAttribute('placeholder', 'Minimal 8 karakter');
            passwordHint.classList.add('hidden');
            roleEl.value = 'student';
            openModal();
        });

        window.addEventListener('member:edit', function (e) {
            var member = e.detail;
            mode = 'edit';
            currentId = member.id;
            modalTitle.textContent = 'Edit Anggota';
            submitBtn.textContent = 'Simpan Perubahan';
            nameEl.value = member.name;
            emailEl.value = member.email;
            passwordEl.value = '';
            passwordEl.removeAttribute('required');
            passwordEl.setAttribute('placeholder', 'Biarkan kosong jika tidak diubah');
            passwordHint.classList.remove('hidden');
            roleEl.value = member.role || 'student';
            errorEl.classList.add('hidden');
            openModal();
        });

        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorEl.classList.add('hidden');

            var payload = {
                name: nameEl.value,
                email: emailEl.value,
                role: roleEl.value,
            };

            var url = membersBase;
            var method = 'POST';

            if (mode === 'edit') {
                url = membersBase + '/' + currentId;
                method = 'PUT';
            } else {
                payload.password = passwordEl.value;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            fetch(url, {
                method: method,
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
                    close();
                    window.dispatchEvent(new CustomEvent('members:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan anggota.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    showError(msg);
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.textContent = mode === 'edit' ? 'Simpan Perubahan' : 'Simpan';
                });
        });
    })();
</script>
