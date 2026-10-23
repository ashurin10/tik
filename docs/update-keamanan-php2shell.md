# Pembaruan keamanan TIK — 23 Oktober 2026

PHP2Shell (CVE-2026-109618 dan CVE-2026-109619) berada di runtime PHP dan dapat dipicu sebelum kode Laravel berjalan. Pembaruan Composer, validasi upload, Turnstile, dan aturan Nginx tidak menggantikan pembaruan PHP.

Versi upstream yang sudah diperbaiki: **8.2.35, 8.3.36, 8.4.27, atau 8.5.12**, beserta patch sesudahnya pada cabang masing-masing. Ubuntu 24.04 menyediakan backport resmi pada **8.3.6-0ubuntu0.24.04.13** atau yang lebih baru. Angka `PHP_VERSION` saja tidak cukup untuk menilai paket distro.

Sumber: [advisory PHP](https://www.php.net/archive/2026.php#2026-10-21-5), [Ubuntu CVE-2026-109618](https://ubuntu.com/security/CVE-2026-109618), dan [Ubuntu CVE-2026-109619](https://ubuntu.com/security/CVE-2026-109619). Periksa pembaruan vendor kembali saat menjalankan panduan ini.

## Server Ubuntu 24.04 yang sudah terpasang

Jalankan pada server melalui SSH. Ambil snapshot/backup database dan file upload terlebih dahulu. Pertahankan `.env` dan `APP_KEY` lama; pembaruan ini tidak memerlukan database kosong atau kunci baru.

Periksa sumber paket dan versi terpasang:

```bash
cat /etc/os-release
apt-cache policy php8.3-fpm php8.3-cli php8.3-common nginx
dpkg-query -W -f='${Package} ${Version}\n' 'php*' nginx
```

Perintah berikut ditujukan untuk paket resmi Ubuntu 24.04. Jika menggunakan PPA, container, panel hosting, atau versi PHP lain, terapkan pembaruan dari vendor tersebut ke SEMUA runtime web yang terpasang dan verifikasi kedua CVE. Jangan menyimpulkan paket PPA aman hanya dengan membandingkannya terhadap versi Ubuntu.

```bash
sudo apt update
sudo apt upgrade
sudo apt install --only-upgrade php8.3-fpm php8.3-cli php8.3-common

for package in php8.3-fpm php8.3-cli php8.3-common; do
    version=$(dpkg-query -W -f='${Version}' "$package") || exit 1
    dpkg --compare-versions "$version" ge '8.3.6-0ubuntu0.24.04.13' || {
        echo "BELUM DITAMBAL: $package $version"
        exit 1
    }
    echo "$package $version"
done
```

`apt upgrade` juga memperbarui paket sistem yang tersedia, termasuk Nginx, OpenSSL, database, dan Composer yang dikelola APT. Periksa paket yang ditahan dan `/var/run/reboot-required`; selesaikan pembaruan yang membutuhkan restart/reboot sesuai jadwal layanan. Pembaruan aplikasi tidak memperbarui perangkat lunak server secara otomatis.

Salin rilis aplikasi yang baru ke `/var/www/tik`, pertahankan data dan konfigurasi lama, lalu:

```bash
cd /var/www/tik
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
composer audit --locked --no-dev
sudo install -m 644 docs/php-tik-security.ini /etc/php/8.3/fpm/conf.d/99-tik-security.ini
sudo install -m 644 docs/php-tik-security.ini /etc/php/8.3/cli/conf.d/99-tik-security.ini
sudo php-fpm8.3 -t
```

Jika memperbarui dari rilis lama yang masih mempunyai `public/dl_logo.php`, hapus helper tersebut dari document root. Rilis baru tidak menyertakannya. Terapkan perubahan pada `docs/nginx-tik.conf` ke konfigurasi situs aktif dengan mempertahankan domain dan konfigurasi HTTPS/Certbot yang sudah digunakan. Hanya `/index.php` diteruskan ke FPM; skrip di direktori upload ditolak.

```bash
sudo nginx -t
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan optimize
sudo -u www-data php artisan queue:restart
sudo systemctl is-active php8.3-fpm nginx
```

Restart juga worker yang dikelola Supervisor/systemd, Octane, atau runtime PHP lain jika ada. Worker yang belum dimulai ulang dapat tetap menjalankan binary lama.

## Verifikasi PHP yang benar-benar melayani web

Periksa socket `fastcgi_pass` pada virtual host yang melayani domain TIK. Pemeriksaan CLI saja tidak membuktikan bahwa PHP web telah diperbarui.

```bash
sudo nginx -T
php -v
sudo php-fpm8.3 -v
sudo php-fpm8.3 -i | grep -E 'session.upload_progress.enabled|post_max_size|upload_max_filesize|Loaded Configuration'
```

Untuk menguji worker FPM aktif melalui socket, pasang klien FastCGI dan jalankan blok berikut sebagai satu sesi Bash. File sementara berada di storage privat dan dihapus setelah pemeriksaan. Sesuaikan socket jika konfigurasi situs berbeda.

```bash
sudo apt install libfcgi-bin
(
    set -eu
    probe=$(sudo -u www-data mktemp /var/www/tik/storage/app/private/runtime-check-XXXXXX.php)
    trap 'sudo rm -f -- "$probe"' EXIT
    printf '%s\n' '<?php echo PHP_VERSION, " ", PHP_SAPI, " upload_progress=", ini_get("session.upload_progress.enabled"), PHP_EOL;' \
        | sudo -u www-data tee "$probe" >/dev/null
    sudo env SCRIPT_FILENAME="$probe" SCRIPT_NAME=/runtime-check.php REQUEST_METHOD=GET \
        cgi-fcgi -bind -connect /run/php/php8.3-fpm.sock
)
curl -I https://tik.example.com/up
curl -I https://tik.example.com/login
curl -I https://tik.example.com/storage/test.php
```

Ganti domain dengan domain Anda. Probe harus melaporkan `fpm-fcgi` dan versi yang cocok dengan paket yang sudah diverifikasi. Pada paket Ubuntu, PHP masih dapat melaporkan `8.3.6`; bukti backport adalah revisi paket resmi yang diperiksa di atas. `/up` dan `/login` harus berfungsi, sedangkan skrip dalam `/storage` harus ditolak. Tidak perlu memublikasikan `phpinfo()`.

## Windows dan lingkungan build lokal

Folder `.tools/php` pada workspace lokal berisi PHP CLI **8.3.36** untuk pengujian, bukan penggantian otomatis PHP web di IIS/XAMPP. Perbarui binary PHP pada server yang sesungguhnya, cocokkan arsitektur serta TS/NTS dengan SAPI, gunakan ekstensi yang sesuai versi, lalu restart IIS/Apache/FastCGI. Konfirmasi versi dari worker web aktif. Jangan memakai rilis RC untuk produksi.

Node **24.22.1** dan npm **11.19.1** digunakan untuk build rilis ini. Node hanya dibutuhkan pada mesin build; server dapat menggunakan hasil `public/build` dalam paket rilis. Persyaratan build tercatat pada `.node-version` dan `package.json`.

```bash
npm ci
npm run audit:security
npm run build
composer run audit:security
composer test
```

`composer.json` menyatakan kompatibilitas bahasa PHP, termasuk paket distro dengan backport. Constraint tersebut dan hasil `composer audit` tidak memeriksa tambalan PHP2Shell pada runtime. Verifikasi PHP server tetap wajib.

## Perubahan aplikasi

- Laravel 12.70.1 dan seluruh dependensi PHP diperbarui ke versi kompatibel terbaru dari cabang yang dipakai.
- Vite 8, plugin Laravel Vite 3, Tailwind 4.3.3, Alpine, Axios, dan Tom Select diperbarui. Template dimigrasikan untuk utilitas Tailwind 4; diperlukan browser modern yang mendukungnya.
- Upload portal dibatasi ke JPG, PNG, GIF, dan WebP dengan pemeriksaan isi serta ekstensi. Nama file penyimpanan tetap dibuat oleh Laravel.
- Helper unduh logo publik dihapus; aturan Apache dan contoh Nginx membatasi eksekusi skrip.
- Konfigurasi PHP tambahan menonaktifkan upload progress dan membatasi ukuran request. Ini penguatan tambahan setelah patch, bukan perbaikan PHP2Shell tersendiri.

Paket rilis tidak menyertakan `.env`, salinannya, database, upload pengguna, cache runtime, `vendor`, `node_modules`, atau `.tools`. Pasang dependensi PHP dari lockfile pada server. Jangan menggunakan paket rilis lama untuk memperoleh pembaruan ini.

Validasi lokal: 63 tes / 1.147 assertion lulus pada PHP 8.3.36; `composer validate --strict`, pemeriksaan platform, dan audit Composer lulus. `npm ci`, build produksi, serta audit npm lulus tanpa advisory kerentanan. Halaman login diperiksa pada lebar 1440 dan 390 piksel tanpa overflow horizontal atau error JavaScript; layanan Turnstile eksternal tidak diuji. Konfigurasi Nginx/Apache dan paket PHP produksi belum dijalankan pada server tujuan.
