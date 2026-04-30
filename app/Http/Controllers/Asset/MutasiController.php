<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\AsetTik;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiPeminjaman;
use App\Services\Asset\MasterAsetService;
use App\Services\UniqueCodeService;
use App\Http\Requests\Asset\StoreMasterAsetRequest;
use Illuminate\Http\Request;

class MutasiController extends Controller
{
    public function penerimaan()
    {
        $riwayatPenerimaan = TransaksiMasuk::latest()->get();
        return view('aset.mutasi.penerimaan.index', compact('riwayatPenerimaan'));
    }

    public function createPenerimaan()
    {
        return view('aset.mutasi.penerimaan.create');
    }

    public function storePenerimaanAset(StoreMasterAsetRequest $request, MasterAsetService $masterAsetService)
    {
        try {
            // Validasi tambahan untuk penerimaan
            $request->validate([
                'sumber_aset' => 'required|string',
                'tanggal_masuk' => 'required|date',
                'diterima_oleh' => 'required|string',
            ]);

            // 1. Create the Asset using existing service
            $asset = $masterAsetService->createAsset($request->validated());

            // 2. Create the Penerimaan (TransaksiMasuk) logging
            $noTransaksi = UniqueCodeService::generate('TM', 'transaksi_masuk');
            TransaksiMasuk::create([
                'no_transaksi' => $noTransaksi,
                'tanggal_masuk' => $request->tanggal_masuk,
                'sumber_aset' => $request->sumber_aset,
                'diterima_oleh' => $request->diterima_oleh,
                'keterangan' => 'Registrasi Aset Baru: ' . $asset->nama_aset . ' (' . $asset->kode_aset . ')'
            ]);

            return redirect()->route('aset.mutasi.penerimaan.index')->with('success', 'Aset Masuk berhasil ditambahkan dan dicatat di inventaris: ' . $asset->kode_aset);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan aset masuk: ' . $e->getMessage())->withInput();
        }
    }

    public function importPenerimaan(Request $request, MasterAsetService $masterAsetService)
    {
        $request->validate([
            'sumber_aset' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'diterima_oleh' => 'required|string',
            'file_import' => 'required|mimes:csv,txt|max:5120'
        ]);

        try {
            // Import assets using existing service
            $importedCount = $masterAsetService->importCsv($request->file('file_import'));

            // Create 1 aggregate record in Transaksi Masuk
            $noTransaksi = UniqueCodeService::generate('TM', 'transaksi_masuk');
            TransaksiMasuk::create([
                'no_transaksi' => $noTransaksi,
                'tanggal_masuk' => $request->tanggal_masuk,
                'sumber_aset' => $request->sumber_aset,
                'diterima_oleh' => $request->diterima_oleh,
                'keterangan' => 'Import Bulk (' . $importedCount . ' Aset Baru)'
            ]);

            return redirect()->route('aset.mutasi.penerimaan.index')->with('success', "Sukses! $importedCount Aset Masuk berhasil diimpor & dicatat riwayatnya.");
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return back()->with('error', 'Gagal memproses file impor: Terdapat bentrokan Kode Aset yang sudah ada (Duplikat). Pastikan data tidak diimpor dua kali.');
            }
            return back()->with('error', 'Terjadi kesalahan sistem database saat menyimpan data. Hubungi administrator.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file import. Pastikan format CSV sesuai dan tidak ada data yang korup.');
        }
    }

    public function checkout()
    {
        $assets = AsetTik::where('status', 'Aktif')->get();
        $riwayatCheckout = TransaksiPeminjaman::where('status', 'Dipinjam')->latest()->get();
        return view('aset.mutasi.checkout.index', compact('assets', 'riwayatCheckout'));
    }

    public function storeCheckout(Request $request)
    {
        $request->validate([
            'aset_id' => 'required|exists:aset_tik,id',
            'nama_peminjam' => 'required|string',
            'tanggal_pinjam' => 'required|date',
        ]);

        $aset = AsetTik::findOrFail($request->aset_id);
        if ($aset->status !== 'Aktif') {
            return back()->with('error', 'Aset tidak tersedia untuk didistribusikan.');
        }

        $noPinjam = UniqueCodeService::generate('OUT', 'transaksi_peminjaman', 'no_peminjaman');

        TransaksiPeminjaman::create([
            'no_peminjaman' => $noPinjam,
            'aset_id' => $request->aset_id,
            'nama_peminjam' => $request->nama_peminjam,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'rencana_kembali' => null,
            'status' => 'Dipinjam',
        ]);

        // Update status master aset menjadi Terpakai, dan otomatis mengubah Unit Pengguna/Lokasinya sesuai tujuan
        $aset->update([
            'status' => 'Terpakai',
            'unit_pengguna' => $request->nama_peminjam,
            'lokasi' => $request->nama_peminjam
        ]);

        return redirect()->route('aset.mutasi.checkout.index')->with('success', 'Berhasil! Aset Keluar telah didistribusikan dan Daftar Master Aset telah terupdate otomatis.');
    }

    public function checkin()
    {
        // Get all currently borrowed active records
        $peminjaman = TransaksiPeminjaman::with('aset')->where('status', 'Dipinjam')->get();
        $riwayatCheckin = TransaksiPeminjaman::with('aset')->where('status', 'Dikembalikan')->latest()->limit(50)->get();
        return view('aset.mutasi.checkin.index', compact('peminjaman', 'riwayatCheckin'));
    }

    public function storeCheckin(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:transaksi_peminjaman,id',
            'tanggal_kembali' => 'required|date',
            'kondisi_saat_kembali' => 'required|string',
        ]);

        $transaksi = TransaksiPeminjaman::findOrFail($request->peminjaman_id);

        $transaksi->update([
            'tanggal_kembali' => $request->tanggal_kembali,
            'kondisi_saat_kembali' => $request->kondisi_saat_kembali,
            'status' => 'Dikembalikan',
        ]);

        $aset = AsetTik::find($transaksi->aset_id);
        if ($aset) {
            // Ketika aset ditarik dari OPD, status kembali Aktif di gudang, dan lokasi di-reset.
            $aset->update([
                'status' => 'Aktif',
                'kondisi' => $request->kondisi_saat_kembali,
                'unit_pengguna' => 'Gudang TIK',
                'lokasi' => 'Gudang Penyimpanan TIK'
            ]);
        }

        return redirect()->route('aset.mutasi.checkin.index')->with('success', 'Aset berhasil ditarik dan Daftar Master Aset telah dikembalikan ke Gudang TIK!');
    }

    public function approval()
    {
        return view('aset.placeholder', [
            'title' => 'Request Approval Mutasi/Peminjaman',
            'icon' => 'fa-clipboard-check'
        ]);
    }
}
