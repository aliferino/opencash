# OpenCash — Panduan Agent

Dokumen ini adalah aturan operasional untuk agent yang bekerja di repo ini.
`CLAUDE.md` adalah **sumber kebenaran logika bisnis** (role, alur onboarding,
alur pembayaran, struktur data, konvensi controller) — baca itu dulu sebelum
mengerjakan apa pun. File ini fokus ke cara kerja + aturan frontend.

## 1. Setup & Verifikasi

```sh
php -v                 # butuh PHP 8.x
composer -V
npm run dev            # atau: npm run build
php artisan serve      # dev server di http://localhost:8000
```

### Menjalankan test

Test butuh database MySQL terpisah (ekstensi `pdo_sqlite` tidak aktif di
Laragon, jadi `phpunit.xml` memakai MySQL):

```sh
mysql -u root -e "CREATE DATABASE IF NOT EXISTS opencash_testing CHARACTER SET utf8mb4;"
php artisan test
```

Test cicilan ada di `tests/Feature/InstallmentTest.php` — jalankan ini kalau
menyentuh logika pembayaran/sisa/saldo.

- Jangan commit `.env`, `node_modules`, `vendor`, `public/build`.
- Folder `.agents/` dan `.claude/` **di-`.gitignore`** — itu skill pihak
  ketiga (taste skill dll) untuk pemakaian lokal. Aturan yang berlaku ada di
  file ini dan `CLAUDE.md`, bukan di folder tersebut.
- Setelah mengubah Blade/CSS: jalankan `php artisan view:clear`, lalu
  verifikasi markup hasil render (mis. `curl http://localhost:8000/login`),
  bukan hanya membaca kode sumber.

## 2. Aturan Kode Umum

- Kode sengaja **minim komentar**; logika bisnis didokumentasikan di
  `CLAUDE.md`. Cukup nama method/variable yang jelas.
- **Nama route & folder = bahasa Inggris; teks yang dilihat user = bahasa
  Indonesia.** Contoh: route `student.cash.index`, folder `student/cash/`,
  judul halaman "Kas Kelas".
- **Halaman utama sebuah folder view = `index.blade.php`**, walau method
  controllernya `edit()`/`show()` (mis. `profile/index.blade.php`).
  Partial pakai prefix `_` (`_table`, `_modal`, `_import`).
- Struktur controller per-role: `Admin/`, `Treasurer/`, `Student/`. Setiap
  controller WAJIB `use App\Http\Controllers\Controller;` di namespace
  bertingkat.
- Controller resource WAJIB scope query ke `group_id` user login, KECUALI
  controller `Admin\` (admin global, `group_id` selalu null).
- **Jangan bagi satu route antara bendahara & siswa kalau halaman itu punya
  aksi tulis.** Siswa read-only: kalau butuh data yang sama, buat
  controller/endpoint terpisah di namespace `Student\`, jangan longgarkan
  middleware role.
- Endpoint CRUD = JSON API (dipanggil async dari view). Dashboard & halaman
  siswa di-render Blade langsung untuk first paint cepat.
- Jangan tulis data uji destruktif ke database dev — bungkus transaksi +
  rollback, atau simpan & pulihkan nilai lama.
- `user_audits` read-only (diisi `UserObserver`), jangan create/update manual.

## 2b. Aturan Kas & Cicilan (penting)

- **Tagihan boleh dicicil** — satu siswa bisa punya banyak `cash_incomes`
  untuk `cash_schedule_id` yang sama. Jangan asumsikan "satu tagihan = satu
  baris pembayaran". Jangan hidupkan lagi `abortIfAlreadyPaid()`.
- **Semua hitungan terbayar/sisa/saldo WAJIB lewat `App\Support\CashLedger`**
  (`billSummary`, `billSummaries`, `scheduleProgress`, `remainingFor`,
  `balance`, `rupiah`). Jangan hitung manual di controller/view — angka
  siswa & bendahara harus selalu sama.
- Batas pembayaran = sisa tagihan, dihitung dari `verified + pending`
  (pending ikut supaya tidak bayar dobel saat bukti QRIS belum diverifikasi).
  Melampaui sisa → HTTP 422.
- Saldo kas boleh minus; tampilkan dengan `CashLedger::rupiah()`, bukan
  `number_format()` polos.
- Nominal kas per tagihan = `cash_schedules.amount`. TIDAK ADA pengaturan
  nominal per periode (`periods` & `group_settings` sudah dihapus).
- Detail lengkap: `CLAUDE.md` §3.

## 2c. Import Excel (bulk add)

- **Validasi dulu SEMUA baris, baru simpan.** Kalau ada satu baris salah,
  tidak ada yang disimpan, dan error dilaporkan per baris
  ("Baris 4: ..."). Ini supaya bendahara tidak menebak data mana yang masuk.
- Cek bentrok ANTAR baris di berkas (mis. dua baris menabrak tagihan yang
  sama sampai totalnya melebihi nominal) — jangan cuma cek lawan data di DB.
- Import pemasukan = tunai + langsung `verified` (bendahara sudah pegang
  uangnya), sama seperti "Catat Tunai" manual.
- Import pengeluaran tidak punya foto nota (`proof_image` null) — nota tetap
  wajib kalau dicatat manual.
- Kelas import (`App\Imports\*`) hanya MEMBACA & merapikan baris; validasi
  dan penyimpanan ada di controller import.
- Template contoh ada di `docs/import-templates/` dan tombol "Unduh
  template" di modal import.

## 3. Aturan Frontend & Desain

Ringkasan dari `CLAUDE.md` §8 — detail lengkap ada di sana.

### Stack & style
- Blade + **Tailwind v4** (`@theme` di `resources/css/app.css`, tanpa config
  JS). Token: `--color-bg`, `--color-surface`, `--color-ink`, `--color-muted`,
  `--color-accent`, `--color-accent-bright`, `--color-line`.
- Pakai utility token (`bg-surface`, `text-muted`, `border-line`). Jangan
  hardcode hex baru di markup.
- Palette terkunci: dark navy off-black (`#070b18`), surface `#101935`, satu
  accent biru (`#3e7bff` / `#86b4ff`). Jangan tambah accent warna lain.
- Font: Instrument Sans (`bunny()` di `vite.config.js`).
- Icon: SVG inline, `stroke-width="1.5"`, `stroke-linecap="round"`,
  `aria-hidden="true"`. `lucide` tersedia via `window.lucideRefresh()`.
  Emoji dilarang.

### Dilarang (anti-slop)
- Emoji di kode/markup/teks/alt text.
- Gradien ungu-biru "AI aesthetic", glow neon, glassmorphism tanpa fungsi.
- `h-screen` untuk section full-height → pakai `min-h-[100dvh]`.
- Shadow hitam pekat default; tint shadow ke warna background.
- Animasi yang mengubah `top`/`left`/`width`/`height` → hanya `transform`
  dan `opacity`.
- `backdrop-filter` pada container yang ikut scroll (hanya untuk
  fixed/sticky/overlay).
- Konten mock yang tidak nyambung dengan halaman (mis. kartu saldo kas di
  halaman login). Isi panel dengan poin fitur yang menggambarkan alur nyata.

### Motion
- Durasi 200–350ms (micro-interaction), 600–800ms (perpindahan panel besar).
- Easing: `cubic-bezier(0.22, 1, 0.36, 1)` (masuk),
  `cubic-bezier(0.76, 0, 0.24, 1)` (slide panel penuh).
- Wajib ada state `hover`, `focus-visible`, `active`, dan loading/disabled.
  Submit form pakai spinner (`data-loading="true"`).
- Animasi yang harus replay tiap pergantian state: taruh di class pemicu
  (mis. `.is-entering`), restart lewat JS
  (`classList.remove` → baca `offsetWidth` → `classList.add`).
- Hormati `@media (prefers-reduced-motion: reduce)`.

### Aksesibilitas
- Setiap `input` punya `<label for>`, `autocomplete` tepat, error inline
  `role="alert"`.
- Elemen tersembunyi-visual: `pointer-events: none` + `aria-hidden="true"` +
  `inert` (tidak boleh bisa di-tab).
- Tombol ikon wajib `aria-label`. Kontras teks minimal 4.5:1.

### Struktur view
- `resources/views/{admin,treasurer,student}/…` mengikuti struktur
  controller. Halaman publik di `resources/views/web/`.
- Layout: auth → `layouts.app`; publik → `layouts.web`; panel →
  `layouts.panel`.
- CSS khusus halaman di `@push('head')`, JS di `@push('scripts')`. Jangan
  taruh di `app.css`/`app.js` kecuali dipakai lintas halaman.

## 4. Checklist Sebelum Selesai

- [ ] Tidak ada emoji; tidak ada warna/font di luar token `@theme`
- [ ] State interaktif lengkap (hover/focus/active/loading/disabled)
- [ ] Hanya `transform`/`opacity` yang dianimasikan
- [ ] Responsif: desktop, tablet (≤900px), mobile (≤767px)
- [ ] `prefers-reduced-motion` dihormati
- [ ] Label, `aria-*`, urutan tab benar
- [ ] Tidak ada konten mock yang tidak nyambung
- [ ] `php artisan view:clear` dijalankan + hasil render diverifikasi
- [ ] Kalau menyentuh kas: `php artisan test` lulus (khususnya
      `InstallmentTest`)
- [ ] Kalau menyentuh akses role: sudah dicoba sebagai siswa DAN bendahara
      (siswa tidak boleh bisa membuka halaman ber-aksi-tulis)
