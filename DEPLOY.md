# Deploy OpenCash

Panduan ini ditulis untuk kondisi project apa adanya (Laravel 13, PHP 8.4, MySQL).
Belum ada server yang disentuh — semua langkah di bawah masih manual/opsional.

## Ringkasan kebutuhan

| Kebutuhan | Kenapa |
|---|---|
| PHP 8.4 + ekstensi `pdo_mysql gd intl zip bcmath` | `composer.json` mensyaratkan `php ^8.4` dan ekstensi itu |
| **MySQL** (bukan Postgres/SQLite) | migration `2026_09_18_210000_convert_stored_timestamps_to_wib.php` pakai `DATE_ADD()` / `DATE_SUB()` yang khas MySQL |
| **Disk permanen** untuk `storage/app/public` | upload QRIS, bukti transfer, dan foto nota disimpan ke `disk('public')` |
| `CACHE_STORE=file` | tabel `cache` tidak ada di migration project ini; pakai `database` akan error |
| `SESSION_DRIVER=database` | tabel `sessions` ada di migration `0001_01_01_000000_create_users_table.php` |
| Queue worker (opsional) | `QUEUE_CONNECTION=database`, dipakai untuk notifikasi |

## File yang sudah disiapkan

| File | Fungsi |
|---|---|
| `Dockerfile` | multi-stage: build Vite (Node 22) → `composer install --no-dev` → runtime FrankenPHP PHP 8.4 |
| `Caddyfile` | web server FrankenPHP, `root public/`, listen di `:$PORT` |
| `docker/entrypoint.sh` | cek `APP_KEY`, `migrate --force`, `storage:link`, cache config/route/view, lalu jalankan FrankenPHP |
| `.dockerignore` | `.env`, `vendor`, `node_modules`, `public/build`, storage runtime tidak ikut ke image |
| `.env.production.example` | template env produksi (`APP_DEBUG=false`, `CACHE_STORE=file`, `LOG_CHANNEL=stderr`) |
| `docker-compose.yml` | uji lokal: app + MySQL 8.4 + volume uploads |

Image ini **portabel**: Railway, Render, Fly.io, maupun VPS sendiri semuanya bisa memakai `Dockerfile` yang sama.

## Uji dulu di laptop (opsional, butuh Docker Desktop)

```bash
cd /d/applications/opencash
export APP_KEY=$(php artisan key:generate --show)
docker compose up --build
# buka http://localhost:8080
```

> Docker Desktop belum terpasang di mesin ini, jadi langkah ini belum pernah dijalankan.

## Opsi A — Railway (deploy otomatis dari GitHub)

1. Push repo ke GitHub (Railway build dari repo, bukan dari laptop).
2. Railway → **New Project → Deploy from GitHub repo** → pilih `aliferino/opencash`.
3. Tambah service **MySQL** di project yang sama.
4. Set environment variables di service app:

```
APP_NAME=OpenCash
APP_ENV=production
APP_KEY=<hasil: php artisan key:generate --show>
APP_DEBUG=false
APP_URL=https://<domain-railway-kamu>
LOG_CHANNEL=stderr
LOG_LEVEL=warning
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

5. Tambah **Volume** dengan mount path `/app/storage/app/public` (kalau tidak, upload hilang tiap redeploy).
6. Railway otomatis mendeteksi `Dockerfile`. Deploy pertama akan menjalankan migrasi lewat entrypoint.
7. Bikin admin pertama:

```bash
railway run php artisan tinker
# atau lewat shell service: php artisan db:seed
```

> Catatan harga: plan Free Railway ($1/bln kredit) terlalu kecil untuk Laravel + MySQL.
> Yang realistis: trial $5 (30 hari, tanpa kartu kredit), lalu Hobby $5/bln.

## Opsi B — Render

Sama seperti Railway, tapi **Render free tidak punya disk permanen** dan service di-suspend setelah 15 menit idle.
Kalau pakai Render, upload harus pindah ke object storage dulu (Cloudflare R2 gratis 10 GB, S3-compatible):

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=<R2 access key>
AWS_SECRET_ACCESS_KEY=<R2 secret>
AWS_DEFAULT_REGION=auto
AWS_BUCKET=<nama bucket>
AWS_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

Itu juga butuh perubahan kecil di `config/filesystems.php` + memanggil `Storage::disk('s3')` di controller upload.
Belum dikerjakan — project ini sekarang masih `disk('public')`.

## Opsi C — VPS sendiri (SSH)

Prasyarat yang **belum** ada di mesin ini:

- kunci SSH (di `~/.ssh/` cuma ada `known_hosts`, tidak ada `id_rsa`/`id_ed25519`)
- user + host server

Catatan penting: host di `known_hosts` — `103.187.147.170` — saat ini melayani aplikasi lain
(halaman login-nya berjudul **KASSPOT**, Inertia/React), bukan OpenCash. Host `103.37.124.80` tidak merespons.
Jadi jangan asal menimpa; pastikan dulu vhost mana yang boleh dipakai.

Langkahnya kalau kredensial sudah ada:

```bash
# di server (Ubuntu/Debian)
sudo apt install -y docker.io docker-compose-plugin git
git clone https://github.com/aliferino/opencash.git /opt/opencash
cd /opt/opencash
cp .env.production.example .env   # isi APP_KEY, DB_*, APP_URL
docker compose up -d --build
```

Lalu taruh Nginx/Caddy di depan port 8080 untuk TLS, dan arahkan `root` ke `public/`.

## Setelah deploy: wajib dilakukan

1. **Ganti password default.** `README.md` mencatat password seeder default dan menulis
   *"Ganti password ini sebelum deploy ke production."* Jangan lupa.
2. **`APP_DEBUG=false`** dan **`APP_KEY`** terisi (entrypoint akan menolak start kalau `APP_KEY` kosong).
3. **Backup database** MySQL secara berkala (upload saja tidak cukup).
4. **Queue worker** kalau notifikasi mau jalan: `php artisan queue:work --tries=3`
   (di Railway/Render jadi service terpisah; di VPS pakai systemd/supervisor).

## Yang belum ada dan disengaja

- Tidak ada `render.yaml` / `railway.json` — Railway & Render sudah cukup pintar mendeteksi `Dockerfile`,
  jadi tidak perlu file tambahan yang harus dirawat.
- Tidak ada CI/CD GitHub Actions — bisa ditambah nanti kalau mau build otomatis + smoke test.
- Tidak ada `.env.production` asli — file itu **tidak boleh** masuk repo (sudah ada di `.gitignore`).
  Semua nilai rahasia diisi lewat dashboard platform.
