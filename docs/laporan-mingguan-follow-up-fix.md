# Perbaikan tindak lanjut Laporan Mingguan — 26 Oktober 2026

Ekstrak Teks sebelumnya membuat tindak lanjut dari kata kunci nama kegiatan. Kalimat tersebut dapat tidak sesuai dengan laporan sumber dan tetap tertinggal saat status diubah. Label Tindak Lanjut dan Status juga belum dipisahkan dari kolom di sekitarnya.

Perubahan:

- Tindak lanjut tidak lagi dibuat berdasarkan tebakan kata kunci. Kolom yang tidak disebutkan dibiarkan kosong; kendala pada kegiatan yang sama tetap menjadi fallback jika label tindak lanjut tidak tersedia.
- Label Tindak Lanjut, Keterangan Tindak Lanjut, dan Rencana Tindak Lanjut dikenali, termasuk isi multiline dan laporan satu baris. Nilainya berhenti pada label atau kegiatan berikutnya.
- Status dan prioritas yang tertulis pada laporan terstruktur diutamakan dibanding deteksi otomatis.
- Pilihan riwayat kegiatan tidak menyalin status, hasil, dan tindak lanjut laporan lama ke laporan yang sedang diisi.
- Form edit mengosongkan tiga kalimat generik otomatis lama jika bertentangan dengan status. Isi manual lainnya dipertahankan. Perubahan baru tersimpan setelah pengguna menekan Perbarui Data.
- Kartu hasil ekstraksi menampilkan tindak lanjut agar dapat diperiksa sebelum disimpan.
- Penamaan kegiatan mendahulukan bentuk kegiatan: `Pembuatan formulir laporan` menjadi `Membuat formulir laporan`, dan `Desain header webinar` menjadi `Membuat desain header webinar`. Kata kerja eksplisit seperti `Membuat` dan `Menyusun` tetap dipertahankan. Pencocokan kata kunci nonteknis memakai kata utuh agar `SK`/`RKA` tidak cocok dengan potongan kata lain.

Validasi gabungan: 68 tes / 1.206 assertion lulus; build produksi berhasil. Pemeriksaan browser mencakup status dan kalimat otomatis yang bertentangan, pemeliharaan isi manual, pemilihan riwayat kegiatan, ekstraksi dua kegiatan melalui parser asli, serta pembukaan form tiap hasil pada desktop dan ponsel. Perbaikan penamaan menambahkan 22 pemeriksaan nama pada laporan terstruktur dan teks bebas.

## Pemasangan patch

Unggah `tik-laporan-follow-up-fix-20261026-v2.tar.gz` ke `/tmp` server. Versi ini menyertakan perbaikan tindak lanjut dan penamaan kegiatan. Sesuaikan `/var/www/tik` dengan lokasi aplikasi:

```bash
tar -xzf /tmp/tik-laporan-follow-up-fix-20261026-v2.tar.gz -C /var/www/tik
cd /var/www/tik
sudo -u www-data php artisan view:clear
```

Paket berisi service parser, template laporan, perbaikan dialog login sebelumnya, serta hasil build frontend. Gunakan pada versi TIK yang telah diperbarui ke Laravel 12 dan Tailwind 4. Jika PHP memakai OPcache dengan pemeriksaan timestamp dinonaktifkan, muat ulang layanan PHP-FPM yang melayani aplikasi setelah mengganti file.

Patch tidak menjalankan migrasi atau mengubah database. Data lama dengan tindak lanjut yang keliru tetap perlu diperiksa dan disimpan ulang, atau diekstrak ulang dari teks sumber yang benar.
