Pemasangan TIK pada Ubuntu 24.04

**Pembaruan keamanan 23 Oktober 2026:** sebelum membuka layanan, ikuti [panduan PHP2Shell](update-keamanan-php2shell.md). PHP 8.3 dari Ubuntu 24.04 harus memakai paket resmi `8.3.6-0ubuntu0.24.04.13` atau lebih baru, atau PHP upstream 8.3.36 atau lebih baru. Memasang nama paket `php8.3-fpm` saja belum membuktikan tambalan aktif. Restart dan verifikasi worker FPM. Untuk server yang sudah berisi data, gunakan bagian pembaruan pada panduan tersebut.

Panduan ini memasang kode proyek saat ini pada `/var/www/tik`, menggunakan Nginx, PHP 8.3 FPM, dan database SQLite baru. Paket sudah menyertakan hasil build tampilan dan perubahan penghapusan fitur Backup Database.

Ganti `IP_SERVER`, `USER_SSH`, dan `tik.example.com` dengan alamat server, username SSH, dan domain Anda. Jalankan perintah Linux di terminal SSH server, kecuali langkah unggah yang dijalankan di PowerShell komputer Windows.

Jika ingin memulihkan data lama, siapkan backup database, file unggahan, serta APP_KEY lama. Langkah pembuatan database di bawah ditujukan untuk pemasangan dengan database kosong.

**1. Pasang kebutuhan server**

```bash
sudo apt update
sudo apt install -y nginx php8.3-fpm php8.3-cli php8.3-common php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip php8.3-sqlite3 php8.3-bcmath composer sqlite3 unzip curl
sudo systemctl enable --now php8.3-fpm nginx
php -v
composer --version
```

Paket menyertakan `public/build`, sehingga Node.js tidak diperlukan pada server untuk pemasangan ini.

**2. Buat folder proyek**

```bash
sudo mkdir -p /var/www/tik
sudo chown "$(id -un):$(id -gn)" /var/www/tik
sudo chmod 755 /var/www/tik
ls -lah /var/www/tik
```

Gunakan folder kosong untuk pemasangan baru ini. Jika berisi aplikasi atau data sebelumnya, periksa isinya sebelum mengekstrak paket.

**3. Unggah paket dari Windows**

Jalankan pada PowerShell komputer Windows. Ganti `USER_SSH@IP_SERVER` dengan akses SSH Anda.

```powershell
scp "D:\app\tik\storage\app\private\deploy\tik-security-20261023.tar.gz" USER_SSH@IP_SERVER:/tmp/
```

Kembali ke terminal server, lalu ekstrak:

```bash
tar -xzf /tmp/tik-security-20261023.tar.gz -C /var/www/tik
cd /var/www/tik
ls -l artisan composer.json public/build/manifest.json
```

**4. Atur konfigurasi aplikasi**

Pada pemasangan baru:

```bash
cp .env.example .env
nano .env
```

Ubah atau tambahkan nilai berikut. Gunakan domain Anda pada APP_URL. Dua kunci Turnstile diisi langsung di server dari widget Cloudflare milik Anda; domain tersebut harus terdaftar pada widget. Login aplikasi ini mewajibkan Turnstile.

```dotenv
APP_NAME="TIK"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://tik.example.com

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/tik/storage/app/private/database/database.sqlite

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

CLOUDFLARE_TURNSTILE_SITE_KEY=ISI_SITE_KEY_ANDA
CLOUDFLARE_TURNSTILE_SECRET_KEY=ISI_SECRET_KEY_ANDA
```

QUEUE_CONNECTION=sync menjalankan pekerjaan langsung. Kode proyek saat panduan ini dibuat tidak mendefinisikan pekerjaan queue atau jadwal aplikasi yang memerlukan worker tambahan.

**5. Pasang dependensi dan siapkan folder yang dapat ditulis**

```bash
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public storage/app/private/database bootstrap/cache
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan key:generate --force
touch storage/app/private/database/database.sqlite
sudo chgrp www-data .env
chmod 640 .env
sudo chgrp -R www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 2770 {} +
sudo find storage bootstrap/cache -type f -exec chmod 660 {} +
sudo -u www-data php artisan migrate --force
php artisan storage:link
```

Jalankan Composer sebagai user SSH pemilik proyek. Izin tulis diberikan pada `storage` dan `bootstrap/cache`, termasuk lokasi database SQLite yang dipilih di atas.

Jangan menjalankan `migrate:fresh` pada database berisi data. Pemasangan ini cukup memakai `migrate --force`; seeder bawaan membuat akun uji dan tidak digunakan.

**6. Buat admin pertama**

```bash
php artisan tinker
```

Di dalam Tinker, jalankan satu baris berikut. Isi email dan password melalui prompt. Password akan disembunyikan saat diketik dan model User menyimpannya sebagai hash.

```php
\App\Models\User::create(['name' => 'Administrator', 'email' => \Laravel\Prompts\text('Email admin', required: true), 'password' => \Laravel\Prompts\password('Password admin', required: true, validate: fn ($value) => strlen($value) >= 12 ? null : 'Minimal 12 karakter.'), 'peran' => 'superadmin', 'aktif' => true]);
```

Gunakan password minimal 12 karakter dengan kombinasi huruf, angka, dan simbol. Akhiri Tinker dengan:

```php
exit
```

**7. Atur Nginx**

```bash
sudo cp docs/nginx-tik.conf /etc/nginx/sites-available/tik
sudo nano /etc/nginx/sites-available/tik
```

Ganti `tik.example.com` pada server_name dengan domain Anda. Konfigurasi menggunakan document root `/var/www/tik/public` dan socket `/run/php/php8.3-fpm.sock`.

Aktifkan situs baru:

```bash
sudo ln -s /etc/nginx/sites-available/tik /etc/nginx/sites-enabled/tik
sudo nginx -t && sudo systemctl reload nginx
```

Domain harus mengarah ke IP server. Izinkan koneksi TCP 80 dan 443 pada firewall/provider. Jika UFW sudah aktif, jalankan:

```bash
sudo ufw allow 'Nginx Full'
```

**8. Aktifkan HTTPS untuk domain**

Setelah DNS domain mengarah ke server dan situs HTTP dapat dijangkau:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tik.example.com
```

Ganti domain pada perintah tersebut. Setelah sertifikat berhasil terpasang, edit `.env`:

```bash
nano /var/www/tik/.env
```

Atur:

```dotenv
APP_URL=https://tik.example.com
SESSION_SECURE_COOKIE=true
```

Lalu:

```bash
cd /var/www/tik
sudo -u www-data php artisan optimize
```

**9. Periksa hasil pemasangan**

```bash
sudo -u www-data php artisan migrate:status
curl -I https://tik.example.com/up
curl -I https://tik.example.com/login
```

Route `/up` seharusnya mengembalikan HTTP 200. Buka `https://tik.example.com/login`, selesaikan Turnstile, dan masuk menggunakan akun admin yang dibuat. Periksa portal dan menu aplikasi. Jika ada kegagalan, lihat:

```bash
sudo tail -n 60 /var/www/tik/storage/logs/laravel.log
sudo tail -n 60 /var/log/nginx/tik-error.log
```

Panduan disusun berdasarkan konfigurasi lokal proyek dan dokumentasi resmi [deployment Laravel 12](https://laravel.com/docs/12.x/deployment), [PHP pada Ubuntu](https://documentation.ubuntu.com/server/how-to/web-services/install-php/), dan [pengelolaan widget Turnstile](https://developers.cloudflare.com/turnstile/get-started/widget-management/). Build frontend berhasil secara lokal; instalasi PHP, migrasi, Nginx, HTTPS, dan login perlu diverifikasi di server tujuan.
