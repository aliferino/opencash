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