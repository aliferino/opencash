{{-- Modal viewer QRIS halaman Grup: zoom, geser/cubit, dan simpan ke perangkat.
     Dipisah dari modal Detail Grup supaya modal itu isinya cuma form
     (nama, kode undangan, input berkas QRIS) + satu tombol Simpan. --}}
<div id="qris-viewer" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/70 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="qris-viewer-title">
    <div class="flex max-h-[90vh] w-full max-w-3xl flex-col rounded-2xl border border-line bg-surface">
        <div class="flex shrink-0 items-center justify-between gap-3 border-b border-line px-5 py-4">
            <div class="min-w-0">
                <p id="qris-viewer-title" class="truncate text-[15px] font-semibold text-ink">QRIS {{ $group->name }}</p>
                <p id="qris-viewer-zoom" class="text-[11.5px] text-muted">100%</p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <button type="button" id="qris-zoom-out" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-line text-muted transition-colors hover:border-accent hover:text-accent-bright focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="Perkecil" title="Perkecil">
                    <i data-lucide="zoom-out" class="h-4 w-4" stroke-width="1.8"></i>
                </button>
                <button type="button" id="qris-zoom-in" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-line text-muted transition-colors hover:border-accent hover:text-accent-bright focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="Perbesar" title="Perbesar">
                    <i data-lucide="zoom-in" class="h-4 w-4" stroke-width="1.8"></i>
                </button>
                <button type="button" id="qris-zoom-reset" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-line text-muted transition-colors hover:border-accent hover:text-accent-bright focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="Kembalikan ukuran" title="Kembalikan ukuran">
                    <i data-lucide="maximize-2" class="h-4 w-4" stroke-width="1.8"></i>
                </button>
                <button type="button" id="qris-download" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-line text-muted transition-colors hover:border-accent hover:text-accent-bright focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="Simpan gambar ke perangkat" title="Simpan ke perangkat">
                    <i data-lucide="download" class="h-4 w-4" stroke-width="1.8"></i>
                </button>
                <button type="button" id="qris-viewer-close" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-line text-muted transition-colors hover:border-accent hover:text-accent-bright focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="Tutup" title="Tutup">
                    <i data-lucide="x" class="h-4 w-4" stroke-width="1.8"></i>
                </button>
            </div>
        </div>

        <div id="qris-stage" class="relative h-[60vh] min-h-0 cursor-grab overflow-hidden touch-none bg-bg">
            <img id="qris-viewer-img" src="" alt="QRIS kelas"
                class="absolute left-1/2 top-1/2 max-h-none max-w-none origin-center select-none"
                style="transform: translate(-50%, -50%) scale(1)" draggable="false" />
        </div>

        <p class="shrink-0 border-t border-line px-5 py-3 text-center text-[12px] text-muted">
            Cubit atau scroll untuk zoom, geser untuk memindahkan, tombol unduh untuk menyimpan gambar.
        </p>
    </div>
</div>


@push('scripts')
<script>
    (function () {
        // Viewer memakai transform (translate + scale) saja — tidak menyentuh
        // top/left/width/height supaya tetap mulus di perangkat lemah.
        var viewer = document.getElementById('qris-viewer');
        var viewerImg = document.getElementById('qris-viewer-img');
        var stage = document.getElementById('qris-stage');
        var zoomLabel = document.getElementById('qris-viewer-zoom');
        var zoomInBtn = document.getElementById('qris-zoom-in');
        var zoomOutBtn = document.getElementById('qris-zoom-out');
        var zoomResetBtn = document.getElementById('qris-zoom-reset');
        var downloadBtn = document.getElementById('qris-download');
        var viewerCloseBtn = document.getElementById('qris-viewer-close');

        // tombol "Simpan" di kartu halaman Grup (tanpa harus buka viewer dulu)
        var cardDownloadBtn = document.getElementById('qris-download-card');

        // Kartu QRIS adalah satu-satunya sumber URL gambar di halaman ini —
        // tidak ada lagi pratinjau <img> di kartu.
        var qrisCard = document.getElementById('qris-card');

        // pemicu viewer: hanya tombol "Lihat"
        var openBtn = document.getElementById('qris-open');

        if (!viewer || !viewerImg || !stage) return;

        var MIN_SCALE = 1;
        var MAX_SCALE = 6;
        var scale = 1;
        var offsetX = 0;
        var offsetY = 0;
        var lastFocus = null;
        var fitScale = null;

        function currentSrc() {
            return (qrisCard && qrisCard.dataset.qrisUrl) || '';
        }

        /**
         * Hitung skala "pas modal" untuk pertama kali dibuka: gambar
         * diperbesar sampai muat di dalam panggung (tanpa melebihi MAX_SCALE),
         * jadi tidak tampil kecil di tengah bidang luas.
         * Hasilnya dipakai sebagai skala minimum sekaligus skala 100% label.
         */
        function computeFitScale() {
            var natW = viewerImg.naturalWidth;
            var natH = viewerImg.naturalHeight;
            var boxW = stage.clientWidth;
            var boxH = stage.clientHeight;

            if (!natW || !natH || !boxW || !boxH) return 1;

            var fit = Math.min(boxW / natW, boxH / natH);
            return Math.min(Math.max(fit, 0.05), MAX_SCALE);
        }

        function applyTransform() {
            viewerImg.style.transform =
                'translate(calc(-50% + ' + offsetX + 'px), calc(-50% + ' + offsetY + 'px)) scale(' + scale + ')';

            var min = fitScale || MIN_SCALE;
            zoomLabel.textContent = Math.round((scale / min) * 100) + '%';

            var atMin = scale <= min + 0.001;
            zoomOutBtn.disabled = atMin;
            zoomInBtn.disabled = scale >= MAX_SCALE - 0.001;
            stage.classList.toggle('cursor-grab', !atMin);
            stage.classList.toggle('cursor-default', atMin);
        }

        function resetView() {
            scale = fitScale || MIN_SCALE;
            offsetX = 0;
            offsetY = 0;
            applyTransform();
        }

        function openViewer() {
            var src = currentSrc();
            if (!src) return;

            lastFocus = document.activeElement;
            fitScale = null;
            viewerImg.src = src;
            scale = MIN_SCALE;
            offsetX = 0;
            offsetY = 0;
            applyTransform();

            viewer.classList.remove('hidden');
            viewer.classList.add('flex');
            document.body.style.overflow = 'hidden';

            // ukuran asli gambar baru diketahui setelah ter-decode; setelah itu
            // baru bisa dihitung skala "pas modal" dan diterapkan.
            if (viewerImg.complete) {
                fitScale = computeFitScale();
                resetView();
            } else {
                viewerImg.onload = function () {
                    fitScale = computeFitScale();
                    resetView();
                };
            }

            viewerCloseBtn.focus();
        }

        function closeViewer() {
            viewer.classList.add('hidden');
            viewer.classList.remove('flex');
            document.body.style.overflow = '';
            if (lastFocus && lastFocus.focus) lastFocus.focus();
        }

        function zoomAt(nextScale, originX, originY) {
            var min = fitScale || MIN_SCALE;
            nextScale = Math.min(MAX_SCALE, Math.max(min, nextScale));
            if (nextScale === scale) return;

            if (typeof originX === 'number' && typeof originY === 'number') {
                // jaga titik di bawah kursor / titik cubit tetap di tempatnya
                var rect = stage.getBoundingClientRect();
                var cx = originX - rect.left - rect.width / 2;
                var cy = originY - rect.top - rect.height / 2;
                var ratio = nextScale / scale;
                offsetX = cx - (cx - offsetX) * ratio;
                offsetY = cy - (cy - offsetY) * ratio;
            }

            scale = nextScale;
            if (scale === min) { offsetX = 0; offsetY = 0; }
            applyTransform();
        }

        function downloadQris() {
            var src = currentSrc();
            if (!src) return;

            var name = src.split('/').pop().split('?')[0] || 'qris-kelas.png';
            var link = document.createElement('a');
            link.href = src;
            link.download = name;
            link.rel = 'noopener';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        if (openBtn) openBtn.addEventListener('click', openViewer);

        viewerCloseBtn.addEventListener('click', closeViewer);

        // klik area gelap di luar kartu modal = tutup (perilaku modal standar)
        viewer.addEventListener('click', function (e) {
            if (e.target === viewer) closeViewer();
        });
        zoomResetBtn.addEventListener('click', resetView);
        zoomInBtn.addEventListener('click', function () { zoomAt(scale * 1.4); });
        zoomOutBtn.addEventListener('click', function () { zoomAt(scale / 1.4); });

        stage.addEventListener('wheel', function (e) {
            e.preventDefault();
            zoomAt(scale * (e.deltaY < 0 ? 1.12 : 1 / 1.12), e.clientX, e.clientY);
        }, { passive: false });

        // pointer events menangani mouse, stylus, dan cubit dua jari sekaligus
        var pointers = {};
        var pinchStart = null;
        var panStart = null;

        function pointerList() {
            return Object.keys(pointers).map(function (id) { return pointers[id]; });
        }

        stage.addEventListener('pointerdown', function (e) {
            stage.setPointerCapture(e.pointerId);
            pointers[e.pointerId] = { x: e.clientX, y: e.clientY };

            if (pointerList().length === 1) {
                panStart = { x: e.clientX, y: e.clientY, offsetX: offsetX, offsetY: offsetY };
                stage.classList.add('cursor-grabbing');
            } else if (pointerList().length === 2) {
                var pts = pointerList();
                pinchStart = {
                    distance: Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y),
                    scale: scale,
                    centerX: (pts[0].x + pts[1].x) / 2,
                    centerY: (pts[0].y + pts[1].y) / 2,
                };
                panStart = null;
            }
        });

        stage.addEventListener('pointermove', function (e) {
            if (!pointers[e.pointerId]) return;
            pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
            var pts = pointerList();

            if (pts.length === 2 && pinchStart) {
                var distance = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y);
                if (pinchStart.distance > 0) {
                    zoomAt(pinchStart.scale * (distance / pinchStart.distance), pinchStart.centerX, pinchStart.centerY);
                }
                return;
            }

            if (pts.length === 1 && panStart) {
                offsetX = panStart.offsetX + (e.clientX - panStart.x);
                offsetY = panStart.offsetY + (e.clientY - panStart.y);
                applyTransform();
            }
        });

        function endPointer(e) {
            delete pointers[e.pointerId];
            var remaining = pointerList();

            if (remaining.length === 1) {
                // sisa satu jari setelah cubit -> lanjutkan sebagai geser
                panStart = { x: remaining[0].x, y: remaining[0].y, offsetX: offsetX, offsetY: offsetY };
                pinchStart = null;
            } else if (remaining.length === 0) {
                pinchStart = null;
                panStart = null;
                stage.classList.remove('cursor-grabbing');
            }
        }

        stage.addEventListener('pointerup', endPointer);
        stage.addEventListener('pointercancel', endPointer);

        downloadBtn.addEventListener('click', downloadQris);
        if (cardDownloadBtn) cardDownloadBtn.addEventListener('click', downloadQris);

        document.addEventListener('keydown', function (e) {
            if (viewer.classList.contains('hidden')) return;

            if (e.key === 'Escape') closeViewer();
            else if (e.key === '+' || e.key === '=') zoomAt(scale * 1.4);
            else if (e.key === '-') zoomAt(scale / 1.4);
            else if (e.key === '0') resetView();
        });

        // Setelah modal Detail Grup menyimpan QRIS baru, kartu ini ikut segar
        // (teks status + tombol Lihat/Simpan muncul) — tanpa reload.
        window.addEventListener('qris:updated', function (e) {
            var url = e.detail && e.detail.url;
            if (!url || !qrisCard) return;

            qrisCard.dataset.qrisUrl = url;

            var status = document.getElementById('qris-card-status');
            var actions = document.getElementById('qris-card-actions');

            if (status) status.textContent = 'Gambar QRIS kelas sudah tersedia.';
            if (actions) actions.classList.remove('hidden');
        });

        applyTransform();
    })();
</script>
@endpush
