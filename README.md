<div align="center">

# 💵 OpenCash

**Aplikasi pengelolaan kas kelas — transparan, gampang dipakai, tanpa nyatet manual di buku.**

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-06B6D4?logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![Status](https://img.shields.io/badge/status-beta-blue)

</div>

---

## Kenapa OpenCash?

Kas kelas biasanya dicatat manual di buku kecil — gampang hilang, gampang lupa siapa yang belum bayar, dan gak ada bukti transparan ke seluruh anggota kelas. **OpenCash** menggantikan itu semua dengan sistem web sederhana: siswa bisa bayar kas lewat QRIS atau tunai, bendahara tinggal verifikasi, dan semua orang di kelas bisa lihat laporan pemasukan-pengeluaran secara real-time.

## ✨ Fitur Utama

- 🏫 **Multi-kelas** — satu aplikasi bisa menaungi banyak kelas/kelompok sekaligus, masing-masing dengan kas terpisah.
- 🔑 **Gabung kelas pakai kode undangan** — siswa cukup masukkan kode dari bendahara/guru, tidak perlu didaftarkan manual satu-satu. Kode bisa dipakai berulang oleh banyak siswa.
- 💳 **Dua jalur pembayaran** — tunai (dicatat langsung oleh bendahara, langsung terverifikasi) atau QRIS mandiri (siswa upload bukti transfer, bendahara tinggal verifikasi).
- 🪙 **Tagihan boleh dicicil** — satu tagihan bisa dibayar berkali-kali (tunai maupun QRIS) sampai lunas. Sisa tagihan selalu dihitung dari satu sumber (`CashLedger`), jadi angka di sisi siswa dan bendahara tidak pernah beda.
- 🚫 **Anti bayar dobel** — sistem menolak pembayaran yang melebihi sisa tagihan (422). Pembayaran QRIS yang masih `pending` sudah dihitung sebagai "terpakai" supaya siswa tidak menembak bayar dua kali.
- ✏️ **Koreksi nominal saat verifikasi** — kalau nominal transfer ternyata beda dari yang diisi siswa, bendahara bisa mengoreksinya (termasuk denda) saat memverifikasi.
- 📅 **Jadwal tagihan fleksibel** — bendahara atur deskripsi, jatuh tempo, dan nominal per tagihan (`cash_schedules.amount`). Nominal kas **tidak lagi** diatur per periode.
- 📥 **Import Excel/CSV** — tambah banyak jadwal tagihan sekaligus dari berkas. Validasi dulu semua baris, baru disimpan (all-or-nothing, error dilaporkan per baris).
- 🧾 **Riwayat pengeluaran + bukti nota** — setiap pengeluaran kelas wajib disertai foto nota, transparan buat semua anggota.
- 📊 **Laporan & export** — ringkasan saldo, pemasukan, pengeluaran, partisipasi pembayaran, dan rincian arus kas. Bisa di-export ke **PDF** dan **Excel** (laporan bendahara & riwayat siswa).
- 🔔 **Notifikasi** — bendahara diberi tahu saat ada bukti pembayaran baru, siswa diberi tahu saat pembayarannya diverifikasi (tersimpan di database + endpoint JSON; UI lonceng belum dipasang).
- 🔍 **Audit perubahan data** — setiap perubahan nama/email/role user tercatat otomatis di `user_audits` (read-only).
- 🙋 **Transparansi penuh** — saldo kas kelas, seluruh pengeluaran beserta nota, dan "ke mana uangnya pergi" bisa dilihat semua siswa kapan saja.

## 👥 Peran Pengguna

| Peran | Cakupan | Bisa ngapain aja |
|---|---|---|
| **Admin** | Seluruh platform (lintas kelas) | Membuat/mengubah/menghapus kelas, mengatur & me-refresh kode undangan, menambahkan akun bendahara ke sebuah kelas, mengelola semua akun, memantau audit perubahan data |
| **Bendahara** | Satu kelas | Kelola identitas kelas & anggota, unggah QRIS kelas, atur jadwal tagihan (+ import), catat pembayaran tunai, verifikasi/koreksi bukti QRIS, catat pengeluaran + nota, lihat & export laporan |
| **Siswa** | Satu kelas | Lihat tagihan & sisa per tagihan, bayar QRIS (upload bukti), lihat riwayat pembayaran sendiri (+ export), lihat kas kelas. Read-only selain upload bukti |

Detail lengkap alur & aturan bisnisnya ada di [`CLAUDE.md`](./CLAUDE.md), dan aturan kerja untuk kontributor/AI ada di [`AGENTS.md`](./AGENTS.md).

## 🔄 Alur Singkat

1. **Admin** membuat kelas → kode undangan otomatis dibuat → admin menambahkan 1–2 akun bendahara ke kelas itu.
2. **Bendahara** mengatur jadwal tagihan (manual atau import Excel), mengunggah gambar QRIS kelas, dan membagikan kode undangan.
3. **Siswa** daftar sendiri lalu memasukkan kode undangan di halaman onboarding (selalu masuk sebagai `student`), atau ditambahkan langsung oleh bendahara.
4. **Siswa bayar**: tunai diserahkan ke bendahara (dicatat & langsung `verified`), atau scan QRIS lalu upload bukti (`pending`).
5. **Bendahara** memverifikasi bukti QRIS (bisa mengoreksi nominal/denda) → siswa dapat notifikasi.
6. **Kas tercatat & terbuka** — saldo, pemasukan, dan pengeluaran bisa dilihat semua anggota kelas.

> Saldo kas boleh **minus** kalau pengeluaran melebihi pemasukan terverifikasi. Itu kondisi sah, ditampilkan apa adanya lewat `CashLedger::rupiah()`.

## 🛠️ Tech Stack

- **Backend:** Laravel 13, PHP 8.4+ (`bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `pdo_mysql`, `zip`; tambah `intl` untuk image Docker)
- **Database:** **MySQL 8** (wajib — ada migration yang memakai `DATE_ADD()`/`DATE_SUB()` khas MySQL)
- **Frontend:** Blade + Tailwind CSS v4 (`@theme`, tanpa config JS) + Vite 8, vanilla JS `fetch` untuk interaksi tanpa reload, ikon `lucide`, font Instrument Sans
- **Queue:** Database driver (notifikasi)
- **Cache:** File-based (`CACHE_STORE=file` — tabel `cache` tidak ada di migration project ini)
- **Storage:** Local disk `public` (QRIS, bukti transfer, nota) — rencana pindah ke object storage (R2/S3) untuk produksi
- **Export:** `barryvdh/laravel-dompdf` (PDF) + `maatwebsite/excel` (Excel)
- **Deploy:** Docker multi-stage (FrankenPHP) — lihat [`DEPLOY.md`](./DEPLOY.md)

## 🚀 Instalasi Lokal

Butuh **PHP 8.4+**, **Composer**, **Node.js**, dan **MySQL** yang sudah jalan.

```bash
# 1. Clone repo
git clone https://github.com/aliferino/opencash.git
cd opencash

# 2. Install dependency
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate

# 4. Sesuaikan koneksi database di .env (DB_CONNECTION=mysql, DB_DATABASE=opencash),
#    lalu migrasi + seed
php artisan migrate --seed

# 5. Build asset & jalankan server
npm run build
php artisan serve
```

Ada juga shortcut yang menjalankan server + queue worker + Vite sekaligus:

```bash
composer dev
```

Setelah seeding, akun admin default tersedia untuk login pertama kali:

```
Email    : admin@opencash.test
Password : password
```

> ⚠️ **Ganti password ini sebelum deploy ke production.**

## 🧪 Menjalankan Test

Test memakai **database MySQL terpisah** (`pdo_sqlite` tidak aktif di lingkungan dev Windows/Laragon), jadi buat dulu:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS opencash_testing CHARACTER SET utf8mb4;"
php artisan test
```

Saat ini **54 test / 192 assertion** lulus, mencakup:

- `tests/Feature/InstallmentTest.php` — cicilan, sisa tagihan, saldo minus, akses siswa vs bendahara
- `tests/Feature/TreasurerToolsTest.php` — import jadwal tagihan, export laporan, profil, QRIS kelas
- `tests/Feature/OnboardingTest.php` — alur gabung kelas lewat kode undangan

## 📂 Struktur Project

```
app/Http/Controllers/
├── Admin/        → Fitur khusus admin (global, lintas kelas)
├── Treasurer/    → Fitur khusus bendahara (per kelas)
├── Student/      → Fitur khusus siswa (per kelas)
├── Auth/         → Login & registrasi
└── ...           → Controller bersama (notifikasi, onboarding, profil)

app/Support/CashLedger.php   → SATU sumber perhitungan terbayar/sisa/saldo
app/Imports/SchedulesImport.php
app/Exports/                 → Export PDF/Excel (laporan & riwayat)
app/Notifications/           → Notifikasi database
app/Observers/               → UserObserver (audit), GroupObserver

resources/views/
├── admin/ treasurer/ student/  → Panel sesuai peran
├── web/                        → Halaman publik (Beranda, Tentang, Cara Kerja)
├── auth/ onboarding/ profile/  → Halaman bersama
└── layouts/                    → Layout panel, web, dan auth
```

## 🗺️ Status Pengembangan

- [x] Skema database & model
- [x] Logika bisnis & controller (Admin/Bendahara/Siswa) — termasuk pembayaran **dicicil**
- [x] Routing lengkap dengan proteksi role (70 route)
- [x] Tampilan Blade semua peran + halaman publik & auth (63 view)
- [x] Import Excel/CSV jadwal tagihan (validasi all-or-nothing)
- [x] Export laporan ke PDF & Excel
- [x] Notifikasi tersimpan di database + endpoint JSON
- [x] Audit perubahan data user
- [x] Docker + panduan deploy (`DEPLOY.md`)
- [x] Test otomatis (54 test lulus)
- [ ] UI lonceng notifikasi (polling di panel)
- [ ] Pindah storage produksi ke object storage (Cloudflare R2/S3)
- [ ] Panduan pengguna non-teknis (`.docx`) — belum ada di repo
- [ ] CI/CD (build + smoke test otomatis)

## 📖 Dokumentasi Lain

- [`CLAUDE.md`](./CLAUDE.md) — **sumber kebenaran** logika bisnis, struktur data, dan konvensi kode.
- [`AGENTS.md`](./AGENTS.md) — aturan operasional & aturan frontend untuk kontributor/AI agent.
- [`DEPLOY.md`](./DEPLOY.md) — panduan deploy (Railway / Render / VPS, Docker + FrankenPHP).

## 📝 Catatan Penting

- **Wajib MySQL.** Jangan pakai PostgreSQL/SQLite — ada migration yang spesifik MySQL.
- **`CACHE_STORE=file`.** Jangan diubah ke `database`; tabel `cache` tidak ada di migration project ini.
- **`FILESYSTEM_DISK=local`** menyimpan QRIS/bukti transfer/nota di `storage/app/public`. Di platform dengan filesystem sementara (Render/Railway), pasang volume permanen atau pindahkan ke object storage.
- Jangan hitung terbayar/sisa/saldo manual di controller atau view — selalu lewat `App\Support\CashLedger`.

---

<div align="center">
Dibuat untuk memudahkan kas kelas — tanpa buku catatan, tanpa drama "siapa yang belum bayar".
</div>
