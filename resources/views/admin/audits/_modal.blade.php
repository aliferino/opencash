<div id="audit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-lg rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-ink">Detail Perubahan</h2>
                <p id="audit-modal-subtitle" class="mt-0.5 text-[13px] text-muted"></p>
            </div>
            <button type="button" id="audit-modal-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <div id="audit-modal-meta" class="mt-4 grid gap-2 rounded-md border border-line bg-bg/40 p-3 text-[13px] sm:grid-cols-2"></div>

        <div class="mt-4 grid grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_minmax(0,1.2fr)] gap-2 px-3 text-[11px] font-medium uppercase tracking-wide text-muted">
            <span>Kolom</span>
            <span>Sebelum</span>
            <span>Sesudah</span>
        </div>
        <div id="audit-modal-changes" class="mt-1 max-h-[45vh] overflow-y-auto rounded-md border border-line"></div>

        <p class="mt-4 text-xs text-muted">Log ini bersifat readonly dan dibuat otomatis oleh sistem — tidak bisa diubah atau dihapus.</p>

        <div class="mt-5 flex items-center justify-end">
            <button type="button" id="audit-modal-ok" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Tutup</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('audit-modal');
        var closeBtn = document.getElementById('audit-modal-close');
        var okBtn = document.getElementById('audit-modal-ok');
        var subtitleEl = document.getElementById('audit-modal-subtitle');
        var metaEl = document.getElementById('audit-modal-meta');
        var changesEl = document.getElementById('audit-modal-changes');

        var fieldLabels = {
            name: 'Nama',
            email: 'Email',
            role: 'Peran',
            group_id: 'Grup',
            password: 'Password',
            invite_code: 'Kode Undangan',
        };

        var roleLabels = { admin: 'Admin', treasurer: 'Bendahara', student: 'Siswa' };
        var actionLabels = {
            created: 'Dibuat',
            registered: 'Daftar Sendiri',
            updated: 'Diubah',
            role_changed: 'Peran Diubah',
            group_changed: 'Pindah Grup',
            joined: 'Masuk Grup',
            invite_code_refreshed: 'Kode Undangan Diperbarui',
            deleted: 'Dihapus',
        };
        var subjectLabels = { user: 'Pengguna', group: 'Grup' };
        var sensitiveFields = ['password'];

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function fieldLabel(key) {
            return fieldLabels[key] || key;
        }

        function formatValue(key, value) {
            if (sensitiveFields.indexOf(key) !== -1) return '••••••••';
            if (key === 'role') return value == null ? '—' : (roleLabels[value] || value);
            if (key === 'group_id') return value == null ? 'Tanpa grup' : 'Grup #' + value;
            if (value === null || value === undefined || value === '') return '—';
            return String(value);
        }

        function metaRow(label, value) {
            return '<div class="flex items-center justify-between gap-3">' +
                '<span class="text-muted">' + escapeHtml(label) + '</span>' +
                '<span class="text-right font-medium text-ink">' + escapeHtml(value) + '</span>' +
            '</div>';
        }

        function changeRow(key, oldValues, newValues) {
            var hasOld = Object.prototype.hasOwnProperty.call(oldValues, key);
            var hasNew = Object.prototype.hasOwnProperty.call(newValues, key);

            var oldCell = hasOld
                ? '<span class="rounded bg-red-500/10 px-1.5 py-0.5 text-red-300">' + escapeHtml(formatValue(key, oldValues[key])) + '</span>'
                : '<span class="text-muted">—</span>';

            var newCell = hasNew
                ? '<span class="rounded bg-emerald-500/10 px-1.5 py-0.5 text-emerald-300">' + escapeHtml(formatValue(key, newValues[key])) + '</span>'
                : '<span class="text-muted">dihapus</span>';

            return '' +
                '<div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_minmax(0,1.2fr)] items-center gap-2 border-b border-line px-3 py-2.5 text-[13px] last:border-0">' +
                    '<span class="font-medium text-ink">' + escapeHtml(fieldLabel(key)) + '</span>' +
                    '<span class="break-words">' + oldCell + '</span>' +
                    '<span class="break-words">' + newCell + '</span>' +
                '</div>';
        }

        function open(audit) {
            var subjectType = subjectLabels[audit.subject_type] || audit.subject_type || '—';
            subtitleEl.textContent = (audit.subject_name || '—') + ' — ' + subjectType;

            metaEl.innerHTML = [
                metaRow('Aksi', actionLabels[audit.action] || audit.action),
                metaRow('Pelaku', audit.actor_name || 'Sistem'),
                metaRow('Grup', audit.group_name || '—'),
                metaRow('Waktu', audit.created_at ? new Date(audit.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'),
            ].join('');

            var oldValues = audit.old_values || {};
            var newValues = audit.new_values || {};

            // union: kolom yang berubah bisa ada di old saja (dihapus) atau new saja (diisi)
            var keys = Object.keys(newValues);
            Object.keys(oldValues).forEach(function (key) {
                if (keys.indexOf(key) === -1) keys.push(key);
            });

            changesEl.innerHTML = keys.length
                ? keys.map(function (key) { return changeRow(key, oldValues, newValues); }).join('')
                : '<p class="px-3 py-4 text-center text-[13px] text-muted">Tidak ada detail perubahan pada log ini.</p>';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.addEventListener('audit:view', function (e) { open(e.detail); });
        closeBtn.addEventListener('click', close);
        okBtn.addEventListener('click', close);
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    })();
</script>
