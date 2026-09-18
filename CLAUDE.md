# OpenCash — Konteks Project

Aplikasi web Laravel untuk mengelola kas kelas (sekolah). Dokumen ini adalah
sumber kebenaran untuk logika bisnis & alur kerja — baca ini dulu sebelum
mengerjakan apa pun di repo ini, jangan asumsi dari nama file/kode saja.

Kode sengaja MINIM KOMENTAR (logika bisnis didokumentasikan di sini, bukan
di inline comment). Ikuti gaya ini saat menambah/mengubah kode: tidak perlu
docblock panjang di tiap method, cukup nama method/variable yang jelas.

## 1. Peran (Roles)

Ada 3 role, disimpan di `users.role` (enum: `admin`, `treasurer`, `student`,
nullable — user baru daftar belum punya role).

### Admin — GLOBAL, lintas kelas
- `group_id` SELALU `null`. Tidak terikat satu kelas manapun.
- Tidak bisa didapat lewat registrasi publik — akun admin di-seed manual
  (lihat `database/seeders/DatabaseSeeder.php`, kredensial default:
  `admin@opencash.test` / `password`).
- Wewenang: CRUD semua kelas (`Group`), lihat semua user & audit lintas
  kelas, tambah 1-2 akun bendahara langsung ke sebuah kelas setelah kelas
  itu dibuat.
- TIDAK mengurus operasional harian satu kelas (itu tugas bendahara).

### Treasurer (Bendahara) — per kelas, "operator" kelas
- Terikat satu `group_id`. Satu kelas bisa punya lebih dari satu bendahara.
- Wewenang: kelola anggota kelasnya (tambah manual / ubah data / keluarkan
  / ubah role student ↔ treasurer) lewat halaman **Grup**, ubah nama
  kelasnya sendiri, refresh & salin kode undangan, atur jadwal tagihan
  (`CashSchedule`), atur nominal kas & denda per periode (`GroupSetting`),
  upload gambar QRIS, catat pembayaran tunai, verifikasi pembayaran QRIS,
  catat pengeluaran, lihat laporan.
- Halaman **Grup** (`Treasurer\GroupController`, view
  `resources/views/treasurer/groups/`) sengaja menggabungkan "identitas
  kelas" + "kelola anggota" dalam satu halaman — dulu dipisah sebagai
  halaman Siswa, diganti nama jadi Grup karena konsepnya bendahara
  mengelola KEANGGOTAAN kelas, bukan daftar siswa saja.
- Tata letak halaman Grup: grid konten adalah bintang utamanya — 3 kartu
  statistik (Total Anggota / Siswa / Bendahara) + tabel Anggota Grup.
  Form "Informasi Grup" (nama + kode undangan) SENGAJA ditaruh di dalam
  MODAL (`#group-detail-modal`), bukan sebagai kartu di halaman, supaya
  grid & tabel tetap fokus dan tidak terbelah dua kolom. Pemicunya tombol
  **"Detail Grup"** di header kanan atas halaman (sejajar tombol export di
  halaman Laporan). Karena nama grup jadi tersembunyi di modal, nama
  ditampilkan ulang di subjudul header (`#group-name-label`) supaya tetap
  kelihatan, dan label itu ikut ter-update setiap kali nama disimpan.
- HANYA bisa mengubah nama & refresh kode undangan kelasnya sendiri —
  TIDAK bisa membuat atau menghapus kelas (itu wewenang admin). Karena itu
  halaman Grup bendahara TIDAK punya tombol hapus/buat grup (beda dengan
  `admin/groups/_detail` yang punya "Zona Berbahaya").
- Tidak bisa mengubah role / mengeluarkan akun sendiri (cegah lockout).

### Student (Siswa)
- Terikat satu `group_id`, `role` selalu `student`.
- Masuk kelas dengan salah satu dari 2 cara:
  1. Memasukkan kode undangan di halaman onboarding (`/onboarding`).
  2. Ditambahkan manual oleh bendahara (`Treasurer\GroupController::storeMember`).
- Wewenang: lihat tagihan kelasnya, bayar tunai (diinput bendahara) atau
  QRIS mandiri (upload bukti sendiri, lihat §3).

## 2. Alur Onboarding

- User baru daftar (`Auth\RegisterController`) → `role` & `group_id` NULL.
- Setelah login, kalau `group_id` masih null → diarahkan ke `/onboarding`
  (halaman tunggu, cuma ada form input kode undangan — TIDAK ADA tombol
  "buat kelas").
- Submit kode undangan (`OnboardingController::join`) → dicari `Group`
  dengan `invite_code` itu → kalau ketemu, user langsung di-set
  `group_id` = kelas itu, `role` = `student` (SELALU jadi siswa, siapa pun
  yang membagikan kodenya — admin atau bendahara).
- **Kode undangan TIDAK sekali pakai** — sengaja tidak di-null-kan setelah
  dipakai, karena satu kode dipakai berulang oleh banyak siswa. Hanya
  di-refresh manual kalau bendahara/admin curiga kode bocor.
- Admin membuat kelas baru → `Admin\GroupController::store` → generate
  kode undangan otomatis → admin lanjut `addTreasurer()` untuk menambahkan
  1-2 akun bendahara ke kelas itu.

## 3. Alur Pembayaran Kas

Dua jalur pembayaran untuk satu `CashSchedule` (tagihan):

**Jalur A — Tunai**: Siswa serahkan uang fisik ke bendahara di kelas.
Bendahara input langsung lewat `CashIncomeController::storeCash`. Status
langsung `verified` (uang sudah dipegang bendahara), `proof_image` null.

**Jalur B — QRIS mandiri**: Siswa scan QRIS kelas (gambar diambil dari
`GroupSetting.qris_image` periode terbaru), transfer, lalu upload bukti
lewat `CashIncomeController::storeQris`. Status masuk sebagai `pending`
sampai bendahara mengecek mutasi rekening secara manual dan memverifikasi
(`CashIncomeController::verify`, ubah ke `verified`/`rejected`).

**Aturan penting**: satu siswa TIDAK BOLEH punya lebih dari satu
`CashIncome` berstatus `verified`/`pending` untuk `cash_schedule_id` yang
sama (dicegah lewat `abortIfAlreadyPaid()` di kedua method `storeCash` &
`storeQris`) — mencegah tagihan yang sama dibayar dobel.

Notifikasi: submit QRIS → semua bendahara kelas dapat notifikasi
(`CashIncomeSubmitted`). Verifikasi berhasil → siswa dapat notifikasi
(`CashIncomeVerified`).

## 4. Struktur Data Kunci

- `groups` — satu baris = satu kelas. Kolom: `name`, `invite_code`. TIDAK
  ADA `qris_image` di sini (lihat catatan migrasi di bawah).
- `periods` — referensi global (bukan per-kelas), mis. "Mingguan",
  "Bulanan". Dikelola admin (`Admin\PeriodController`), dipakai semua kelas.
- `group_settings` — pengaturan kas PER KELAS PER PERIODE: `cash_amount`,
  `fine_amount`, DAN `qris_image`. Satu form pengaturan bendahara nulis ke
  satu tabel ini — makanya `qris_image` sengaja di sini, bukan di `groups`
  (migrasi `move_qris_image_column_to_group_settings_table` sudah
  menjalankan perpindahan ini).
- `cash_schedules` — daftar tagihan per kelas (`due_date`, `description`,
  `amount`).
- `cash_incomes` — riwayat pembayaran siswa. `payment_method`: `cash`|
  `qris`. `status`: `pending`|`verified`|`rejected`.
- `cash_expenses` — pengeluaran kas, wajib ada `proof_image` (foto nota).
- `user_audits` — log otomatis tiap `users` diupdate (role/nama/email
  berubah), dibuat oleh `UserObserver` — read-only, jangan pernah bikin
  create/update/destroy manual untuk tabel ini.

## 5. Konvensi Controller & Routing

- Struktur folder controller per-role: `Admin/`, `Treasurer/`, `Student/`.
  Controller yang levelnya "shared" (dipakai admin+treasurer+student atau
  cuma treasurer+student) taruh di namespace milik yang paling banyak
  action-nya, lalu expose route index-nya lewat middleware role gabungan
  di `routes/web.php` (lihat grup `role:treasurer,student` di paling
  bawah file itu).
- Semua controller resource WAJIB scope query ke `group_id` milik user
  yang login (`$request->user()->group_id`), KECUALI controller di bawah
  namespace `Admin\` yang memang global (admin tidak punya `group_id`).
- Pola otorisasi kepemilikan: method privat `authorizeOwnership()` yang
  `abort_unless(...403)` kalau record bukan milik grup user. Ikuti pola
  ini untuk controller baru.
- Route model binding: SELALU beri nama parameter route yang sama persis
  dengan nama variable di type-hint controller (mis. `{groupSetting}`,
  bukan `{group_setting}`), supaya tidak kena masalah Laravel yang
  snake_case-in nama parameter dari URL segment yang mengandung tanda "-".
- Dashboard (`*/DashboardController::index`) & halaman siswa
  (`Student\PaymentController::index`) me-render Blade view langsung
  (server-rendered, data disiapkan di controller) untuk first paint cepat.
  Endpoint CRUD lain (Group, User, CashSchedule, dst) tetap JSON API biasa,
  dipanggil async (fetch) dari view supaya interaksi (verifikasi bayar,
  ubah role, dst) tidak perlu reload halaman.

## 6. Yang Belum Selesai / Sengaja Di-stub

- **View Blade**: view treasurer sudah lengkap (dashboard, pengaturan kas,
  jadwal tagihan, **grup**, pemasukan, pengeluaran, **laporan**). Yang masih
  kosong: view student (`student/bills`, `student/history`,
  `student/expenses` — folder ini di-scaffold tapi belum dipakai, cek
  `routes/web.php` untuk route student yang benar) dan
  `treasurer/reports/pdf.blade.php` (untuk export PDF nanti).
- **Export PDF/Excel** (`ReportController::exportPdf/exportExcel`): sengaja
  return HTTP 501, nunggu `barryvdh/laravel-dompdf` &
  `maatwebsite/excel` diinstall. UI harus visually disable tombol export
  sampai ini aktif.
- **Laporan**: sengaja SATU halaman (`/treasurer/reports`) tanpa sub-tab —
  4 kartu ringkasan + 3 panel grid (partisipasi pembayaran, siswa teratas,
  aktivitas terbaru) + satu tabel "Rincian Arus Kas" yang menggabungkan
  pemasukan & pengeluaran (difilter lewat dropdown, bukan tab). Endpoint
  JSON pendukung: `reports/incomes` & `reports/expenses` (keduanya
  paginated + search). Penting: rincian HANYA menampilkan pemasukan
  `verified` supaya totalnya konsisten dengan kartu "Total Pemasukan" —
  pemasukan `pending` sengaja tidak masuk saldo.
- **Storage QRIS/proof image**: pakai `Storage::disk('public')`. Rencana
  pindah ke Cloudinary lewat `.env` kalau deploy ke platform dengan
  ephemeral filesystem (Render/Railway — BUKAN Vercel, Vercel tidak cocok
  untuk Laravel karena tidak ada queue worker persisten & local storage).
- **Notifikasi**: polling based (fetch berkala ke `NotificationController`),
  belum ada WebSocket/real-time push.

## 7. Kesalahan yang Sudah Pernah Terjadi (jangan diulang)

- Jangan taruh `qris_image` di model `Group` — itu kolom `GroupSetting`.
- Semua controller yang `extends Controller` WAJIB
  `use App\Http\Controllers\Controller;` di namespace bertingkat
  (`Admin\`, `Treasurer\`, `Student\`) — pernah kelewat di beberapa file
  dan bikin fatal error "Class not found".
- Jangan biarkan `Admin\GroupController` (atau controller admin lain)
  ke-scope ke satu `group_id` — admin itu global, scoping seperti itu
  salah arsitektur untuk role ini.
- Halaman bendahara TIDAK boleh punya aksi buat/hapus grup. Bendahara cuma
  mengelola grup yang sudah ada (nama, kode undangan, anggota).
- Saat menguji lewat HTTP, login dulu baru akses halaman panel —
  `layouts/partials/_sidebar.blade.php` memanggil `auth()->user()->isAdmin()`
  dan error "Call to a member function isAdmin() on null" kalau belum login.
- Jangan menulis data uji ke database dev secara destruktif (mis. mengubah
  nama grup asli saat uji). Bungkus dalam transaksi + rollback, atau
  simpan nilai lama dan pulihkan setelah selesai.

## 8. Aturan Frontend & Desain (WAJIB dibaca sebelum menyentuh UI)

Catatan: folder `.agents/` dan `.claude/` berisi skill pihak ketiga (taste
skill, dll) yang di-`.gitignore` — itu alat bantu lokal, BUKAN bagian dari
repo. Aturan di bawah adalah hasil rangkuman yang berlaku untuk project ini,
jadi jangan bergantung pada isi folder itu.

### 8.1 Stack & sumber kebenaran style

- Stack UI: **Blade + Tailwind v4** (`resources/css/app.css` pakai `@theme`,
  tanpa file config JS) + **CSS custom di `@push('head')`** untuk komponen
  yang butuh state/animation kompleks (contoh: `auth/index.blade.php`).
- Token warna/font ada di blok `@theme` di `resources/css/app.css`
  (`--color-bg`, `--color-surface`, `--color-ink`, `--color-muted`,
  `--color-accent`, `--color-accent-bright`, `--color-line`). Pakai utility
  hasil token itu (`bg-surface`, `text-muted`, `border-line`) — JANGAN
  hardcode hex baru di markup.
- Tema: dark navy (off-black `#070b18`, bukan `#000`), satu accent biru,
  surface `#101935`. Ini palette terkunci — jangan tambah accent warna lain.
- Font: Instrument Sans (dimuat lewat `bunny()` di `vite.config.js`).
  Jangan tambah font baru tanpa alasan kuat.
- Icon: pakai **SVG inline stroke 1.5** (`stroke-width="1.5"`,
  `stroke-linecap="round"`). Library `lucide` tersedia lewat
  `window.lucideRefresh()`. Jangan pakai emoji sebagai ikon.

### 8.2 Aturan yang dilarang (anti-slop)

- Emoji di kode, markup, teks, atau alt text.
- Gradien ungu/biru "AI aesthetic", glow neon, glassmorphism tanpa fungsi.
- Tiga kartu fitur sejajar sebagai satu-satunya pola layout; hero center
  simetris untuk halaman marketing.
- `h-screen` untuk section full-height — selalu `min-h-[100dvh]` (bug
  viewport iOS Safari).
- Shadow hitam pekat (`shadow-lg` default). Kalau perlu elevasi, tint
  shadow ke warna background (mis. `rgba(62, 123, 255, 0.42)` di halaman
  auth).
- Animasi yang mengubah `top`/`left`/`width`/`height`. Animasikan
  **`transform` dan `opacity`** saja.
- `backdrop-filter` di container yang ikut scroll (pemicu repaint berat di
  mobile) — hanya untuk elemen fixed/sticky/overlay.
- Menampilkan konten mock yang tidak nyambung dengan halaman (mis. kartu
  "Saldo kas kelas Rp1.284.500" di halaman login). Kalau butuh pengisi
  panel, pakai poin fitur yang benar-benar menggambarkan alur aplikasi.

### 8.3 Motion & transisi

- Durasi: 200–350ms untuk micro-interaction, 600–800ms untuk perpindahan
  panel besar.
- Easing: `cubic-bezier(0.22, 1, 0.36, 1)` untuk masuk (decelerate),
  `cubic-bezier(0.76, 0, 0.24, 1)` untuk slide panel penuh.
- Setiap elemen interaktif wajib punya state `hover`, `focus-visible`,
  `active`, dan `disabled`/loading. Tombol submit form pakai spinner
  (`data-loading="true"`), bukan diam saja.
- Animasi masuk yang perlu replay tiap pergantian state: taruh di class
  pemicu (contoh `.is-entering`) lalu restart lewat JS
  (`classList.remove` → baca `offsetWidth` → `classList.add`), jangan
  andalkan animasi sekali-jalan di page load.
- Selalu hormati `@media (prefers-reduced-motion: reduce)`.

### 8.4 Aksesibilitas (non-negotiable)

- Form: setiap `input` punya `<label for>`, `autocomplete` yang tepat, dan
  pesan error inline (`role="alert"`).
- Elemen yang disembunyikan secara visual tapi masih ada di DOM harus
  `pointer-events: none` + `aria-hidden="true"` + `inert` supaya tidak
  bisa di-tab.
- Ikon dekoratif: `aria-hidden="true"`. Tombol ikon: `aria-label`.
- Kontras teks minimal 4.5:1. Teks muted di atas dark navy jangan lebih
  gelap dari `#92a0c0`.

### 8.5 Konvensi struktur view

- View per-role: `resources/views/{admin,treasurer,student}/…` mengikuti
  struktur controller. Halaman publik di `resources/views/web/`.
- Halaman auth pakai `layouts.app` (tanpa navbar/sidebar); halaman publik
  pakai `layouts.web`; halaman panel pakai `layouts.panel`.
- CSS spesifik satu halaman ditulis di `@push('head')`, JS di
  `@push('scripts')` — jangan taruh di `resources/css/app.css` atau
  `resources/js/app.js` kecuali dipakai lintas halaman.
- Kelas util Tailwind boleh dipakai campur dengan kelas komponen
  (`auth-field`, `overlay-points`, dst). Untuk komponen dengan banyak state,
  prefer kelas komponen + CSS di `@push('head')` supaya markup tetap
  terbaca.
- Setelah mengubah Blade/CSS, jalankan `php artisan view:clear` dan
  verifikasi dengan `curl` (cek markup hasil render), bukan hanya dari
  kode sumber.

### 8.6 Checklist sebelum menyelesaikan pekerjaan UI

- [ ] Tidak ada emoji, tidak ada warna/font baru di luar token `@theme`
- [ ] Semua state interaktif (hover/focus/active/loading/disabled) ada
- [ ] Hanya `transform`/`opacity` yang dianimasikan
- [ ] Responsif: desktop, tablet (≤900px), mobile (≤767px) dicek
- [ ] `prefers-reduced-motion` dihormati
- [ ] Label, `aria-*`, dan urutan tab benar
- [ ] Tidak ada konten mock yang tidak nyambung dengan halaman
- [ ] `php artisan view:clear` dijalankan dan hasil render diverifikasi