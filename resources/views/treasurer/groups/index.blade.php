@extends('layouts.panel')

@section('title', 'Grup — OpenCash')

@section('panel')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Grup</h1>
            <p class="mt-1 text-[15px] text-muted">Kelola informasi dan anggota kelas <span id="group-name-label" class="font-medium text-ink">{{ $group->name }}</span>.</p>
        </div>

        <button
            type="button"
            id="group-detail-open"
            class="flex shrink-0 items-center gap-2 rounded-md border border-line px-4 py-2.5 text-[14px] font-medium text-ink transition-colors hover:bg-white/5"
        >
            <i data-lucide="settings-2" class="h-4 w-4" stroke-width="1.8"></i>
            Detail Grup
        </button>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-3">
        <div class="rounded-2xl border border-accent/40 bg-accent-tint p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent text-white">
                <i data-lucide="users" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Total Anggota</p>
            <p class="mt-1 text-3xl font-semibold text-ink">{{ $group->students_count + $group->treasurers_count }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                <i data-lucide="graduation-cap" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Siswa</p>
            <p class="mt-1 text-3xl font-semibold text-emerald-400">{{ $group->students_count }}</p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-6">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                <i data-lucide="user-cog" class="h-4 w-4" stroke-width="1.8"></i>
            </span>
            <p class="mt-4 text-[13px] text-muted">Bendahara</p>
            <p class="mt-1 text-3xl font-semibold text-amber-400">{{ $group->treasurers_count }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-line bg-surface p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-[15px] font-semibold text-ink">QRIS Pembayaran</h2>
                <p class="mt-1 text-[13px] text-muted">Gambar QRIS kelas yang dipakai siswa untuk transfer mandiri.</p>
            </div>

            <div class="flex items-center gap-3">
                <div id="group-qris-preview-wrap" class="{{ $group->qris_image ? '' : 'hidden' }} shrink-0">
                    <img id="group-qris-preview" src="{{ $group->qris_image ? Storage::disk('public')->url($group->qris_image) : '' }}"
                        alt="QRIS kelas" class="h-24 w-24 rounded-md border border-line object-contain" />
                </div>

                <div class="min-w-[14rem]">
                    <input type="file" id="group-qris-file" accept="image/*"
                        class="w-full rounded-md border border-line bg-bg px-3 py-2.5 text-[13px] text-ink outline-none file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-[12px] file:font-medium file:text-white focus:border-accent" />
                    <p class="mt-1.5 text-xs text-muted">Format gambar, maksimal 2 MB.</p>
                </div>

                <button type="button" id="group-qris-submit"
                    class="shrink-0 rounded-md bg-accent px-4 py-2.5 text-[14px] font-medium text-white transition-colors hover:bg-accent-bright hover:text-[#070b18]">
                    Unggah
                </button>
            </div>
        </div>

        <p id="group-qris-error" class="mt-3 hidden text-[13px] text-red-400"></p>
        <p id="group-qris-success" class="mt-3 hidden text-[13px] text-emerald-400"></p>
    </div>

    <script>
        (function () {
            var fileInput = document.getElementById('group-qris-file');
            var submitBtn = document.getElementById('group-qris-submit');
            var previewWrap = document.getElementById('group-qris-preview-wrap');
            var preview = document.getElementById('group-qris-preview');
            var errorEl = document.getElementById('group-qris-error');
            var successEl = document.getElementById('group-qris-success');

            var endpoint = '{{ route('treasurer.group.qris') }}';
            var csrf = '{{ csrf_token() }}';

            submitBtn.addEventListener('click', function () {
                errorEl.classList.add('hidden');
                successEl.classList.add('hidden');

                if (!fileInput.files.length) {
                    errorEl.textContent = 'Pilih gambar QRIS terlebih dahulu.';
                    errorEl.classList.remove('hidden');
                    return;
                }

                var payload = new FormData();
                payload.append('qris_image', fileInput.files[0]);

                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengunggah...';

                fetch(endpoint, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: payload,
                })
                    .then(function (res) {
                        if (!res.ok) return res.json().then(function (d) { throw d; });
                        return res.json();
                    })
                    .then(function (data) {
                        preview.src = data.qris_url;
                        previewWrap.classList.remove('hidden');
                        fileInput.value = '';
                        successEl.textContent = 'QRIS berhasil diunggah.';
                        successEl.classList.remove('hidden');
                    })
                    .catch(function (d) {
                        var msg = (d && d.message) || 'Gagal mengunggah QRIS.';
                        if (d && d.errors) { var first = Object.values(d.errors)[0]; if (first && first[0]) msg = first[0]; }
                        errorEl.textContent = msg;
                        errorEl.classList.remove('hidden');
                    })
                    .finally(function () {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Unggah';
                    });
            });
        })();
    </script>

    @include('treasurer.groups._table')
    @include('treasurer.groups._modal')
@endsection
