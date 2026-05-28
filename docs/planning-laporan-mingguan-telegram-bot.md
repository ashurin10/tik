# Planning Fitur Input Laporan Mingguan Menggunakan Telegram Bot

## Ringkasan

Fitur ini menambahkan kanal input laporan mingguan melalui Telegram Bot agar pegawai dapat mengirim laporan langsung dari Telegram. Sistem menerima pesan dari bot, memvalidasi format, mem-parsing isi laporan, lalu menyimpan data ke tabel `laporan_mingguans` yang sudah digunakan oleh modul Laporan Mingguan.

Fondasi yang sudah tersedia:

- Model utama: `App\Models\LaporanMingguan`
- Service parsing dan normalisasi: `App\Services\LaporanMingguanService`
- Endpoint web yang sudah ada: `laporan-mingguan/parse-text` dan `laporan-mingguan/bulk-store`
- Parser teks Telegram dokumen sudah mulai tercakup oleh `tests/Feature/LaporanMingguanTelegramDocumentParserTest.php`

## Tujuan

- Memudahkan input laporan mingguan tanpa harus membuka aplikasi web.
- Mengurangi duplikasi input dari chat Telegram ke aplikasi.
- Menjaga struktur data tetap sama dengan modul laporan mingguan saat ini.
- Memberi respons otomatis kepada pengirim saat laporan berhasil, perlu koreksi, atau gagal diproses.
- Menyediakan jejak audit sumber data dari Telegram.

## Di Luar Cakupan Awal

- Pembuatan dashboard Telegram penuh.
- Approval berlapis di Telegram.
- Parsing file `.docx`, `.pdf`, atau gambar secara langsung dari Telegram.
- Pengiriman laporan rekap otomatis ke grup, kecuali dijadikan fase lanjutan.

## Alur Pengguna

1. Pegawai membuka chat dengan bot Telegram.
2. Pegawai mengirim laporan dalam format yang didukung.
3. Bot menerima update melalui webhook Laravel.
4. Sistem memeriksa apakah pengirim Telegram sudah terdaftar.
5. Sistem mem-parsing pesan menjadi data laporan mingguan.
6. Sistem menampilkan ringkasan hasil parsing ke Telegram.
7. Pegawai mengonfirmasi penyimpanan dengan perintah `/simpan` atau tombol inline.
8. Sistem menyimpan laporan ke tabel `laporan_mingguans`.
9. Bot membalas status berhasil beserta ringkasan laporan.

## Format Pesan yang Didukung

### Format Terstruktur

```text
Tanggal : 25 Mei 2026
Nama Kegiatan : Monitoring jaringan kantor
Lokasi : Diskominfo Subang
Nama Pelaksana : Tio
Keterangan : Pemeriksaan koneksi internet
Hasil Kegiatan : Koneksi stabil dan tidak ditemukan gangguan
Kendala : -
```

### Format Ringkas

```text
25 Mei 2026
Monitoring jaringan kantor
Lokasi: Diskominfo Subang
PIC: Tio
Hasil: Koneksi stabil dan tidak ditemukan gangguan
```

### Format Dokumen Telegram

Format ini mengikuti pola export/chat Telegram yang sudah mulai didukung oleh `LaporanMingguanService`, misalnya:

```text
Dedi Nugraha
laporan antivirus.docx
Terlampir laporan penggunaan antivirus april 2026
```

## Data yang Disimpan

Data utama tetap mengikuti struktur tabel `laporan_mingguans`:

| Field | Sumber |
| --- | --- |
| `tanggal` | Hasil parsing pesan atau tanggal pesan Telegram |
| `nama_kegiatan` | Hasil parsing atau inferensi dari nama dokumen/pesan |
| `lokasi` | Hasil parsing, default `-` jika kosong |
| `hasil_deskripsi` | Isi hasil/keterangan laporan |
| `prioritas` | Default `Sedang`, bisa diisi dari pesan |
| `pic` | Nama pelaksana atau mapping dari Telegram user |
| `status` | Default `Selesai`, bisa diisi dari pesan |
| `keterangan_tindak_lanjut` | Kendala/tindak lanjut |
| `created_by`, `updated_by` | User aplikasi yang terhubung dengan Telegram sender |

## Tambahan Skema Database

### Tabel `telegram_users`

Digunakan untuk mapping akun Telegram ke user aplikasi.

Kolom yang disarankan:

- `id`
- `user_id` nullable, relasi ke `users.id`
- `telegram_user_id` unique
- `telegram_chat_id`
- `username` nullable
- `first_name` nullable
- `last_name` nullable
- `display_name` nullable
- `is_active` boolean default `true`
- `last_seen_at` nullable
- `created_at`
- `updated_at`

### Tabel `telegram_laporan_drafts`

Digunakan untuk menyimpan hasil parsing sementara sebelum user mengonfirmasi.

Kolom yang disarankan:

- `id`
- `telegram_user_id` relasi ke `telegram_users.id`
- `message_id`
- `chat_id`
- `raw_text` long text
- `parsed_payload` json
- `status` enum/string: `draft`, `saved`, `cancelled`, `failed`
- `error_message` nullable
- `expires_at`
- `created_at`
- `updated_at`

### Tambahan Opsional pada `laporan_mingguans`

Untuk audit asal input:

- `source` nullable, contoh: `web`, `telegram`
- `source_reference` nullable, contoh: `telegram:{chat_id}:{message_id}`

Jika ingin menghindari perubahan tabel utama pada fase awal, audit sumber dapat disimpan di tabel draft saja.

## Konfigurasi Environment

Tambahkan konfigurasi berikut:

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_WEBHOOK_SECRET=
TELEGRAM_ALLOWED_CHAT_IDS=
TELEGRAM_DRAFT_TTL_MINUTES=60
```

Tambahkan ke `config/services.php`:

```php
'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    'allowed_chat_ids' => env('TELEGRAM_ALLOWED_CHAT_IDS'),
    'draft_ttl_minutes' => env('TELEGRAM_DRAFT_TTL_MINUTES', 60),
],
```

## Rancangan Komponen

### Route API/Webhook

Tambahkan route webhook khusus Telegram:

```php
Route::post('/telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');
```

Catatan:

- Route dapat ditempatkan di `routes/web.php` jika ingin sederhana.
- Untuk desain lebih bersih, bisa dibuat `routes/api.php`.
- Secret harus divalidasi agar endpoint tidak bisa dipakai sembarang pihak.

### Controller

Controller baru:

- `App\Http\Controllers\TelegramWebhookController`

Tanggung jawab:

- Menerima payload webhook Telegram.
- Memvalidasi secret.
- Mengambil `message.text`, `message.caption`, `chat.id`, `from.id`, dan `message_id`.
- Meneruskan proses ke service.
- Mengembalikan response HTTP 200 cepat ke Telegram.

### Service

Service baru:

- `App\Services\Telegram\TelegramBotService`
- `App\Services\Telegram\TelegramLaporanMingguanService`

Tanggung jawab `TelegramBotService`:

- Mengirim pesan ke Telegram API.
- Mengirim inline keyboard untuk konfirmasi.
- Menangani error response Telegram.

Tanggung jawab `TelegramLaporanMingguanService`:

- Mapping Telegram user ke user aplikasi.
- Memanggil `LaporanMingguanService::parseMultipleTexts()`.
- Membuat draft laporan.
- Menyimpan draft menjadi `LaporanMingguan`.
- Membatalkan draft.

## Perintah Bot

| Perintah | Fungsi |
| --- | --- |
| `/start` | Menampilkan instruksi awal dan status akun |
| `/format` | Menampilkan contoh format laporan |
| `/simpan` | Menyimpan draft terakhir |
| `/batal` | Membatalkan draft terakhir |
| `/status` | Menampilkan draft aktif atau status mapping akun |
| `/help` | Bantuan singkat |

## Validasi dan Keamanan

- Webhook wajib memakai secret URL atau header secret.
- Batasi akses berdasarkan `TELEGRAM_ALLOWED_CHAT_IDS` untuk fase awal.
- Telegram user harus terhubung ke user aplikasi sebelum boleh menyimpan laporan.
- Jika tidak ada mapping user, bot hanya boleh menerima `/start` dan memberi instruksi pendaftaran.
- Jangan menyimpan laporan jika field wajib tidak lengkap:
  - `tanggal`
  - `nama_kegiatan`
  - `pic`
- Simpan raw text untuk audit dan debugging.
- Hindari membocorkan stack trace/error internal ke Telegram.

## Strategi Mapping User

### Opsi 1: Mapping Manual oleh Admin

Admin mengisi `telegram_user_id` pada data user atau tabel mapping.

Kelebihan:

- Sederhana.
- Aman untuk fase pertama.

Kekurangan:

- Membutuhkan admin untuk setup awal.

### Opsi 2: Kode Aktivasi

User login ke aplikasi web, membuka halaman profil, lalu membuat kode aktivasi. User mengirim:

```text
/aktivasi ABC123
```

Kelebihan:

- Lebih mandiri.
- Cocok jika jumlah user bertambah.

Kekurangan:

- Butuh UI tambahan.

Rekomendasi fase awal: mulai dari mapping manual, lalu lanjut ke kode aktivasi.

## Rencana Implementasi Bertahap

### Fase 1: Fondasi Webhook dan Parsing

- Tambahkan config Telegram di `config/services.php`.
- Buat migration `telegram_users`.
- Buat migration `telegram_laporan_drafts`.
- Buat `TelegramWebhookController`.
- Buat `TelegramBotService`.
- Buat `TelegramLaporanMingguanService`.
- Integrasikan parser yang sudah ada dari `LaporanMingguanService`.
- Balas pesan Telegram dengan ringkasan hasil parsing.

Output fase ini:

- Bot bisa menerima pesan.
- Bot bisa mem-parsing laporan.
- Bot bisa membuat draft.

### Fase 2: Konfirmasi dan Penyimpanan

- Tambahkan perintah `/simpan`.
- Tambahkan perintah `/batal`.
- Simpan draft valid ke `laporan_mingguans`.
- Isi `created_by` dan `updated_by` dari mapping user.
- Kirim balasan sukses/gagal ke Telegram.

Output fase ini:

- Laporan dari Telegram masuk ke halaman Laporan Mingguan.

### Fase 3: UX Bot

- Tambahkan `/format`.
- Tambahkan `/status`.
- Tambahkan inline keyboard: `Simpan`, `Batal`, `Edit via Web`.
- Tampilkan pesan validasi yang mudah dipahami.
- Jika hasil parsing lebih dari satu laporan, tampilkan jumlah item dan minta konfirmasi.

Output fase ini:

- Penggunaan bot lebih jelas dan minim kebingungan.

### Fase 4: Audit dan Admin

- Tambahkan halaman admin untuk melihat mapping Telegram user.
- Tambahkan filter laporan berdasarkan sumber `telegram`.
- Tambahkan log error webhook.
- Tambahkan pembersihan draft expired via scheduler.

Output fase ini:

- Admin dapat memantau dan merawat integrasi Telegram.

## Contoh Respons Bot

### Draft Berhasil Dibuat

```text
Draft laporan berhasil dibuat:

Tanggal: 25 Mei 2026
Kegiatan: Monitoring jaringan kantor
Lokasi: Diskominfo Subang
PIC: Tio
Status: Selesai
Prioritas: Sedang

Ketik /simpan untuk menyimpan atau /batal untuk membatalkan.
```

### Field Kurang Lengkap

```text
Laporan belum bisa disimpan karena data berikut belum lengkap:

- Tanggal
- Nama kegiatan

Ketik /format untuk melihat contoh format laporan.
```

### User Belum Terdaftar

```text
Akun Telegram Anda belum terhubung dengan akun aplikasi.

Telegram User ID: 123456789
Silakan hubungi admin untuk aktivasi.
```

## Testing

### Unit/Feature Test yang Disarankan

- Webhook menolak secret yang salah.
- Webhook menerima payload text Telegram valid.
- User Telegram tidak terdaftar tidak bisa menyimpan laporan.
- Pesan terstruktur berhasil menjadi draft.
- `/simpan` menyimpan draft ke `laporan_mingguans`.
- `/batal` mengubah status draft menjadi `cancelled`.
- Draft expired tidak bisa disimpan.
- Parsing multi-laporan menghasilkan beberapa item.
- Audit `created_by` dan `updated_by` sesuai user mapping.

### Test Payload Minimal

```json
{
  "message": {
    "message_id": 10,
    "chat": {
      "id": 123456789
    },
    "from": {
      "id": 123456789,
      "username": "tio",
      "first_name": "Tio"
    },
    "text": "Tanggal : 25 Mei 2026\nNama Kegiatan : Monitoring jaringan kantor\nLokasi : Diskominfo Subang\nNama Pelaksana : Tio\nHasil Kegiatan : Koneksi stabil"
  }
}
```

## Rollout

1. Buat bot melalui BotFather dan simpan token di `.env`.
2. Deploy route webhook ke domain HTTPS.
3. Set webhook Telegram ke endpoint aplikasi.
4. Tambahkan 1-2 user pilot ke `telegram_users`.
5. Uji input laporan dari chat pribadi.
6. Uji input laporan dari grup jika memang akan dipakai di grup.
7. Aktifkan untuk seluruh user setelah format dan validasi stabil.

## Risiko dan Mitigasi

| Risiko | Mitigasi |
| --- | --- |
| Pesan tidak sesuai format | Sediakan `/format` dan validasi jelas |
| Laporan ganda tersimpan | Gunakan draft + konfirmasi sebelum simpan |
| User Telegram tidak dikenali | Mapping user wajib sebelum menyimpan |
| Webhook disalahgunakan | Secret webhook dan allowlist chat |
| Telegram API gagal | Log error dan retry manual bila perlu |
| Parser salah menebak field | Tampilkan ringkasan draft sebelum simpan |

## Checklist Teknis

- [ ] Tambah env dan config Telegram.
- [ ] Buat migration `telegram_users`.
- [ ] Buat migration `telegram_laporan_drafts`.
- [ ] Buat model `TelegramUser`.
- [ ] Buat model `TelegramLaporanDraft`.
- [ ] Buat `TelegramWebhookController`.
- [ ] Buat `TelegramBotService`.
- [ ] Buat `TelegramLaporanMingguanService`.
- [ ] Integrasi ke `LaporanMingguanService`.
- [ ] Tambah perintah `/start`, `/format`, `/simpan`, `/batal`, `/status`.
- [ ] Tambah feature tests webhook dan simpan draft.
- [ ] Tambah dokumentasi setup webhook.
- [ ] Uji manual dengan bot Telegram asli.

## Catatan Implementasi

- Gunakan queue untuk pengiriman balasan Telegram jika proses mulai berat.
- Untuk fase awal, proses webhook bisa sinkron selama response tetap cepat.
- Pertahankan `LaporanMingguanService` sebagai pusat parsing agar input web dan Telegram konsisten.
- Hindari menaruh token bot di kode. Token hanya dari `.env`.
- Jika callback inline keyboard digunakan, webhook harus menangani `callback_query` selain `message`.
