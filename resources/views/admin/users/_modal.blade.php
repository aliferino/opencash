<div id="user-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 id="user-modal-title" class="text-lg font-semibold text-ink">Tambah Pengguna</h2>
            <button type="button" id="user-modal-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <form id="user-modal-form" class="mt-5 space-y-4">
            <div>
                <label for="user-modal-name" class="text-[13px] font-medium text-ink">Nama</label>
                <input type="text" id="user-modal-name" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Nama lengkap" />
            </div>

            <div>
                <label for="user-modal-email" class="text-[13px] font-medium text-ink">Email</label>
                <input type="email" id="user-modal-email" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="nama@email.com" />
            </div>

            <div>
                <label for="user-modal-password" class="text-[13px] font-medium text-ink">Password</label>
                <input type="password" id="user-modal-password" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Minimal 8 karakter" />
                <p id="user-modal-password-hint" class="mt-1 hidden text-xs text-muted">Kosongkan jika tidak ingin mengubah password.</p>
            </div>

            <div>
                <label for="user-modal-role" class="text-[13px] font-medium text-ink">Peran</label>
                <select id="user-modal-role" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                    <option value="admin">Admin</option>
                    <option value="treasurer">Bendahara</option>
                    <option value="student">Siswa</option>
                </select>
            </div>

            <div id="user-modal-group-info" class="hidden space-y-1.5">
                <label class="text-[13px] font-medium text-ink">Grup</label>
                <p id="user-modal-group-name" class="rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-muted"></p>
                <p class="text-xs text-muted">Keanggotaan grup hanya bisa diubah lewat halaman Grup.</p>
            </div>

            <p id="user-modal-error" class="hidden text-[13px] text-red-400"></p>
            <p id="user-modal-success" class="hidden text-[13px] text-emerald-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="user-modal-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="user-modal-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('user-modal');
        var openCreateBtn = document.getElementById('user-create-open');
        var closeBtn = document.getElementById('user-modal-close');
        var cancelBtn = document.getElementById('user-modal-cancel');
        var titleEl = document.getElementById('user-modal-title');
        var submitBtn = document.getElementById('user-modal-submit');
        var form = document.getElementById('user-modal-form');
        var nameInput = document.getElementById('user-modal-name');
        var emailInput = document.getElementById('user-modal-email');
        var passwordInput = document.getElementById('user-modal-password');
        var passwordHint = document.getElementById('user-modal-password-hint');
        var roleSelect = document.getElementById('user-modal-role');
        var groupInfo = document.getElementById('user-modal-group-info');
        var groupNameEl = document.getElementById('user-modal-group-name');
        var errorEl = document.getElementById('user-modal-error');
        var successEl = document.getElementById('user-modal-success');
        var baseUrl = '{{ route('admin.users.index') }}';

        var mode = 'create';
        var currentId = null;

        function showError(msg) {
            successEl.classList.add('hidden');
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
        }

        function clearMessages() {
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');
        }

        function openCreate() {
            mode = 'create';
            currentId = null;
            titleEl.textContent = 'Tambah Pengguna';
            submitBtn.textContent = 'Simpan';
            form.reset();
            roleSelect.value = 'student';
            passwordInput.setAttribute('required', 'required');
            passwordInput.setAttribute('placeholder', 'Minimal 8 karakter');
            passwordHint.classList.add('hidden');
            groupInfo.classList.add('hidden');
            clearMessages();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function openEdit(detail) {
            mode = 'edit';
            currentId = detail.id;
            titleEl.textContent = 'Kelola Pengguna';
            submitBtn.textContent = 'Simpan Perubahan';
            nameInput.value = detail.name;
            emailInput.value = detail.email;
            passwordInput.value = '';
            passwordInput.removeAttribute('required');
            passwordInput.setAttribute('placeholder', 'Biarkan kosong jika tidak diubah');
            passwordHint.classList.remove('hidden');
            roleSelect.value = detail.role || 'student';

            if (detail.groupName && detail.groupName !== '—') {
                groupNameEl.textContent = detail.groupName;
                groupInfo.classList.remove('hidden');
            } else {
                groupInfo.classList.add('hidden');
            }

            clearMessages();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentId = null;
        }

        openCreateBtn.addEventListener('click', openCreate);
        window.addEventListener('user:manage', function (e) { openEdit(e.detail); });
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearMessages();

            var payload = {
                name: nameInput.value,
                email: emailInput.value,
                role: roleSelect.value,
            };

            if (passwordInput.value) {
                payload.password = passwordInput.value;
            }

            var url = mode === 'create' ? baseUrl : baseUrl + '/' + currentId;
            var method = mode === 'create' ? 'POST' : 'PUT';

            if (mode === 'create') {
                payload.password = passwordInput.value;
            }

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
                    close();
                    window.dispatchEvent(new CustomEvent('users:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan pengguna.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    showError(msg);
                });
        });
    })();
</script>