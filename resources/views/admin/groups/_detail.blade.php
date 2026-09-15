@extends('layouts.panel')

@section('title', $group->name . ' — Grup — OpenCash')

@section('panel')
    
<div>
    <a href="{{ route('admin.groups.index') }}" class="inline-flex items-center gap-1.5 text-[13px] font-medium text-muted transition-colors hover:text-ink">
        <i data-lucide="arrow-left" class="h-3.5 w-3.5" stroke-width="1.8"></i>
        Kembali ke Grup
    </a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $group->name }}</h1>
    <p class="mt-1 text-[15px] text-muted">{{ $group->users_count }} anggota terdaftar di grup ini.</p>
</div>

<div class="mt-6 flex items-center justify-between gap-3">
    <div class="relative max-w-sm flex-1">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" stroke-width="1.8"></i>
        <input
            type="text"
            id="group-members-search"
            placeholder="Cari nama anggota..."
            class="w-full rounded-md border border-line bg-surface py-2.5 pl-9 pr-3 text-[14px] text-ink placeholder:text-muted outline-none focus:border-accent"
        />
    </div>

    <button
        type="button"
        id="member-create-open"
        class="flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]"
    >
        <i data-lucide="plus" class="h-4 w-4" stroke-width="2"></i>
        Anggota
    </button>
</div>

<div class="mt-4 grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div
            id="group-members-table"
            data-endpoint="{{ route('admin.users.index') }}"
            data-group-id="{{ $group->id }}"
            class="overflow-hidden rounded-2xl border border-line bg-surface"
        >
            <table class="w-full text-left text-[14px]">
                <thead>
                    <tr class="border-b border-line text-xs uppercase tracking-wide text-muted">
                        <th class="px-6 py-4 font-medium">Nama</th>
                        <th class="px-6 py-4 font-medium">Email</th>
                        <th class="px-6 py-4 font-medium">Peran</th>
                    </tr>
                </thead>
                <tbody id="group-members-table-body" class="divide-y divide-line">
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-muted">Memuat data...</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-line px-6 py-4">
                <p id="group-members-table-info" class="text-xs text-muted"></p>
                <div class="flex items-center gap-2">
                    <button type="button" id="group-members-prev" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Sebelumnya</button>
                    <button type="button" id="group-members-next" class="rounded-md border border-line px-3 py-1.5 text-[13px] text-muted transition-colors hover:text-ink disabled:cursor-not-allowed disabled:opacity-40" disabled>Berikutnya</button>
                </div>
            </div>
        </div>
    </div>

    <div class="h-fit rounded-2xl border border-line bg-surface p-6">
        <h2 class="text-lg font-semibold text-ink">Pengaturan Grup</h2>

        <form id="group-detail-form" class="mt-5 space-y-4">
            <div>
                <label for="group-detail-name" class="text-[13px] font-medium text-ink">Nama Grup</label>
                <input
                    type="text"
                    id="group-detail-name"
                    required
                    value="{{ $group->name }}"
                    class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent"
                />
            </div>

            <div>
                <label class="text-[13px] font-medium text-ink">Kode Undangan</label>
                <div class="mt-1.5 flex gap-2">
                    <input
                        type="text"
                        id="group-detail-invite-code"
                        readonly
                        value="{{ $group->invite_code }}"
                        class="w-full rounded-md border border-line bg-bg px-3 py-2.5 font-mono text-[14px] text-muted outline-none"
                    />
                    <button
                        type="button"
                        id="group-detail-refresh-invite"
                        class="flex shrink-0 items-center gap-1.5 rounded-md border border-line px-3.5 py-2.5 text-[13px] font-medium text-muted transition-colors hover:text-ink"
                        title="Buat kode baru"
                    >
                        <i data-lucide="refresh-cw" class="h-4 w-4" stroke-width="1.8"></i>
                    </button>
                </div>
            </div>

            <div class="space-y-1 border-t border-line pt-4 text-[13px]">
                <p class="flex items-center justify-between text-muted">
                    <span>Dibuat</span>
                    <span class="text-ink">{{ optional($group->created_at)->format('d M Y, H:i') ?? '—' }}</span>
                </p>
                <p class="flex items-center justify-between text-muted">
                    <span>Diperbarui</span>
                    <span id="group-detail-updated-at" class="text-ink">{{ optional($group->updated_at)->format('d M Y, H:i') ?? '—' }}</span>
                </p>
            </div>

            <p id="group-detail-error" class="hidden text-[13px] text-red-400"></p>
            <p id="group-detail-success" class="hidden text-[13px] text-emerald-400"></p>

            <button type="submit" class="w-full rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
        </form>
    </div>
</div>

<div class="mt-6 rounded-2xl border border-red-500/30 bg-surface p-6">
    <h2 class="text-[15px] font-semibold text-red-400">Zona Berbahaya</h2>
    <p class="mt-1 text-[13px] text-muted">Menghapus grup akan menghapus seluruh data kas terkait grup ini. Tindakan ini tidak bisa dibatalkan.</p>

    <button
        type="button"
        id="group-detail-delete"
        class="mt-4 flex items-center justify-center gap-2 rounded-md border border-red-500/30 px-4 py-2.5 text-[14px] font-medium text-red-400 transition-colors hover:bg-red-500/10"
    >
        <i data-lucide="trash-2" class="h-4 w-4" stroke-width="1.8"></i>
        Hapus Grup
    </button>
</div>

<div id="member-create-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Tambah Anggota</h2>
            <button type="button" id="member-create-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <div class="mt-5 flex gap-1 rounded-md bg-bg p-1">
            <button type="button" id="member-tab-new" class="member-tab flex-1 rounded-md px-3 py-2 text-[13px] font-medium transition-colors">Buat Baru</button>
            <button type="button" id="member-tab-existing" class="member-tab flex-1 rounded-md px-3 py-2 text-[13px] font-medium transition-colors">Pilih Terdaftar</button>
        </div>

        <form id="member-create-form" class="mt-4 space-y-4">
            <div id="member-fields-new" class="space-y-4">
                <div>
                    <label for="member-name" class="text-[13px] font-medium text-ink">Nama</label>
                    <input type="text" id="member-name" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Nama lengkap" />
                </div>

                <div>
                    <label for="member-email" class="text-[13px] font-medium text-ink">Email</label>
                    <input type="email" id="member-email" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="nama@email.com" />
                </div>

                <div>
                    <label for="member-password" class="text-[13px] font-medium text-ink">Password</label>
                    <input type="password" id="member-password" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Minimal 8 karakter" />
                </div>

                <div>
                    <label for="member-role" class="text-[13px] font-medium text-ink">Peran</label>
                    <select id="member-role" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                        <option value="student">Siswa</option>
                        <option value="treasurer">Bendahara</option>
                    </select>
                </div>
            </div>

            <div id="member-fields-existing" class="hidden space-y-1.5">
                <label for="member-existing-select" class="text-[13px] font-medium text-ink">Pilih Pengguna</label>
                <select id="member-existing-select" class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent">
                    <option value="">Memuat pengguna...</option>
                </select>
                <p class="text-xs text-muted">Hanya menampilkan bendahara/siswa yang belum tergabung di grup manapun.</p>
            </div>

            <p id="member-create-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="member-create-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" id="member-create-submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var groupId = '{{ $group->id }}';
        var groupUpdateUrl = '{{ route('admin.groups.update', $group) }}';
        var groupDeleteUrl = '{{ route('admin.groups.destroy', $group) }}';
        var groupRefreshInviteUrl = '{{ route('admin.groups.invite-code.refresh', $group) }}';
        var groupMembersStoreUrl = '{{ route('admin.groups.members.store', $group) }}';
        var groupMembersAttachUrl = '{{ route('admin.groups.members.attach', $group) }}';
        var groupsIndexUrl = '{{ route('admin.groups.index') }}';
        var usersEndpoint = document.getElementById('group-members-table').dataset.endpoint;
        var csrfToken = '{{ csrf_token() }}';

        // ---- Members table ----
        var body = document.getElementById('group-members-table-body');
        var info = document.getElementById('group-members-table-info');
        var prevBtn = document.getElementById('group-members-prev');
        var nextBtn = document.getElementById('group-members-next');
        var searchInput = document.getElementById('group-members-search');
        var page = 1;
        var searchTimer = null;

        var roleLabels = { admin: 'Admin', treasurer: 'Bendahara', student: 'Siswa' };

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function emptyRow(text) {
            return '<tr><td colspan="3" class="px-6 py-10 text-center text-muted">' + text + '</td></tr>';
        }

        function renderMemberRow(user) {
            var roleLabel = roleLabels[user.role] || '—';

            return '' +
                '<tr class="transition-colors hover:bg-white/5">' +
                    '<td class="px-6 py-4 font-medium text-ink">' + escapeHtml(user.name) + '</td>' +
                    '<td class="px-6 py-4 text-muted">' + escapeHtml(user.email) + '</td>' +
                    '<td class="px-6 py-4"><span class="rounded-full bg-accent-tint px-2.5 py-1 text-xs font-medium text-accent-bright">' + roleLabel + '</span></td>' +
                '</tr>';
        }

        function loadMembers(targetPage) {
            body.innerHTML = emptyRow('Memuat data...');

            var url = usersEndpoint + '?page=' + targetPage + '&group_id=' + groupId;
            if (searchInput.value.trim()) {
                url += '&search=' + encodeURIComponent(searchInput.value.trim());
            }

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    page = json.current_page;

                    body.innerHTML = json.data.length
                        ? json.data.map(renderMemberRow).join('')
                        : emptyRow('Belum ada anggota.');

                    info.textContent = 'Menampilkan ' + json.data.length + ' dari ' + json.total + ' anggota — halaman ' + json.current_page + ' dari ' + json.last_page;
                    prevBtn.disabled = !json.prev_page_url;
                    nextBtn.disabled = !json.next_page_url;

                    if (window.lucideRefresh) window.lucideRefresh();
                })
                .catch(function () {
                    body.innerHTML = emptyRow('Gagal memuat data.');
                });
        }

        prevBtn.addEventListener('click', function () { loadMembers(page - 1); });
        nextBtn.addEventListener('click', function () { loadMembers(page + 1); });

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { loadMembers(1); }, 350);
        });

        loadMembers(page);

        // ---- Group settings form ----
        var form = document.getElementById('group-detail-form');
        var nameInput = document.getElementById('group-detail-name');
        var codeInput = document.getElementById('group-detail-invite-code');
        var updatedAtEl = document.getElementById('group-detail-updated-at');
        var errorEl = document.getElementById('group-detail-error');
        var successEl = document.getElementById('group-detail-success');
        var refreshBtn = document.getElementById('group-detail-refresh-invite');
        var deleteBtn = document.getElementById('group-detail-delete');

        function showError(msg) {
            successEl.classList.add('hidden');
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
        }

        function showSuccess(msg) {
            errorEl.classList.add('hidden');
            successEl.textContent = msg;
            successEl.classList.remove('hidden');
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            fetch(groupUpdateUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name: nameInput.value }),
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function (data) {
                    showSuccess('Nama grup berhasil diperbarui.');
                    if (data.updated_at_human) updatedAtEl.textContent = data.updated_at_human;
                    window.dispatchEvent(new CustomEvent('groups:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menyimpan grup.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    showError(msg);
                });
        });

        refreshBtn.addEventListener('click', function () {
            fetch(groupRefreshInviteUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    codeInput.value = data.invite_code;
                    showSuccess('Kode undangan berhasil diperbarui.');
                })
                .catch(function () { showError('Gagal memperbarui kode undangan.'); });
        });

        deleteBtn.addEventListener('click', function () {
            if (!confirm('Hapus grup ini beserta semua datanya? Tindakan ini tidak bisa dibatalkan.')) return;

            fetch(groupDeleteUrl, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(function () {
                    window.location.href = groupsIndexUrl;
                })
                .catch(function () { showError('Gagal menghapus grup.'); });
        });

        // ---- Add member modal ----
        var memberModal = document.getElementById('member-create-modal');
        var memberOpenBtn = document.getElementById('member-create-open');
        var memberCloseBtn = document.getElementById('member-create-close');
        var memberCancelBtn = document.getElementById('member-create-cancel');
        var memberForm = document.getElementById('member-create-form');
        var memberErrorEl = document.getElementById('member-create-error');
        var memberTabNewBtn = document.getElementById('member-tab-new');
        var memberTabExistingBtn = document.getElementById('member-tab-existing');
        var memberFieldsNew = document.getElementById('member-fields-new');
        var memberFieldsExisting = document.getElementById('member-fields-existing');
        var memberNameInput = document.getElementById('member-name');
        var memberEmailInput = document.getElementById('member-email');
        var memberPasswordInput = document.getElementById('member-password');
        var memberExistingSelect = document.getElementById('member-existing-select');
        var memberTab = 'new';
        var existingUsersLoaded = false;

        function setMemberTab(tab) {
            memberTab = tab;
            memberErrorEl.classList.add('hidden');

            if (tab === 'new') {
                memberFieldsNew.classList.remove('hidden');
                memberFieldsExisting.classList.add('hidden');
                memberNameInput.setAttribute('required', 'required');
                memberEmailInput.setAttribute('required', 'required');
                memberPasswordInput.setAttribute('required', 'required');
                memberExistingSelect.removeAttribute('required');
                memberTabNewBtn.classList.add('bg-accent-tint', 'text-accent-bright');
                memberTabNewBtn.classList.remove('text-muted');
                memberTabExistingBtn.classList.remove('bg-accent-tint', 'text-accent-bright');
                memberTabExistingBtn.classList.add('text-muted');
            } else {
                memberFieldsNew.classList.add('hidden');
                memberFieldsExisting.classList.remove('hidden');
                memberNameInput.removeAttribute('required');
                memberEmailInput.removeAttribute('required');
                memberPasswordInput.removeAttribute('required');
                memberExistingSelect.setAttribute('required', 'required');
                memberTabExistingBtn.classList.add('bg-accent-tint', 'text-accent-bright');
                memberTabExistingBtn.classList.remove('text-muted');
                memberTabNewBtn.classList.remove('bg-accent-tint', 'text-accent-bright');
                memberTabNewBtn.classList.add('text-muted');
                loadExistingUsers();
            }
        }

        function loadExistingUsers(force) {
            if (existingUsersLoaded && !force) return;

            memberExistingSelect.innerHTML = '<option value="">Memuat pengguna...</option>';

            fetch(usersEndpoint + '?unassigned=1&per_page=100', { headers: { Accept: 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    var roleLabels = { treasurer: 'Bendahara', student: 'Siswa' };

                    memberExistingSelect.innerHTML = json.data.length
                        ? json.data.map(function (user) {
                            var label = user.name + ' (' + user.email + ') — ' + (roleLabels[user.role] || user.role);
                            return '<option value="' + user.id + '">' + label + '</option>';
                        }).join('')
                        : '<option value="">Tidak ada pengguna yang bisa ditambahkan</option>';

                    existingUsersLoaded = true;
                })
                .catch(function () {
                    memberExistingSelect.innerHTML = '<option value="">Gagal memuat pengguna</option>';
                });
        }

        function openMemberModal() {
            memberModal.classList.remove('hidden');
            memberModal.classList.add('flex');
            setMemberTab('new');
        }

        function closeMemberModal() {
            memberModal.classList.add('hidden');
            memberModal.classList.remove('flex');
            memberErrorEl.classList.add('hidden');
            memberForm.reset();
            existingUsersLoaded = false;
        }

        memberOpenBtn.addEventListener('click', openMemberModal);
        memberCloseBtn.addEventListener('click', closeMemberModal);
        memberCancelBtn.addEventListener('click', closeMemberModal);
        memberModal.addEventListener('click', function (e) { if (e.target === memberModal) closeMemberModal(); });
        memberTabNewBtn.addEventListener('click', function () { setMemberTab('new'); });
        memberTabExistingBtn.addEventListener('click', function () { setMemberTab('existing'); });

        memberForm.addEventListener('submit', function (e) {
            e.preventDefault();
            memberErrorEl.classList.add('hidden');

            var url = memberTab === 'new' ? groupMembersStoreUrl : groupMembersAttachUrl;
            var body = memberTab === 'new'
                ? {
                    name: memberNameInput.value,
                    email: memberEmailInput.value,
                    password: memberPasswordInput.value,
                    role: document.getElementById('member-role').value,
                }
                : { user_id: memberExistingSelect.value };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(body),
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (d) { throw d; });
                    return res.json();
                })
                .then(function () {
                    closeMemberModal();
                    loadMembers(1);
                    window.dispatchEvent(new CustomEvent('groups:refresh'));
                })
                .catch(function (d) {
                    var msg = (d && d.message) || 'Gagal menambahkan anggota.';
                    if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                    memberErrorEl.textContent = msg;
                    memberErrorEl.classList.remove('hidden');
                });
        });
    })();
</script>
@endsection