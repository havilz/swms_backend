# SWMS Backend API Documentation

Dokumentasi ini menjelaskan kontrak API untuk aplikasi mobile pengguna dan frontend admin yang terpisah.

## API Contract

- **Base path**: `/api/v1`
- **Authentication**: Laravel Sanctum personal access token
- **Format**: JSON, kecuali create report yang memakai `multipart/form-data`
- **Required headers** untuk endpoint authenticated:

```http
Accept: application/json
Authorization: Bearer <token>
```

Contoh base URL lokal:

```text
http://localhost:8000/api/v1
```

Untuk staging atau production, ganti host sesuai environment aplikasi.

## Storage configuration

Report images use the `r2` disk backed by Cloudflare's S3-compatible API. Set `R2_ACCESS_KEY_ID` and `R2_SECRET_ACCESS_KEY` to the R2 S3 access-key pair, `R2_BUCKET_NAME=swms`, `AWS_DEFAULT_REGION=auto`, `R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com`, and `R2_PUBLIC_URL` to the bucket's public domain. Do not use the Cloudflare dashboard API token as the S3 secret or expose the S3 API endpoint as an image URL. The backend also accepts the legacy `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, and `AWS_ENDPOINT` names as fallbacks.

The database stores the complete public URL in `reports.image_url`. For backward compatibility, report JSON includes `image_path` with the same URL. Legacy local images can be moved with `php artisan reports:migrate-images-to-r2 --dry-run`; review the output before running the command without `--dry-run`.

## Authentication Flow

1. Register atau login untuk mendapatkan `token`.
2. Simpan token secara aman di secure storage perangkat mobile.
3. Kirim token sebagai `Authorization: Bearer <token>` pada endpoint yang membutuhkan login.
4. Saat logout, token aktif dihapus dari server.

API register selalu membuat akun dengan role `user`. Role `admin` tidak dapat dibuat melalui API mobile.

### POST `/auth/register`

Membuat akun pengguna mobile baru dan langsung mengembalikan token.

#### Request body

```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "phone": "081234567890",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Validation

| Field | Required | Rules |
| --- | --- | --- |
| `name` | Ya | String, maksimum 255 karakter |
| `email` | Ya | Email valid, unik di tabel users |
| `phone` | Tidak | String, maksimum 20 karakter |
| `password` | Ya | Minimum 8 karakter dan harus sama dengan `password_confirmation` |
| `password_confirmation` | Ya | Harus sama dengan `password` |

#### Response `201 Created`

```json
{
  "message": "Registrasi berhasil.",
  "data": {
    "user": {
      "id": 10,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "phone": "081234567890",
      "role": "user",
      "avatar": null,
      "email_verified_at": null,
      "created_at": "2026-11-02T10:00:00.000000Z",
      "updated_at": "2026-11-02T10:00:00.000000Z"
    },
    "token": "1|example-token"
  }
}
```

### POST `/auth/login`

Login pengguna dan membuat personal access token baru.

#### Request body

```json
{
  "email": "budi@example.com",
  "password": "password123",
  "remember": false
}
```

`remember` bersifat opsional dan boolean. Token dibuat untuk user yang berhasil terautentikasi, termasuk jika credential admin digunakan; endpoint report mobile tetap hanya menerima role `user`.

#### Response `200 OK`

```json
{
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 10,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "phone": "081234567890",
      "role": "user",
      "avatar": null,
      "email_verified_at": null,
      "created_at": "2026-11-02T10:00:00.000000Z",
      "updated_at": "2026-11-02T10:00:00.000000Z"
    },
    "token": "1|example-token"
  }
}
```

Login dan register dilindungi rate limit `api-auth` sebesar 10 request per menit berdasarkan kombinasi email dan IP.

### GET `/auth/me`

Mengambil profil user dari token aktif.

#### Response `200 OK`

```json
{
  "data": {
    "id": 10,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "phone": "081234567890",
    "role": "user",
    "avatar": null,
    "email_verified_at": null,
    "created_at": "2026-11-02T10:00:00.000000Z",
    "updated_at": "2026-11-02T10:00:00.000000Z"
  }
}
```

### POST `/auth/logout`

Menghapus token Sanctum yang sedang digunakan. Endpoint ini tidak menghapus akun user.

#### Response `200 OK`

```json
{
  "message": "Logout berhasil."
}
```

## Reports

Semua endpoint reports hanya dapat dipakai oleh user mobile dengan role `user`. Admin web tidak dapat menggunakan endpoint ini.

### GET `/reports`

Mengambil laporan milik user yang sedang login. User tidak dapat melihat laporan user lain.

#### Query parameters

| Parameter | Required | Rules | Default |
| --- | --- | --- | --- |
| `status` | Tidak | `pending`, `in_progress`, `completed`, atau `rejected` | Semua status |
| `search` | Tidak | String, maksimum 255 karakter; mencari pada title, location, dan data user | Tidak ada |
| `per_page` | Tidak | Integer 1–50 | `10` |
| `page` | Tidak | Nomor halaman pagination Laravel | `1` |

#### Example

```http
GET /api/v1/reports?status=pending&search=taman&per_page=10&page=1
Accept: application/json
Authorization: Bearer <token>
```

#### Response `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "title": "Sampah di taman",
      "description": "Tumpukan sampah menghalangi jalan.",
      "location_address": "Taman Kota",
      "latitude": "-6.2000000",
      "longitude": "106.8000000",
      "image_url": "https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/reports/example.jpg",
      "image_path": "https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/reports/example.jpg",
      "status": "pending",
      "admin_notes": null,
      "created_at": "2026-11-02T10:00:00.000000Z",
      "updated_at": "2026-11-02T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

Database menyimpan URL publik penuh pada kolom `image_url`. API mengembalikan `image_url` dan alias `image_path` dengan nilai yang sama untuk kompatibilitas client lama. Gambar diunggah ke Cloudflare R2 menggunakan kredensial S3; `R2_PUBLIC_URL` harus menunjuk ke domain publik bucket.

### POST `/reports`

Membuat laporan sampah baru. Gunakan `multipart/form-data` jika mengirim foto.

#### Request fields

| Field | Required | Rules |
| --- | --- | --- |
| `title` | Ya | String, maksimum 255 karakter |
| `description` | Ya | String |
| `location_address` | Ya | String, maksimum 255 karakter |
| `latitude` | Tidak | Numeric antara `-90` dan `90` |
| `longitude` | Tidak | Numeric antara `-180` dan `180` |
| `image` | Tidak | File image dengan MIME `jpg`, `jpeg`, `png`, atau `webp`; maksimum 5 MB |

Status tidak boleh dikirim dari mobile client. Server selalu menetapkan status awal `pending`.

#### cURL example

```bash
curl -X POST "http://localhost:8000/api/v1/reports" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -F "title=Sampah di taman" \
  -F "description=Tumpukan sampah menghalangi jalan." \
  -F "location_address=Taman Kota" \
  -F "latitude=-6.2" \
  -F "longitude=106.8" \
  -F "image=@./trash.jpg"
```

#### Response `201 Created`

```json
{
  "message": "Laporan berhasil dibuat.",
  "data": {
    "id": 1,
    "user_id": 10,
    "title": "Sampah di taman",
    "description": "Tumpukan sampah menghalangi jalan.",
    "location_address": "Taman Kota",
    "latitude": "-6.2000000",
    "longitude": "106.8000000",
    "image_url": "https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/reports/example.jpg",
    "image_path": "https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/reports/example.jpg",
    "status": "pending",
    "admin_notes": null,
    "created_at": "2026-11-02T10:00:00.000000Z",
    "updated_at": "2026-11-02T10:00:00.000000Z"
  }
}
```

### GET `/reports/{report}`

Mengambil detail satu laporan. `{report}` adalah numeric ID laporan.

User yang bukan pemilik laporan menerima `403 Forbidden`.

#### Response `200 OK`

```json
{
  "data": {
    "id": 1,
    "user_id": 10,
    "title": "Sampah di taman",
    "description": "Tumpukan sampah menghalangi jalan.",
    "location_address": "Taman Kota",
    "latitude": "-6.2000000",
    "longitude": "106.8000000",
    "image_url": "https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/reports/example.jpg",
    "image_path": "https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/reports/example.jpg",
    "status": "pending",
    "admin_notes": null,
    "created_at": "2026-11-02T10:00:00.000000Z",
    "updated_at": "2026-11-02T10:00:00.000000Z"
  }
}
```

### GET `/reports/{report}/image`

Mengirim byte foto laporan melalui API terautentikasi agar aplikasi mobile tidak perlu mengakses domain publik R2 secara langsung. Hanya pemilik laporan yang dapat mengakses endpoint ini; pengguna lain menerima `403 Forbidden`. Response sukses berisi byte gambar dengan `Content-Type` `image/jpeg`, `image/png`, atau `image/webp`, serta cache privat.

Jika laporan tidak memiliki foto atau objek tidak ditemukan, API mengembalikan `404 Not Found`.

### PATCH `/reports/{report}`

Memperbarui laporan milik user yang sedang login. `{report}` harus dimiliki user tersebut; pemilik lain menerima `403 Forbidden`. Request menerima JSON, atau `multipart/form-data` bila mengganti gambar. Untuk multipart, kirim sebagai `POST` dengan field `_method=PATCH` agar upload file diparse konsisten oleh PHP.

Field yang dapat diubah: `title`, `description`, `location_address`, `latitude`, `longitude`, `image`, dan `remove_image`. Field bersifat opsional, tetapi minimal satu perubahan harus dikirim. Gambar baru disimpan ke R2 dan URL pada database diperbarui; gambar sebelumnya dihapus setelah perubahan tersimpan.

User tidak dapat mengubah `status`, `admin_notes`, atau `user_id`; perubahan status hanya dapat dilakukan melalui Admin API.

Kirim `remove_image: true` untuk menghapus gambar yang sudah ada. `image` dan `remove_image` tidak boleh dikirim bersamaan. Response `200 OK` berisi `message` dan objek `data` laporan yang diperbarui.

### DELETE `/reports/{report}`

Menghapus laporan milik user yang sedang login beserta gambarnya di R2. User yang bukan pemilik menerima `403 Forbidden`.

#### Response `200 OK`

```json
{
  "message": "Laporan berhasil dihapus."
}
```

## Admin API

Semua endpoint `/admin/*` memerlukan Sanctum token milik user dengan role `admin`. Token admin diperoleh melalui `POST /auth/login`; token user biasa menerima `403 Forbidden`.

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| `GET` | `/admin/dashboard` | Statistik laporan dan laporan terbaru |
| `GET` | `/admin/reports` | Daftar laporan dengan filter status, pencarian, tanggal, dan paginasi |
| `GET` | `/admin/reports/{report}` | Detail laporan dan data pelapor |
| `POST` | `/admin/reports/{report}/classify` | Minta saran klasifikasi foto laporan dari model AI |
| `PATCH` | `/admin/reports/{report}/status` | Perbarui status dan catatan admin sesuai workflow |
| `DELETE` | `/admin/reports/{report}` | Hapus laporan serta gambar terkait |
| `GET` | `/admin/users` | Daftar user/admin dengan filter role, pencarian, dan paginasi |
| `POST` | `/admin/users` | Buat user atau admin |
| `GET` | `/admin/users/{user}` | Detail user dan jumlah laporan |
| `PATCH` | `/admin/users/{user}` | Perbarui user |
| `DELETE` | `/admin/users/{user}` | Hapus user; admin tidak dapat menghapus akunnya sendiri |

Web UI backend hanya menyediakan login admin dan dashboard health check pada `/admin/dashboard`. Endpoint `GET /up` adalah health endpoint API publik Laravel.

### POST `/admin/reports/{report}/classify`

Endpoint ini hanya menerima Sanctum bearer token administrator. Backend membaca
foto dari storage laporan, mengirimnya sebagai multipart ke service model, lalu
meneruskan hasil tervalidasi. Batas request adalah 10 MiB dengan timeout
koneksi 3 detik dan timeout keseluruhan 20 detik. Maksimum 10 request per menit
per admin.

Environment backend:

```env
SWMS_MODEL_API_URL=https://<model-service>/predict
SWMS_MODEL_API_TOKEN=<shared-secret>
```

URL wajib HTTPS. Token yang sama harus disetel sebagai
`SWMS_MODEL_API_TOKEN` di service model dan jangan pernah ditaruh di dashboard,
response, atau log.

Response `200 OK` berisi `predicted_class`, `confidence`, `probabilities`, dan
`model_version`. Nilainya hanya saran untuk admin dan tidak disimpan,
ditambahkan ke status laporan, atau digunakan untuk memperbarui kategori
laporan. Tanpa foto endpoint mengembalikan `404`; konfigurasi service tidak
tersedia mengembalikan `503`; gambar terlalu besar mengembalikan `413`;
gangguan atau response upstream tidak valid mengembalikan `502`.

## Report Status

Status laporan dikelola administrator melalui Admin API. Mobile client hanya membaca status dan `admin_notes`; user tidak dapat mengubah kedua field tersebut.

```text
pending ──> in_progress ──> completed
   │             │
   └─────────────┴────────> rejected
```

| Status | Meaning |
| --- | --- |
| `pending` | Laporan baru dan belum diverifikasi |
| `in_progress` | Laporan sedang ditangani |
| `completed` | Penanganan selesai |
| `rejected` | Laporan ditolak dengan alasan pada `admin_notes` bila tersedia |

## Error Responses

### Unauthenticated `401`

Token tidak ada, invalid, atau sudah tidak berlaku.

```json
{
  "message": "Unauthenticated."
}
```

### Forbidden `403`

User terautentikasi tetapi tidak memiliki akses ke resource tersebut.

```json
{
  "message": "Anda tidak memiliki hak akses untuk melihat laporan ini."
}
```

### Validation error `422`

Request tidak memenuhi validation rules.

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "image": [
      "The image field must be an image."
    ]
  }
}
```

### Rate limit `429`

Login atau register terlalu sering dilakukan. Mobile client perlu menghormati response rate limit dan mencoba kembali setelah jeda.

### Cloudflare R2 configuration and existing images

Atur `R2_ACCESS_KEY_ID` dan `R2_SECRET_ACCESS_KEY` dengan R2 S3 credentials, serta `R2_BUCKET_NAME=swms`, `AWS_DEFAULT_REGION=auto`, `R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com`, dan `R2_PUBLIC_URL` ke domain publik bucket. Backend juga menerima nama variabel lama `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, dan `AWS_ENDPOINT` sebagai fallback. Jangan gunakan Cloudflare dashboard API token sebagai S3 secret.

Migration database mengganti kolom `image_path` menjadi `image_url` tanpa menghapus nilai lama. Setelah R2 dikonfigurasi, jalankan `php artisan reports:migrate-images-to-r2 --dry-run`, tinjau daftar file, lalu jalankan `php artisan reports:migrate-images-to-r2` untuk memindahkan foto legacy dari disk `public` ke R2. Perintah ini aman dijalankan ulang dan mempertahankan record yang file lokalnya tidak ditemukan.

## Local Test Accounts

Credential admin dan demo user tidak ditulis di dokumentasi ini. Seeder mengambil password dari environment:

```env
SWMS_ADMIN_PASSWORD="..."
SWMS_DEMO_PASSWORD="..."
```

Gunakan `php artisan db:seed --no-interaction` setelah environment diisi.
