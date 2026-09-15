<div id="group-create-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-ink">Tambah Kelas</h2>
            <button type="button" id="group-create-close" class="rounded-md p-1.5 text-muted transition-colors hover:bg-white/5 hover:text-ink">
                <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
            </button>
        </div>

        <form id="group-create-form" class="mt-5 space-y-4">
            <div>
                <label for="group-name" class="text-[13px] font-medium text-ink">Nama Kelas</label>
                <input type="text" id="group-name" required class="mt-1.5 w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[14px] text-ink outline-none focus:border-accent" placeholder="Contoh: Kelas 9A" />
            </div>

            <p id="group-create-error" class="hidden text-[13px] text-red-400"></p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="group-create-cancel" class="rounded-md px-4 py-2.5 text-[14px] font-medium text-muted transition-colors hover:text-ink">Batal</button>
                <button type="submit" class="rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('group-create-modal');
        var openBtn = document.getElementById('group-create-open');
        var closeBtn = document.getElementById('group-create-close');
        var cancelBtn = document.getElementById('group-create-cancel');
        var form = document.getElementById('group-create-form');
        var errorEl = document.getElementById('group-create-error');

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            errorEl.classList.add('hidden');
            form.reset();
        }

        openBtn.addEventListener('click', open);
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorEl.classList.add('hidden');

            fetch('{{ route('admin.groups.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ name: document.getElementById('group-name').value }),
            })
                .then(function (res) {
                    if (!res.ok) return res.json().then(function (data) { throw data; });
                    return res.json();
                })
                .then(function () {
                    close();
                    window.dispatchEvent(new CustomEvent('groups:refresh'));
                })
                .catch(function (data) {
                    var msg = (data && data.message) || 'Gagal menyimpan kelas.';
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