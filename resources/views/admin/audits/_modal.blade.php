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

        <div class="mt-5 grid grid-cols-3 gap-2 text-xs font-medium uppercase tracking-wide text-muted">
            <span>Kolom</span>
            <span>Sebelum</span>
            <span>Sesudah</span>
        </div>
        <div id="audit-modal-changes" class="mt-2 divide-y divide-line rounded-md border border-line"></div>

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
        var changesEl = document.getElementById('audit-modal-changes');

        var fieldLabels = { name: 'Nama', email: 'Email', role: 'Peran', group_id: 'Grup', password: 'Password' };
        var roleLabels = { admin: 'Admin', treasurer: 'Bendahara', student: 'Siswa' };
        var sensitiveFields = ['password', 'remember_token'];

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
            if (key === 'role') return roleLabels[value] || (value == null ? '—' : value);
            if (value === null || value === '') return '—';
            return String(value);
        }

        function open(audit) {
            var userName = audit.user ? audit.user.name : 'Pengguna terhapus';
            subtitleEl.textContent = userName + ' — dicatat otomatis oleh sistem';

            var newValues = audit.new_values || {};
            var oldValues = audit.old_values || {};
            var keys = Object.keys(newValues);

            changesEl.innerHTML = keys.length
                ? keys.map(function (key) {
                    return '' +
                        '<div class="grid grid-cols-3 gap-2 px-3 py-2.5 text-[13px]">' +
                            '<span class="font-medium text-ink">' + escapeHtml(fieldLabel(key)) + '</span>' +
                            '<span class="text-muted">' + escapeHtml(formatValue(key, oldValues[key])) + '</span>' +
                            '<span class="text-ink">' + escapeHtml(formatValue(key, newValues[key])) + '</span>' +
                        '</div>';
                }).join('')
                : '<p class="px-3 py-4 text-center text-[13px] text-muted">Tidak ada detail perubahan.</p>';

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
