<div id="user-create-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Tambah Pengguna</h2>
            <button type="button" id="user-create-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <form id="user-create-form" class="mt-5 space-y-4">
            <div>
                <label for="user-name" class="text-[13px] font-medium text-ink">Nama</label>
                <input type="text" id="user-name" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Nama lengkap" />
            </div>

            <div>
                <label for="user-email" class="text-[13px] font-medium text-ink">Email</label>
                <input type="email" id="user-email" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="nama@email.com" />
            </div>

            <div>
                <label for="user-password" class="text-[13px] font-medium text-ink">Password</label>
                <input type="password" id="user-password" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Minimal 8 karakter" />
            </div>

            <div>
                <label for="user-role" class="text-[13px] font-medium text-ink">Peran</label>
                <select id="user-role" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                    <option value="admin">Admin</option>
                    <option value="treasurer">Bendahara</option>
                    <option value="student">Siswa</option>
                </select>
            </div>

            <div id="user-group-field">
                <label for="user-group" class="text-[13px] font-medium text-ink">Kelas</label>
                <select id="user-group" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                    <option value="">Memuat kelas...</option>
                </select>
            </div>

            <p id="user-create-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="user-create-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('user-create-modal');
        var openBtn = document.getElementById('user-create-open');
        var closeBtn = document.getElementById('user-create-close');
        var cancelBtn = document.getElementById('user-create-cancel');
        var form = document.getElementById('user-create-form');
        var errorEl = document.getElementById('user-create-error');
        var roleSelect = document.getElementById('user-role');
        var groupField = document.getElementById('user-group-field');
        var groupSelect = document.getElementById('user-group');
        var groupsLoaded = false;

        function loadGroups() {
            if (groupsLoaded) return;

            fetch('{{ route('admin.groups.index') }}', { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    groupSelect.innerHTML = json.data.length
                        ? json.data.map(function (group) {
                            return '<option value="' + group.id + '">' + group.name + '</option>';
                        }).join('')
                        : '<option value="">Belum ada kelas</option>';
                    groupsLoaded = true;
                });
        }

        function toggleGroupField() {
            if (roleSelect.value === 'admin') {
                groupField.classList.add('hidden');
            } else {
                groupField.classList.remove('hidden');
                loadGroups();
            }
        }

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            toggleGroupField();
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            errorEl.classList.add('hidden');
            form.reset();
            toggleGroupField();
        }

        openBtn.addEventListener('click', open);
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        roleSelect.addEventListener('change', toggleGroupField);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorEl.classList.add('hidden');

            fetch('{{ route('admin.users.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    name: document.getElementById('user-name').value,
                    email: document.getElementById('user-email').value,
                    password: document.getElementById('user-password').value,
                    role: roleSelect.value,
                    group_id: roleSelect.value === 'admin' ? null : groupSelect.value,
                }),
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (data) { throw data; });
                    return res.json();
                })
                .then(function () {
                    close();
                    window.dispatchEvent(new CustomEvent('users:refresh'));
                })
                .catch(function (data) {
                    var msg = (data && data.message) || 'Gagal menyimpan pengguna.';
                    if (data && data.errors) {
                        var first = Object.values(data.errors)[0];
                        if (first && first[0]) msg = first[0];
                    }
                    errorEl.textContent = msg;
                    errorEl.classList.remove('hidden');
                });
        });
    })();
</script>