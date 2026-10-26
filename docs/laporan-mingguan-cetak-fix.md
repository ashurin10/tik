# Perapian cetak Laporan Mingguan — 26 Oktober 2026

Tabel sekarang menggunakan total lebar kolom 100%, dengan ruang lebih besar untuk hasil dan tindak lanjut. Ukuran A4 landscape, margin 12 mm, huruf tabel 9,5 pt, dan jarak antarbaris disesuaikan agar ringkas serta tetap terbaca.

Seluruh kegiatan mengalir dalam satu tabel. Kepala tabel berulang pada halaman lanjutan. Saat mencetak, hanya kegiatan terakhir yang diikat bersama blok tanda tangan. Jika baris terakhir terlalu tinggi, browser mengukur ruang A4 dan memindahkan bagian akhir teksnya ke baris lanjutan bersama tanda tangan. Seluruh karakter tetap dipertahankan; nomor kegiatan tidak diduplikasi. Tanda tangan tetap utuh dan tidak menjadi satu halaman tersendiri. Pengukuran ini dijalankan otomatis sebelum dialog cetak, termasuk saat mencetak ulang.

Placeholder `${ttd_pengirim}` diawali tepat empat spasi yang dipertahankan saat dicetak. Ruang kosong di atas dan bawahnya masing-masing setara tiga baris teks (mengikuti tinggi baris), menggantikan jarak lama 8 mm. Pengukuran pemenggalan halaman otomatis memperhitungkan tinggi tanda tangan yang baru.

Validasi cetak Chromium:

- Data uji 46 kegiatan: 9 halaman sebelum perbaikan menjadi 5 halaman sesudahnya, termasuk tanda tangan.
- Seluruh nomor 1–46 muncul tepat sekali dan seluruh teks berada di dalam margin cetak.
- Laporan kosong, 1, 2, dan 3 kegiatan tetap rapi dalam satu halaman.
- Laporan tanpa penandatangan serta satu uraian yang lebih panjang dari satu halaman diperiksa; isi panjang tetap lengkap dan blok tanda tangan tidak terpotong.
- Regresi tanda tangan diperiksa pada 42 PDF: jumlah kegiatan 0–15 dan 46, berbagai panjang uraian terakhir, uraian sangat panjang, tanpa penandatangan, cetak ulang, dan layar sempit. Pada seluruh laporan bertanda tangan, halaman tanda tangan juga memuat kegiatan atau teks lanjutannya; seluruh nomor muncul sekali, tanpa halaman kosong atau teks keluar margin.
- Dua tes ekspor / 80 assertion lulus. Jarak placeholder diperiksa di browser: empat spasi utuh dan padding atas/bawah masing-masing tiga kali tinggi baris. PDF untuk kasus batas dan uraian panjang dirender menjadi gambar dan diperiksa tata letaknya.

Jumlah halaman laporan sebenarnya mengikuti panjang isinya. Gunakan A4 landscape, skala 100%, dan matikan header/footer tambahan browser untuk hasil bersih.

## Pemasangan paket gabungan v5

Paket `tik-laporan-fix-20261026-v5.tar.gz` menyertakan pengaturan jarak tanda tangan ini beserta perbaikan cetak, ekstraksi teks, tindak lanjut, penamaan kegiatan, dan dialog login sebelumnya. Gunakan pada versi TIK yang telah diperbarui ke Laravel 12 dan Tailwind 4.

Unggah ke `/tmp` server, lalu sesuaikan lokasi root aplikasi:

```bash
tar -xzf /tmp/tik-laporan-fix-20261026-v5.tar.gz -C /var/www/tik
cd /var/www/tik
sudo -u www-data php artisan view:clear
```

Jika OPcache tidak memeriksa perubahan file, muat ulang layanan PHP-FPM yang melayani aplikasi. Paket sudah berisi hasil build frontend. Tidak diperlukan migrasi database. Perbaikan belum diterapkan ke server dari workspace ini.
