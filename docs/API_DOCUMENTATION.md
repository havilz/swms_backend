# SWMS Mobile API Documentation

Dokumentasi ini menjelaskan kontrak API yang saat ini tersedia untuk aplikasi mobile pengguna.

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
      "image_path": "reports/example.jpg",
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

`image_path` adalah path storage saat ini, bukan URL absolut. Mobile client sebaiknya memperlakukan nilai ini sebagai path sampai endpoint media/public URL ditetapkan secara resmi.

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
    "image_path": "reports/example.jpg",
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
    "image_path": "reports/example.jpg",
    "status": "pending",
    "admin_notes": null,
    "created_at": "2026-11-02T10:00:00.000000Z",
    "updated_at": "2026-11-02T10:00:00.000000Z"
  }
}
```

## Report Status

Status laporan dikelola administrator melalui dashboard web. Mobile client hanya membaca status dan `admin_notes`.

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

## Security Requirements for Mobile Client

- Jangan menyimpan token di plain text storage atau log.
- Jangan mencetak password, token, atau response auth ke console production.
- Selalu kirim `Accept: application/json`.
- Gunakan HTTPS di staging/production.
- Jangan mengirim field `status`, `user_id`, atau `admin_notes` saat membuat report.
- Tampilkan `admin_notes` sebagai plain text; jangan merendernya sebagai HTML.
- Tangani `401` dengan menghapus token lokal dan mengarahkan user ke login.
- Tangani `403` sebagai akses ditolak, bukan sebagai sesi kadaluarsa.
- Tangani `422` per field agar pesan validasi dapat ditampilkan dekat input terkait.

## Current Limitations and Future API Work

- Belum ada API untuk update atau delete report dari mobile.
- Belum ada endpoint khusus untuk public image URL atau image download authorization.
- Belum memakai Eloquent API Resource; response mengikuti serialisasi model saat ini.
- Belum ada refresh-token flow; login kembali diperlukan jika token tidak lagi valid.
- Contract test dan OpenAPI/Swagger schema belum tersedia.

## Local Test Accounts

Credential admin dan demo user tidak ditulis di dokumentasi ini. Seeder mengambil password dari environment:

```env
SWMS_ADMIN_PASSWORD="..."
SWMS_DEMO_PASSWORD="..."
```

Gunakan `php artisan db:seed --no-interaction` setelah environment diisi.
