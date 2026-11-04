# Smart Waste Management System (SWMS)

SWMS adalah backend Laravel untuk pengelolaan laporan sampah. Project ini menyediakan dua interface:

- RESTful API Sanctum untuk aplikasi mobile pengguna.
- Dashboard admin berbasis Blade dan Tailwind CSS untuk mengelola user dan laporan.

## Technology Stack

- PHP 8.3+ (tested on PHP 8.5.12)
- Laravel 13
- Laravel Sanctum 4
- SQLite untuk development lokal
- Blade, Tailwind CSS 4, dan Vite
- PHPUnit 12 dan Laravel Pint

## Requirements

- PHP `8.5+`
- Composer
- Node.js dan npm
- SQLite extension untuk PHP

## Local Setup

Clone project, install dependency, dan siapkan environment:

```bash
composer install
copy .env.example .env
php artisan key:generate
npm install
```

Pastikan `.env` memiliki konfigurasi database dan credential seeder:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SWMS_ADMIN_PASSWORD="ganti-dengan-password-admin-minimal-12-karakter"
SWMS_DEMO_PASSWORD="ganti-dengan-password-demo-minimal-12-karakter"
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

Dashboard menyediakan:

- Ringkasan jumlah laporan berdasarkan status.
- Daftar laporan terbaru.
- Filter dan detail laporan.
- Update status dengan workflow `pending` → `in_progress` → `completed` atau `rejected`.
- Catatan admin untuk pengguna mobile.
- Manajemen user dan role.

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
- Admin tidak dapat menggunakan mobile report endpoint.
- Login dan register memiliki rate limit berbasis email + IP.
- Upload report dibatasi ke JPG, JPEG, PNG, WEBP dengan maksimum 5 MB.
- Status report tidak dapat dikontrol dari payload mobile.
- Password dan remember token disembunyikan dari serialization user.
- Form web memakai CSRF protection.
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

Validasi Blade dan frontend build:

```bash
php artisan view:cache
npm run build
```

Security dependency audit:

```bash
composer audit --locked
```

Test suite mencakup guest redirect, admin/user authorization, mobile API isolation, registration, report creation, image validation, status workflow, dan user management.

## GHCR and CI/CD

Workflow [`.github/workflows/docker-publish.yml`](.github/workflows/docker-publish.yml) berjalan ketika ada push ke branch `main` atau `master`, dan juga dapat dijalankan manual melalui `workflow_dispatch`.

Alurnya:

1. GitHub Actions checkout source.
2. Setup PHP 8.5 dan Node.js 22.
3. Install dependency Composer dan npm.
4. Jalankan PHPUnit dan build Vite.
5. Jika test lulus, build satu production image dari [Dockerfile](Dockerfile).
6. Login menggunakan `GITHUB_TOKEN` dan publish ke GHCR.

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

Migration dijalankan sebagai langkah release/deployment terpisah:

```bash
php artisan migrate --force
```

Jangan menjalankan `db:seed` production tanpa keputusan eksplisit karena seeder memperbarui akun demo berdasarkan `SWMS_ADMIN_PASSWORD` dan `SWMS_DEMO_PASSWORD`.
