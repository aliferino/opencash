<div align="center">

# 💵 OpenCash

**Aplikasi pengelolaan kas kelas — transparan, gampang dipakai, tanpa nyatet manual di buku.**

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/status-in%20development-yellow)

</div>

---

## Kenapa OpenCash?

Kas kelas biasanya dicatat manual di buku kecil — gampang hilang, gampang lupa siapa yang belum bayar, dan gak ada bukti transparan ke seluruh anggota kelas. **OpenCash** menggantikan itu semua dengan sistem web sederhana: siswa bisa bayar kas lewat QRIS atau tunai, bendahara tinggal verifikasi, dan semua orang di kelas bisa lihat laporan pemasukan-pengeluaran secara real-time.

## ✨ Fitur Utama

- 🏫 **Multi-kelas** — satu aplikasi bisa menaungi banyak kelas/kelompok sekaligus, masing-masing dengan kas terpisah.
- 🔑 **Gabung kelas pakai kode undangan** — siswa cukup masukkan kode dari bendahara/guru, tidak perlu didaftarkan manual satu-satu.
- 💳 **Dua jalur pembayaran** — tunai (dicatat langsung oleh bendahara) atau QRIS mandiri (siswa upload bukti transfer, bendahara tinggal verifikasi).
- 🚫 **Anti bayar dobel** — sistem otomatis menolak kalau tagihan yang sama sudah lunas atau masih menunggu verifikasi.
- 📅 **Jadwal tagihan fleksibel** — bendahara bisa atur nominal kas & denda per periode (mingguan, bulanan, dst).
- 🧾 **Riwayat pengeluaran + bukti nota** — setiap pengeluaran kelas wajib disertai foto struk, transparan buat semua anggota.
- 🔔 **Notifikasi otomatis** — bendahara diberi tahu saat ada pembayaran baru, siswa diberi tahu saat pembayarannya diverifikasi.
- 📊 **Laporan saldo real-time** — saldo kas, riwayat per siswa, dan laporan kelas bisa dilihat kapan saja.

## 👥 Peran Pengguna

| Peran | Cakupan | Bisa ngapain aja |
|---|---|---|
| **Admin** | Seluruh platform (lintas kelas) | Membuat/menghapus kelas, menambahkan akun bendahara ke sebuah kelas, memantau seluruh user & riwayat perubahan data |
| **Bendahara** | Satu kelas | Kelola siswa, atur jadwal & nominal kas, verifikasi pembayaran, catat pengeluaran, lihat laporan |
| **Siswa** | Satu kelas | Lihat tagihan, bayar tunai/QRIS, lihat riwayat pembayaran sendiri |

Detail lengkap alur & aturan bisnisnya ada di [`CLAUDE.md`](./CLAUDE.md). Panduan pakai untuk pengguna awam ada di **buku manual** terpisah (lihat bagian bawah).

## 🛠️ Tech Stack

- **Backend:** Laravel, PHP 8.3+
- **Database:** MySQL/PostgreSQL (via Eloquent ORM)
- **Frontend:** Blade + Tailwind CSS, fetch API untuk interaksi tanpa reload
- **Queue:** Database driver
- **Cache:** File-based
- **Storage:** Local disk (dev) — direncanakan pindah ke Cloudinary untuk production

## 🚀 Instalasi Lokal

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

# 4. Sesuaikan koneksi database di .env, lalu migrasi + seed
php artisan migrate --seed

# 5. Build asset & jalankan server
npm run build
php artisan serve
```

Setelah seeding, akun admin default tersedia untuk login pertama kali:

```
Email    : admin@opencash.test
Password : password
```

> ⚠️ **Ganti password ini sebelum deploy ke production.**

## 📂 Struktur Peran di Kode

```
app/Http/Controllers/
├── Admin/        → Fitur khusus admin (global, lintas kelas)
├── Treasurer/     → Fitur khusus bendahara (per kelas)
├── Student/      → Fitur khusus siswa (per kelas)
└── ...           → Controller bersama (notifikasi, onboarding, auth)
```

## 🗺️ Status Pengembangan

- [x] Skema database & model
- [x] Business logic & controller (Admin/Treasurer/Student)
- [x] Routing lengkap dengan proteksi role
- [ ] Tampilan (Blade views) — sedang dikerjakan
- [ ] Export laporan ke PDF/Excel
- [ ] Migrasi storage ke Cloudinary untuk production

## 📖 Dokumentasi Lain

- [`CLAUDE.md`](./CLAUDE.md) — konteks logika bisnis & konvensi kode untuk kontributor/AI assistant.
- **Buku Panduan Pengguna** (`.docx`) — panduan pakai aplikasi untuk admin, bendahara, dan siswa, ditulis untuk pengguna awam non-teknis.

---

<div align="center">
Dibuat untuk memudahkan kas kelas — tanpa buku catatan, tanpa drama "siapa yang belum bayar".
</div>