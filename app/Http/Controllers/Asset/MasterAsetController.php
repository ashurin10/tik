<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\AsetTik;
use App\Http\Requests\Asset\StoreMasterAsetRequest;
use App\Http\Requests\Asset\UpdateMasterAsetRequest;
use App\Services\Asset\MasterAsetService;
use Illuminate\Http\Request;

class MasterAsetController extends Controller
{
    protected $masterAsetService;

    public function __construct(MasterAsetService $masterAsetService)
    {
        $this->masterAsetService = $masterAsetService;
    }

    public function index(Request $request)
    {
        $assets = $this->masterAsetService->getFilteredAssets($request->search, $request->kategori, $request->status);
        return view('aset.master.index', compact('assets'));
    }

    public function templateImport()
    {
        $callback = $this->masterAsetService->generateTemplateCallback();
        
        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=Template_Import_Aset_TIK.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|mimes:csv,txt|max:5120'
        ]);

        try {
            $importedCount = $this->masterAsetService->importCsv($request->file('file_import'));
            return redirect()->route('aset.master.data.index')->with('success', "Sukses! $importedCount Aset berhasil diimpor.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file import. Pastikan format kolom sesuai dengan template. Error: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('aset.master.create');
    }

    public function store(StoreMasterAsetRequest $request)
    {
        try {
            $asset = $this->masterAsetService->createAsset($request->validated());
            return redirect()->route('aset.master.data.index')->with('success', 'Aset berhasil ditambahkan: ' . $asset->kode_aset);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan aset: ' . $e->getMessage())->withInput();
        }
    }

    public function show(AsetTik $asetTik)
    {
        return view('aset.master.show', ['aset' => $asetTik]);
    }

    public function edit(AsetTik $asetTik)
    {
        return view('aset.master.edit', ['aset' => $asetTik]);
    }

    public function update(UpdateMasterAsetRequest $request, AsetTik $asetTik)
    {
        try {
            $this->masterAsetService->updateAsset($asetTik, $request->validated());
            return redirect()->route('aset.master.data.index')->with('success', 'Data aset berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui aset: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(AsetTik $asetTik)
    {
        try {
            $asetTik->delete();
            return redirect()->route('aset.master.data.index')->with('success', 'Aset berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return back()->with('error', 'Gagal menghapus! Aset ini sedang terkait dengan data transaksi (Peminjaman/Masuk/Maintenance).');
            }
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus aset: ' . $e->getMessage());
        }
    }

    public function printLabel(AsetTik $asetTik)
    {
        return view('aset.master.print-label', ['aset' => $asetTik]);
    }
}
