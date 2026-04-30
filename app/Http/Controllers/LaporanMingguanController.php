<?php

namespace App\Http\Controllers;

use App\Models\LaporanMingguan;
use App\Services\LaporanMingguanService;
use App\Http\Requests\StoreLaporanMingguanRequest;
use App\Http\Requests\UpdateLaporanMingguanRequest;
use App\Http\Requests\ExportLaporanMingguanRequest;
use Illuminate\Http\Request;

class LaporanMingguanController extends Controller
{
    protected $laporanService;

    public function __construct(LaporanMingguanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index()
    {
        $laporans = LaporanMingguan::orderBy('tanggal', 'desc')->get();
        $duplicateKeys = $this->laporanService->getDuplicateKeys($laporans);

        return view('laporan-mingguan.index', compact('laporans', 'duplicateKeys'));
    }

    public function dashboard()
    {
        $stats = $this->laporanService->getDashboardStats();
        return view('laporan-mingguan.dashboard', $stats);
    }

    public function store(StoreLaporanMingguanRequest $request)
    {
        $data = $this->laporanService->prepareDataForStorage($request->validated());
        LaporanMingguan::create($data);

        return redirect()->route('laporan-mingguan.index')->with('success', 'Data laporan berhasil ditambahkan.');
    }

    // Route Model Binding injected via $laporanMingguan
    public function update(UpdateLaporanMingguanRequest $request, LaporanMingguan $laporanMingguan)
    {
        $data = $this->laporanService->prepareDataForStorage($request->validated());
        $laporanMingguan->update($data);

        return redirect()->route('laporan-mingguan.index')->with('success', 'Data laporan berhasil diperbarui.');
    }

    // Route Model Binding injected via $laporanMingguan
    public function destroy(Request $request, LaporanMingguan $laporanMingguan)
    {
        $laporanMingguan->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('laporan-mingguan.index')->with('success', 'Data laporan berhasil dihapus.');
    }

    public function export(ExportLaporanMingguanRequest $request)
    {
        $validated = $request->validated();
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        
        $penandatangan = $request->penandatangan ?? '';
        $jabatan = $request->jabatan ?? '';
        $pangkat = $request->pangkat ?? '';
        $tanggalTtd = $request->tanggal_ttd ?? null;

        $laporans = LaporanMingguan::whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('laporan-mingguan.cetak', compact('laporans', 'startDate', 'endDate', 'penandatangan', 'jabatan', 'pangkat', 'tanggalTtd'));
    }

    public function searchPics(Request $request)
    {
        $suggestions = $this->laporanService->searchPics($request->get('q', ''));
        return response()->json($suggestions);
    }

    public function searchKegiatan(Request $request)
    {
        $kegiatans = $this->laporanService->searchKegiatan($request->get('q', ''));
        return response()->json($kegiatans);
    }

    public function parseText(Request $request)
    {
        $results = $this->laporanService->parseMultipleTexts($request->input('text', ''));
        return response()->json($results);
    }

    public function bulkStore(Request $request)
    {
        $items = $request->input('items', []);
        $count = 0;

        foreach ($items as $item) {
            if (empty($item['tanggal']) || empty($item['nama_kegiatan'])) continue;

            LaporanMingguan::create([
                'tanggal'                   => $item['tanggal'],
                'nama_kegiatan'             => ucfirst($item['nama_kegiatan']),
                'lokasi'                    => $item['lokasi'] ?? '-',
                'prioritas'                 => $item['prioritas'] ?? 'Sedang',
                'status'                    => $item['status'] ?? 'Selesai',
                'hasil_deskripsi'           => $item['hasil_deskripsi'] ?? '',
                'keterangan_tindak_lanjut'  => $item['keterangan_tindak_lanjut'] ?? '',
                'pic'                       => $item['pic'] ?? '',
            ]);
            $count++;
        }

        return response()->json(['success' => true, 'count' => $count]);
    }
}
