# Smart Waste Management System (SWMS)

SWMS adalah backend Laravel untuk pengelolaan laporan sampah. Project ini menyediakan:

- REST API Sanctum untuk aplikasi mobile dan frontend admin terpisah.
- Login admin dan satu dashboard health check minimal.

## Technology Stack

- PHP 8.3+ (tested on PHP 8.5.12)
- Laravel 13
- Laravel Sanctum 4
- PostgreSQL (supabase)
- Cloudflare R2 melalui S3-compatible Flysystem adapter
- PHPUnit 12 dan Laravel Pint

## Requirements

- PHP `8.5+`
- Composer
- SQLite extension untuk PHP

## Local Setup

Clone project, install dependency, dan siapkan environment:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Pastikan `.env` memiliki konfigurasi database dan credential seeder:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SWMS_ADMIN_PASSWORD="ganti-dengan-password-admin-minimal-12-karakter"
SWMS_DEMO_PASSWORD="ganti-dengan-password-demo-minimal-12-karakter"

AWS_ACCESS_KEY_ID="R2 S3 access key ID"
AWS_SECRET_ACCESS_KEY="R2 S3 secret access key"
AWS_BUCKET=swms
AWS_DEFAULT_REGION=auto
AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://cloudflare_public_url.dev
```

Karakter `#` di dalam password harus berada di dalam tanda kutip.

Buat database, jalankan migration, dan seed data demo:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed --no-interaction
```

Jika database sudah pernah di-seed dan ingin menyelaraskan ulang akun demo:

```bash
php artisan config:clear
php artisan db:seed --no-interaction
```

Jalankan aplikasi:

```bash
php artisan serve
```

Aplikasi tersedia di `http://localhost:8000`.

## Admin Dashboard

Buka:

```text
http://localhost:8000/admin/login
```

Akun admin seeder:

- Email: `admin@swms.test`
- Password: nilai dari `SWMS_ADMIN_PASSWORD` di `.env`

Password tidak disimpan di source code atau dokumentasi.

Dashboard hanya memeriksa status aplikasi/API dan koneksi database. Pengelolaan laporan dan user tersedia melalui Admin API.

## Mobile API

Base path API adalah:

```text
/api/v1
```

Endpoint tersedia:

| Method | Endpoint | Authentication |
| --- | --- | --- |
| `POST` | `/auth/register` | Public |
| `POST` | `/auth/login` | Public |
| `GET` | `/auth/me` | Sanctum token |
| `POST` | `/auth/logout` | Sanctum token |
| `GET` | `/reports` | User token |
| `POST` | `/reports` | User token |
| `GET` | `/reports/{report}` | User token, owner only |
| `PATCH` | `/reports/{report}` | User token, owner only; status remains admin-managed |
| `GET` | `/admin/dashboard` | Admin token |
| `GET` | `/admin/reports` | Admin token |
| `GET/PATCH/DELETE` | `/admin/reports/{report}` | Admin token |
| `PATCH` | `/admin/reports/{report}/status` | Admin token |
| `GET/POST` | `/admin/users` | Admin token |
| `GET/PATCH/DELETE` | `/admin/users/{user}` | Admin token |

Header authenticated request:

```http
Accept: application/json
Authorization: Bearer <token>
```

Dokumentasi endpoint lengkap, request body, response, validation, pagination, upload foto, error format, dan security requirement tersedia di [docs/API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md).

## Architecture

Project mengikuti pemisahan Controller–Service berbasis domain:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   └── Web/Admin/
│   ├── Middleware/
│   └── Requests/
├── Models/
└── Services/
    ├── Auth/
    ├── Report/
    └── User/
```

- Controller menangani request HTTP dan response.
- Form Request menangani validation dan authorization boundary.
- Service menangani business logic, Eloquent orchestration, transaction, dan storage.
- Model menjaga relationship, fillable attributes, scope, dan status workflow.

## Security Baseline

- Admin web menggunakan session authentication dan middleware role admin.
- Mobile API menggunakan Sanctum bearer token.
- User mobile hanya dapat melihat report miliknya sendiri.
- User hanya dapat mengubah field laporan miliknya sendiri; status dan catatan admin hanya dapat diubah admin.
- Admin API routes menggunakan Sanctum dan role middleware `admin`.
- Admin tidak dapat menggunakan mobile report endpoint.
- Login dan register memiliki rate limit berbasis email + IP.
- Upload report dibatasi ke JPG, JPEG, PNG, WEBP dengan maksimum 5 MB.
- Status report tidak dapat dikontrol dari payload mobile.
- Password dan remember token disembunyikan dari serialization user.
- Admin web UI hanya memiliki login dan health dashboard.
- Jangan commit `.env`, token, password, atau credential production.

## Testing and Quality

Jalankan feature/unit test:

```bash
php artisan test --compact
```

Format PHP:

```bash
vendor/bin/pint --format agent
```

Validasi Blade:

```bash
php artisan view:cache
```

Security dependency audit:

```bash
composer audit --locked
```

Test suite mencakup guest redirect, admin/user authorization, mobile API isolation, registration, R2 image storage contract, report editing, image validation, status workflow, dan user management.

## GHCR and CI/CD

Workflow [`.github/workflows/docker-publish.yml`](.github/workflows/docker-publish.yml) berjalan ketika ada push ke branch `main` atau `master`, dan juga dapat dijalankan manual melalui `workflow_dispatch`.

Alurnya:

1. GitHub Actions checkout source.
2. Setup PHP 8.5.
3. Install dependency Composer dan jalankan PHPUnit.
4. Jika test lulus, build satu production image dari [Dockerfile](Dockerfile).
5. Login menggunakan `GITHUB_TOKEN` dan publish ke GHCR.

Image yang dihasilkan:

```text
ghcr.io/<github-owner>/<repository>:latest
ghcr.io/<github-owner>/<repository>:sha-<commit>
```

Workflow memakai permission minimum: job test hanya `contents: read`, sedangkan job publish mendapat `packages: write`. Image juga menyertakan BuildKit provenance dan SBOM.

### Repository Setup

Pastikan repository memiliki:

- Actions enabled.
- Workflow permission yang mengizinkan `GITHUB_TOKEN` membaca source dan menulis package.
- Package visibility GHCR diatur sesuai kebutuhan deployment.
- Branch yang dipakai (`main`, atau sementara `master`) menerima workflow ini.

Tidak perlu membuat `GHCR_TOKEN` manual untuk publish dari repository yang sama; `secrets.GITHUB_TOKEN` digunakan oleh workflow. Untuk deployment dari server lain, gunakan Personal Access Token dengan scope package yang paling minimal dan simpan hanya sebagai secret di platform deployment.

### Runtime Environment

Image tidak membawa `.env`, database SQLite lokal, token, atau password. Saat container dijalankan, inject environment production seperti `APP_KEY`, `APP_ENV`, `APP_DEBUG=false`, database connection, `FILESYSTEM_DISK`, dan secret lain melalui platform deployment. Di Render, `APP_URL` otomatis memakai `RENDER_EXTERNAL_URL` jika `APP_URL` tidak diset; set `APP_URL` hanya bila ingin memaksa custom domain.

Untuk deployment non-Docker, jalankan migration sebagai langkah release:

```bash
php artisan migrate --force
```

Docker image saat ini juga menjalankan migration sebelum Apache saat container start. Untuk memindahkan foto legacy dari local `public` disk ke R2, tinjau terlebih dahulu:

```bash
php artisan reports:migrate-images-to-r2 --dry-run
```

Setelah memastikan target database dan daftar file benar, jalankan perintah tanpa `--dry-run`.

Jangan menjalankan `db:seed` production tanpa keputusan eksplisit karena seeder memperbarui akun demo berdasarkan `SWMS_ADMIN_PASSWORD` dan `SWMS_DEMO_PASSWORD`.
