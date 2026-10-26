# Perapian cetak Laporan Mingguan — 26 Oktober 2026

Tabel sekarang menggunakan total lebar kolom 100%, dengan ruang lebih besar untuk hasil dan tindak lanjut. Ukuran A4 landscape, margin 12 mm, huruf tabel 9,5 pt, dan jarak antarbaris disesuaikan agar ringkas serta tetap terbaca.

Seluruh kegiatan mengalir dalam satu tabel. Kepala tabel berulang pada halaman lanjutan. Pengelompokan paksa dua baris terakhir bersama tanda tangan dihapus; tanda tangan mempunyai blok tersendiri yang tetap utuh. Nomor kegiatan dan seluruh isinya dipertahankan. Halaman pratinjau layar juga memakai lebar kertas yang konsisten.

Validasi cetak Chromium:

- Data uji 46 kegiatan: 9 halaman sebelum perbaikan menjadi 5 halaman sesudahnya, termasuk tanda tangan.
- Seluruh nomor 1–46 muncul tepat sekali dan seluruh teks berada di dalam margin cetak.
- Laporan kosong, 1, 2, dan 3 kegiatan tetap rapi dalam satu halaman.
- Laporan tanpa penandatangan serta satu uraian yang lebih panjang dari satu halaman diperiksa; isi panjang tetap lengkap dan blok tanda tangan tidak terpotong.
- Dua tes ekspor / 80 assertion lulus. Hasil PDF dirender menjadi gambar untuk memeriksa tata letak.

Jumlah halaman laporan sebenarnya mengikuti panjang isinya. Gunakan A4 landscape, skala 100%, dan matikan header/footer tambahan browser untuk hasil bersih.

## Pemasangan paket gabungan v3

Paket `tik-laporan-fix-20261026-v3.tar.gz` menyertakan perbaikan cetak ini beserta perbaikan ekstraksi teks, tindak lanjut, penamaan kegiatan, dan dialog login sebelumnya. Gunakan pada versi TIK yang telah diperbarui ke Laravel 12 dan Tailwind 4.

Unggah ke `/tmp` server, lalu sesuaikan lokasi root aplikasi:

```bash
tar -xzf /tmp/tik-laporan-fix-20261026-v3.tar.gz -C /var/www/tik
cd /var/www/tik
sudo -u www-data php artisan view:clear
```

Jika OPcache tidak memeriksa perubahan file, muat ulang layanan PHP-FPM yang melayani aplikasi. Paket sudah berisi hasil build frontend. Tidak diperlukan migrasi database. Perbaikan belum diterapkan ke server dari workspace ini.
