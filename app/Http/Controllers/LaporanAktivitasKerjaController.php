<?php

namespace App\Http\Controllers;

use App\Models\LaporanAktivitasKerja;
use App\Models\LaporanMingguan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanAktivitasKerjaController extends Controller
{
    private function getAvailablePics()
    {
        $picsRaw = LaporanMingguan::pluck('pic')->toArray();
        $allPics = [];
        foreach ($picsRaw as $rawPic) {
            $parts = array_map('trim', explode(',', $rawPic));
            foreach ($parts as $p) {
                if (!empty($p) && strlen($p) > 2) {
                    $allPics[] = ucwords(strtolower($p));
                }
            }
        }
        $pics = array_unique($allPics);
        sort($pics);
        return $pics;
    }

    private function handleApelPagiAndTimeAdjustment($tanggalStr, $pic, $jamMulaiInput)
    {
        $tanggal = Carbon::parse($tanggalStr);
        $dayOfWeek = $tanggal->dayOfWeekIso; // 1 = Senin, 2 = Selasa, 4 = Kamis, 5 = Jumat
        
        if (in_array($dayOfWeek, [1, 2, 4, 5])) {
            // Geser jam mulai menjadi 08:00 jika awalnya 07:30
            if (substr($jamMulaiInput, 0, 5) == '07:30') {
                $jamMulaiInput = '08:00:00';
            }
            
            $namaKegiatanOtomatis = ($dayOfWeek == 5) ? "Melakukan Jum'at Bersih" : "Melakukan Apel Pagi";
            $searchKeyword = ($dayOfWeek == 5) ? "Jum'at Bersih" : "Apel Pagi";
            
            $kegiatanExists = LaporanAktivitasKerja::where('pic', $pic)
                ->whereDate('tanggal', $tanggal->format('Y-m-d'))
                ->where('uraian_kegiatan', 'like', '%' . $searchKeyword . '%')
                ->exists();
                
            if (!$kegiatanExists) {
                LaporanAktivitasKerja::create([
                    'pic' => $pic,
                    'tanggal' => $tanggal->format('Y-m-d'),
                    'jam_mulai' => '07:30:00',
                    'jam_selesai' => '08:00:00',
                    'uraian_kegiatan' => $namaKegiatanOtomatis,
                    'keterangan' => 'tj',
                ]);
            }
        }
        
        return $jamMulaiInput;
    }

    public function index()
    {
        $pics = $this->getAvailablePics();
        return view('laporan-aktivitas-kerja.index', compact('pics'));
    }

    public function show(Request $request)
    {
        $pic = $request->query('pic');
        $bulanStr = $request->query('bulan'); // Format YYYY-MM
        
        if (!$pic || !$bulanStr) {
            return redirect()->route('laporan-aktivitas-kerja.index')->withErrors(['Pilih PIC dan Bulan terlebih dahulu.']);
        }

        $bulan = Carbon::createFromFormat('Y-m', $bulanStr);
        $month = $bulan->month;
        $year = $bulan->year;

        $laks = LaporanAktivitasKerja::where('pic', $pic)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $pics = $this->getAvailablePics();

        $otherLaporanMingguan = LaporanMingguan::where('pic', '!=', $pic)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal')
            ->get();

        return view('laporan-aktivitas-kerja.show', compact('laks', 'pic', 'bulanStr', 'month', 'year', 'pics', 'otherLaporanMingguan'));
    }

    public function sync(Request $request)
    {
        $pic = $request->input('pic');
        $sourcePic = $request->input('source_pic', $pic); // use source_pic, fallback to pic
        $bulanStr = $request->input('bulan'); // Format YYYY-MM

        if (!$pic || !$bulanStr) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap.']);
        }

        $bulan = Carbon::createFromFormat('Y-m', $bulanStr);
        $month = $bulan->month;
        $year = $bulan->year;

        // Ambil laporan mingguan untuk SOURCE PIC dan bulan tersebut yang belum di-sync
        $laporanMingguans = LaporanMingguan::where('pic', 'like', "%{$sourcePic}%")
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get();

        $syncedCount = 0;
        foreach ($laporanMingguans as $lm) {
            // Cek apakah sudah ada di LAK
            $exists = LaporanAktivitasKerja::where('laporan_mingguan_id', $lm->id)
                ->where('pic', $pic)
                ->exists();

            if (!$exists) {
                $adjustedJamMulai = $this->handleApelPagiAndTimeAdjustment($lm->tanggal, $pic, '07:30:00');
                $jamSelesai = (Carbon::parse($lm->tanggal)->dayOfWeekIso == 5) ? '16:00:00' : '15:30:00';

                LaporanAktivitasKerja::create([
                    'pic' => $pic,
                    'tanggal' => $lm->tanggal,
                    'jam_mulai' => $adjustedJamMulai,
                    'jam_selesai' => $jamSelesai,
                    'uraian_kegiatan' => $lm->nama_kegiatan,
                    'keterangan' => 'tj',
                    'laporan_mingguan_id' => $lm->id,
                ]);
                $syncedCount++;
            }
        }

        return redirect()->route('laporan-aktivitas-kerja.show', ['pic' => $pic, 'bulan' => $bulanStr])
            ->with('success', "Berhasil sinkronisasi $syncedCount kegiatan baru dari Laporan Mingguan.");
    }

    public function reset(Request $request)
    {
        $pic = $request->input('pic');
        $bulanStr = $request->input('bulan');

        if (!$pic || !$bulanStr) {
            return redirect()->back()->withErrors(['Parameter tidak lengkap.']);
        }

        $bulan = Carbon::createFromFormat('Y-m', $bulanStr);
        $month = $bulan->month;
        $year = $bulan->year;

        $deletedRows = LaporanAktivitasKerja::where('pic', $pic)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->delete();

        return redirect()->route('laporan-aktivitas-kerja.show', ['pic' => $pic, 'bulan' => $bulanStr])
            ->with('success', "Berhasil mereset (menghapus) $deletedRows kegiatan LAK pada bulan ini.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pic' => 'required|string',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'uraian_kegiatan' => 'required|string',
            'keterangan' => 'nullable|string',
            'laporan_mingguan_id' => 'nullable|integer',
        ]);

        $validated['keterangan'] = $validated['keterangan'] ?? 'tj';

        // Hanya auto-adjust jam_mulai jika ditambahkan massal dari Laporan Mingguan,
        // namun jika uraian manualnya BUKAN Apel Pagi / Jum'at Bersih, kita bisa sesuaikan.
        if (stripos($validated['uraian_kegiatan'], 'Apel Pagi') === false && stripos($validated['uraian_kegiatan'], "Jum'at Bersih") === false) {
            $validated['jam_mulai'] = $this->handleApelPagiAndTimeAdjustment($validated['tanggal'], $validated['pic'], $validated['jam_mulai']);
        }

        LaporanAktivitasKerja::create($validated);

        $bulanStr = Carbon::parse($validated['tanggal'])->format('Y-m');

        return redirect()->route('laporan-aktivitas-kerja.show', ['pic' => $validated['pic'], 'bulan' => $bulanStr])
            ->with('success', 'Kegiatan berhasil ditambahkan/disalin.');
    }

    public function copyMultiple(Request $request)
    {
        $validated = $request->validate([
            'pic' => 'required|string',
            'bulan' => 'required|string',
            'other_ids' => 'required|array',
            'other_ids.*' => 'integer',
        ]);

        $pic = $validated['pic'];
        $bulanStr = $validated['bulan'];
        $ids = $validated['other_ids'];

        $laporanMingguans = LaporanMingguan::whereIn('id', $ids)->get();
        $syncedCount = 0;

        foreach ($laporanMingguans as $lm) {
            $exists = LaporanAktivitasKerja::where('laporan_mingguan_id', $lm->id)
                ->where('pic', $pic)
                ->exists();

            if (!$exists) {
                $adjustedJamMulai = $this->handleApelPagiAndTimeAdjustment($lm->tanggal, $pic, '07:30:00');
                $jamSelesai = (Carbon::parse($lm->tanggal)->dayOfWeekIso == 5) ? '16:00:00' : '15:30:00';

                LaporanAktivitasKerja::create([
                    'pic' => $pic,
                    'tanggal' => $lm->tanggal,
                    'jam_mulai' => $adjustedJamMulai,
                    'jam_selesai' => $jamSelesai,
                    'uraian_kegiatan' => $lm->nama_kegiatan,
                    'keterangan' => 'tj',
                    'laporan_mingguan_id' => $lm->id,
                ]);
                $syncedCount++;
            }
        }

        return redirect()->route('laporan-aktivitas-kerja.show', ['pic' => $pic, 'bulan' => $bulanStr])
            ->with('success', "Berhasil menyalin $syncedCount kegiatan pilihan.");
    }

    public function update(Request $request, LaporanAktivitasKerja $laporanAktivitasKerja)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'uraian_kegiatan' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $validated['keterangan'] = $validated['keterangan'] ?? 'tj';

        $laporanAktivitasKerja->update($validated);

        $bulanStr = Carbon::parse($laporanAktivitasKerja->tanggal)->format('Y-m');

        return redirect()->route('laporan-aktivitas-kerja.show', ['pic' => $laporanAktivitasKerja->pic, 'bulan' => $bulanStr])
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(LaporanAktivitasKerja $laporanAktivitasKerja)
    {
        $pic = $laporanAktivitasKerja->pic;
        $bulanStr = Carbon::parse($laporanAktivitasKerja->tanggal)->format('Y-m');
        
        $laporanAktivitasKerja->delete();

        return response()->json(['success' => true]);
    }

    public function cetak(Request $request)
    {
        $pic = $request->query('pic');
        $bulanStr = $request->query('bulan');
        
        $jbtn = $request->query('jabatan', 'Manggala Informatika Ahli Pertama');
        $nip = $request->query('nip', '19991010 202504 1 009');
        $pangkat = $request->query('pangkat', 'Penata Muda III/a');
        $unit = $request->query('unit', 'Bidang Teknologi Informasi dan Komunikasi Diskominfo');
        $jml_hari = $request->query('jml_hari', 15);
        $nama_pembuat = $request->query('nama_pembuat', $pic);
        
        $nama_atasan = $request->query('nama_atasan', 'NURDIYANA, S.Pd., M.A.P.');
        $nip_atasan = $request->query('nip_atasan', '19780112 200501 1 012');
        $pangkat_atasan = $request->query('pangkat_atasan', 'Pembina Tk. I IV/b');
        $jabatan_atasan = $request->query('jabatan_atasan', 'Kepala Bidang Teknologi Informasi dan Komunikasi');

        if (!$pic || (!$bulanStr)) {
            abort(404);
        }

        $bulan = Carbon::createFromFormat('Y-m', $bulanStr);
        
        $laks = LaporanAktivitasKerja::where('pic', $pic)
            ->whereMonth('tanggal', $bulan->month)
            ->whereYear('tanggal', $bulan->year)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return view('laporan-aktivitas-kerja.cetak', compact('laks', 'pic', 'bulanStr', 'bulan', 'jbtn', 'nip', 'pangkat', 'unit', 'jml_hari', 'nama_pembuat', 'nama_atasan', 'nip_atasan', 'pangkat_atasan', 'jabatan_atasan'));
    }
}
