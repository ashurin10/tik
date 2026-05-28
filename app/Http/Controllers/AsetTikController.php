<?php

namespace App\Http\Controllers;

use App\Models\AsetTik;
use App\Models\AsetTikKategori;
use App\Models\AsetTikLokasi;
use App\Models\AsetTikPenanggungJawab;
use App\Models\AsetTikRiwayat;
use App\Models\AsetTikTransaksi;
use App\Models\AsetTikVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AsetTikController extends Controller
{
    private array $masterConfig = [
        'kategori' => [
            'model' => AsetTikKategori::class,
            'route' => 'aset-tik.kategori',
            'fields' => ['kode', 'nama', 'deskripsi'],
            'rules' => ['kode' => 'required|string|max:50', 'nama' => 'required|string|max:255', 'deskripsi' => 'nullable|string|max:1000'],
        ],
        'lokasi' => [
            'model' => AsetTikLokasi::class,
            'route' => 'aset-tik.lokasi',
            'fields' => ['kode', 'nama', 'gedung', 'ruangan', 'keterangan'],
            'rules' => ['kode' => 'required|string|max:50', 'nama' => 'required|string|max:255', 'gedung' => 'nullable|string|max:255', 'ruangan' => 'nullable|string|max:255', 'keterangan' => 'nullable|string|max:1000'],
        ],
        'penanggung-jawab' => [
            'model' => AsetTikPenanggungJawab::class,
            'route' => 'aset-tik.penanggung-jawab',
            'fields' => ['nama', 'unit_kerja'],
            'rules' => ['nama' => 'required|string|max:255', 'unit_kerja' => 'nullable|string|max:255'],
        ],
        'vendor' => [
            'model' => AsetTikVendor::class,
            'route' => 'aset-tik.vendor',
            'fields' => ['nama', 'alamat', 'kontak', 'pic'],
            'rules' => ['nama' => 'required|string|max:255', 'alamat' => 'nullable|string|max:1000', 'kontak' => 'nullable|string|max:255', 'pic' => 'nullable|string|max:255'],
        ],
    ];

    private array $transactionConfig = [
        'aset-masuk' => [
            'title' => 'Aset Masuk',
            'jenis' => ['Pengadaan', 'Hibah', 'Mutasi masuk'],
            'route' => 'aset-tik.aset-masuk',
        ],
        'aset-keluar' => [
            'title' => 'Aset Keluar',
            'jenis' => ['Mutasi keluar', 'Pemindahan unit'],
            'route' => 'aset-tik.aset-keluar',
        ],
        'mutasi' => [
            'title' => 'Mutasi Aset',
            'jenis' => ['Mutasi lokasi', 'Mutasi unit kerja'],
            'route' => 'aset-tik.mutasi',
        ],
        'maintenance' => [
            'title' => 'Maintenance Aset',
            'jenis' => ['Preventive', 'Corrective'],
            'route' => 'aset-tik.maintenance',
        ],
        'penghapusan' => [
            'title' => 'Penghapusan Aset',
            'jenis' => ['Rusak berat', 'Hilang', 'Usang', 'Penghapusan resmi'],
            'route' => 'aset-tik.penghapusan',
        ],
    ];

    private array $pages = [
        'dashboard' => [
            'title' => 'Dashboard Aset',
            'description' => 'Monitoring ringkas total aset, kondisi, status, dan grafik pergerakan aset.',
            'icon' => 'fas fa-chart-line',
        ],
        'kategori' => [
            'title' => 'Kategori Aset',
            'description' => 'Klasifikasi aset seperti server, laptop, PC, printer, switch, router, CCTV, UPS, dan perangkat jaringan.',
            'icon' => 'fas fa-tags',
        ],
        'data-aset' => [
            'title' => 'Data Aset TIK',
            'description' => 'Pencatatan kode aset, identitas perangkat, spesifikasi, nilai, kondisi, lokasi, penanggung jawab, foto, dan QR/barcode.',
            'icon' => 'fas fa-laptop',
        ],
        'lokasi' => [
            'title' => 'Lokasi Aset',
            'description' => 'Data lokasi aset berdasarkan kode lokasi, gedung, ruangan, dan keterangan.',
            'icon' => 'fas fa-map-marker-alt',
        ],
        'penanggung-jawab' => [
            'title' => 'Penanggung Jawab',
            'description' => 'Daftar penanggung jawab aset dan unit kerja terkait.',
            'icon' => 'fas fa-user-tie',
        ],
        'vendor' => [
            'title' => 'Vendor/Supplier',
            'description' => 'Data vendor, alamat, kontak, dan PIC untuk pengadaan atau maintenance.',
            'icon' => 'fas fa-building',
        ],
        'aset-masuk' => [
            'title' => 'Aset Masuk',
            'description' => 'Pencatatan aset dari pengadaan, hibah, atau mutasi masuk beserta dokumen pendukung.',
            'icon' => 'fas fa-sign-in-alt',
        ],
        'aset-keluar' => [
            'title' => 'Aset Keluar',
            'description' => 'Pencatatan mutasi keluar, penghapusan, atau pemindahan aset antar unit.',
            'icon' => 'fas fa-sign-out-alt',
        ],
        'mutasi' => [
            'title' => 'Mutasi Aset',
            'description' => 'Perpindahan aset antar lokasi atau unit kerja beserta riwayat mutasinya.',
            'icon' => 'fas fa-exchange-alt',
        ],
        'maintenance' => [
            'title' => 'Maintenance Aset',
            'description' => 'Monitoring preventive dan corrective maintenance, biaya, vendor teknis, dan kondisi akhir.',
            'icon' => 'fas fa-tools',
        ],
        'penghapusan' => [
            'title' => 'Penghapusan Aset',
            'description' => 'Penghapusan aset karena rusak berat, hilang, usang, atau dasar penghapusan resmi.',
            'icon' => 'fas fa-trash-alt',
        ],
        'riwayat' => [
            'title' => 'Riwayat Aset',
            'description' => 'Histori aset masuk, lokasi, mutasi, maintenance, dan penghapusan.',
            'icon' => 'fas fa-history',
        ],
        'qr-tracking' => [
            'title' => 'QR/Barcode Tracking',
            'description' => 'Scan aset, tampilkan detail aset, dan verifikasi inventaris.',
            'icon' => 'fas fa-qrcode',
        ],
        'laporan-aset-masuk' => [
            'title' => 'Laporan Aset Masuk',
            'description' => 'Laporan bulanan aset masuk dengan filter bulan, tahun, kategori, dan vendor.',
            'icon' => 'fas fa-file-import',
        ],
        'laporan-aset-keluar' => [
            'title' => 'Laporan Aset Keluar',
            'description' => 'Laporan bulanan aset keluar dengan filter bulan, tahun, jenis transaksi, dan kategori.',
            'icon' => 'fas fa-file-export',
        ],
        'laporan-stok' => [
            'title' => 'Laporan Sisa/Stok Aset',
            'description' => 'Perhitungan sisa aset dari total masuk dikurangi total keluar, termasuk lokasi dan kondisi.',
            'icon' => 'fas fa-boxes',
        ],
        'laporan-terpakai' => [
            'title' => 'Laporan Aset Terpakai',
            'description' => 'Daftar aset terpakai berdasarkan unit kerja, lokasi, kategori, kondisi, dan status.',
            'icon' => 'fas fa-clipboard-list',
        ],
    ];

    public function page(Request $request, string $page = 'dashboard')
    {
        abort_unless(array_key_exists($page, $this->pages), 404);

        $viewData = [
            'page' => $this->pages[$page],
            'pageKey' => $page,
            'summaryCards' => $this->summaryCards(),
            'masterConfig' => $this->masterConfig[$page] ?? null,
            'masterItems' => collect(),
            'asets' => collect(),
            'transactions' => collect(),
            'riwayats' => collect(),
            'reportRows' => collect(),
            'reportType' => null,
            'trackingResult' => null,
            'transactionConfig' => $this->transactionConfig[$page] ?? null,
            'kategoris' => AsetTikKategori::orderBy('nama')->get(),
            'lokasis' => AsetTikLokasi::orderBy('nama')->get(),
            'penanggungJawabs' => AsetTikPenanggungJawab::orderBy('nama')->get(),
            'vendors' => AsetTikVendor::orderBy('nama')->get(),
        ];

        if (isset($this->masterConfig[$page])) {
            $model = $this->masterConfig[$page]['model'];
            $viewData['masterItems'] = $model::latest()->paginate(10);
        }

        if ($page === 'data-aset') {
            $viewData['asets'] = AsetTik::with(['kategori', 'lokasi', 'penanggungJawab'])
                ->latest()
                ->paginate(10);
        }

        if (isset($this->transactionConfig[$page])) {
            $viewData['asets'] = AsetTik::orderBy('kode')->get();
            $viewData['transactions'] = AsetTikTransaksi::with([
                'aset',
                'vendor',
                'lokasiAsal',
                'lokasiTujuan',
                'penanggungJawabBaru',
            ])
                ->where('tipe', $page)
                ->latest()
                ->paginate(10);
        }

        if ($page === 'riwayat') {
            $viewData['riwayats'] = AsetTikRiwayat::with(['aset', 'transaksi'])
                ->latest()
                ->paginate(15);
        }

        if (str_starts_with($page, 'laporan-')) {
            $viewData['reportType'] = $page;
            $viewData['reportRows'] = $this->reportRows($request, $page);
        }

        if ($page === 'qr-tracking' && $request->filled('q')) {
            $viewData['trackingResult'] = AsetTik::with(['kategori', 'lokasi', 'penanggungJawab', 'riwayat.transaksi'])
                ->where('tracking_code', $request->string('q'))
                ->orWhere('kode', $request->string('q'))
                ->orWhere('serial_number', $request->string('q'))
                ->first();
        }

        if ($page === 'dashboard') {
            $viewData['summaryCards'] = $this->summaryCards();
        }

        return view('aset-tik.page', $viewData);
    }

    public function storeMaster(Request $request, string $type)
    {
        $config = $this->masterConfig[$type] ?? abort(404);
        $config['model']::create($this->validatedMasterData($request, $type));

        return redirect()->route($config['route'])->with('status', 'Data berhasil ditambahkan.');
    }

    public function updateMaster(Request $request, string $type, string $item)
    {
        $config = $this->masterConfig[$type] ?? abort(404);
        $model = (new $config['model'])->resolveRouteBinding($item);
        abort_unless($model, 404);

        $model->update($this->validatedMasterData($request, $type, $model->id));

        return redirect()->route($config['route'])->with('status', 'Data berhasil diperbarui.');
    }

    public function destroyMaster(string $type, string $item)
    {
        $config = $this->masterConfig[$type] ?? abort(404);
        $model = (new $config['model'])->resolveRouteBinding($item);
        abort_unless($model, 404);

        $model->delete();

        return redirect()->route($config['route'])->with('status', 'Data berhasil dihapus.');
    }

    public function storeAset(Request $request)
    {
        AsetTik::create($this->validatedAsetData($request));

        return redirect()->route('aset-tik.data-aset')->with('status', 'Aset berhasil ditambahkan.');
    }

    public function updateAset(Request $request, AsetTik $aset)
    {
        $aset->update($this->validatedAsetData($request, $aset->id));

        return redirect()->route('aset-tik.data-aset')->with('status', 'Aset berhasil diperbarui.');
    }

    public function destroyAset(AsetTik $aset)
    {
        $aset->delete();

        return redirect()->route('aset-tik.data-aset')->with('status', 'Aset berhasil dihapus.');
    }

    public function labelAset(AsetTik $aset)
    {
        $aset->load(['kategori', 'lokasi', 'penanggungJawab']);

        return view('aset-tik.label', compact('aset'));
    }

    public function storeTransaksi(Request $request, string $type)
    {
        $config = $this->transactionConfig[$type] ?? abort(404);
        $data = $this->validatedTransactionData($request, $type);

        DB::transaction(function () use ($data, $type, $config) {
            $transaction = AsetTikTransaksi::create($data + [
                'tipe' => $type,
                'created_by' => auth()->id(),
            ]);

            $aset = AsetTik::findOrFail($data['aset_tik_id']);
            $this->applyTransactionToAsset($aset, $transaction);

            AsetTikRiwayat::create([
                'aset_tik_id' => $aset->id,
                'transaksi_id' => $transaction->id,
                'aktivitas' => $config['title'],
                'tanggal' => $transaction->tanggal,
                'keterangan' => $transaction->keterangan,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route($config['route'])->with('status', 'Transaksi berhasil dicatat.');
    }

    public function destroyTransaksi(AsetTikTransaksi $transaksi)
    {
        $route = $this->transactionConfig[$transaksi->tipe]['route'] ?? 'aset-tik.dashboard';
        $transaksi->delete();

        return redirect()->route($route)->with('status', 'Transaksi berhasil dihapus.');
    }

    private function validatedMasterData(Request $request, string $type, ?int $ignoreId = null): array
    {
        $config = $this->masterConfig[$type] ?? abort(404);
        $rules = $config['rules'];

        if (isset($rules['kode'])) {
            $table = (new $config['model'])->getTable();
            $rules['kode'] = ['required', 'string', 'max:50', Rule::unique($table, 'kode')->ignore($ignoreId)];
        }

        return $request->validate($rules);
    }

    private function validatedAsetData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:100', Rule::unique('aset_tiks', 'kode')->ignore($ignoreId)],
            'tracking_code' => ['nullable', 'string', 'max:100', Rule::unique('aset_tiks', 'tracking_code')->ignore($ignoreId)],
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['nullable', 'exists:aset_tik_kategoris,id'],
            'merk' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'spesifikasi' => ['nullable', 'string', 'max:2000'],
            'tahun_perolehan' => ['nullable', 'integer', 'min:1980', 'max:' . ((int) date('Y') + 1)],
            'nilai' => ['nullable', 'numeric', 'min:0'],
            'kondisi' => ['required', Rule::in(['Baik', 'Rusak ringan', 'Rusak berat'])],
            'status' => ['required', Rule::in(['Aktif', 'Maintenance', 'Rusak', 'Dihapus'])],
            'lokasi_id' => ['nullable', 'exists:aset_tik_lokasis,id'],
            'penanggung_jawab_id' => ['nullable', 'exists:aset_tik_penanggung_jawabs,id'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function validatedTransactionData(Request $request, string $type): array
    {
        return $request->validate([
            'nomor' => ['required', 'string', 'max:100', Rule::unique('aset_tik_transaksis', 'nomor')],
            'tanggal' => ['required', 'date'],
            'aset_tik_id' => ['required', 'exists:aset_tiks,id'],
            'jenis' => ['nullable', 'string', 'max:255'],
            'vendor_id' => ['nullable', 'exists:aset_tik_vendors,id'],
            'lokasi_asal_id' => ['nullable', 'exists:aset_tik_lokasis,id'],
            'lokasi_tujuan_id' => [Rule::requiredIf(in_array($type, ['mutasi', 'aset-keluar'], true)), 'nullable', 'exists:aset_tik_lokasis,id'],
            'penanggung_jawab_baru_id' => ['nullable', 'exists:aset_tik_penanggung_jawabs,id'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'kondisi_akhir' => ['nullable', Rule::in(['Baik', 'Rusak ringan', 'Rusak berat'])],
            'dokumen' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function applyTransactionToAsset(AsetTik $aset, AsetTikTransaksi $transaction): void
    {
        $updates = [];

        if (in_array($transaction->tipe, ['aset-masuk', 'aset-keluar', 'mutasi'], true) && $transaction->lokasi_tujuan_id) {
            $updates['lokasi_id'] = $transaction->lokasi_tujuan_id;
        }

        if ($transaction->penanggung_jawab_baru_id) {
            $updates['penanggung_jawab_id'] = $transaction->penanggung_jawab_baru_id;
        }

        if ($transaction->kondisi_akhir) {
            $updates['kondisi'] = $transaction->kondisi_akhir;
        }

        if ($transaction->tipe === 'maintenance') {
            $updates['status'] = $transaction->kondisi_akhir === 'Rusak berat' ? 'Rusak' : 'Aktif';
        }

        if ($transaction->tipe === 'penghapusan') {
            $updates['status'] = 'Dihapus';
        }

        if ($updates !== []) {
            $aset->update($updates);
        }
    }

    private function reportRows(Request $request, string $page)
    {
        if (in_array($page, ['laporan-aset-masuk', 'laporan-aset-keluar'], true)) {
            $types = $page === 'laporan-aset-masuk'
                ? ['aset-masuk']
                : ['aset-keluar', 'penghapusan'];

            return AsetTikTransaksi::with(['aset.kategori', 'vendor', 'lokasiTujuan'])
                ->whereIn('tipe', $types)
                ->when($request->filled('bulan'), fn($query) => $query->whereMonth('tanggal', $request->integer('bulan')))
                ->when($request->filled('tahun'), fn($query) => $query->whereYear('tanggal', $request->integer('tahun')))
                ->when($request->filled('kategori_id'), function ($query) use ($request) {
                    $query->whereHas('aset', fn($assetQuery) => $assetQuery->where('kategori_id', $request->integer('kategori_id')));
                })
                ->latest('tanggal')
                ->paginate(15)
                ->withQueryString();
        }

        $query = AsetTik::with(['kategori', 'lokasi', 'penanggungJawab'])
            ->when($request->filled('kategori_id'), fn($builder) => $builder->where('kategori_id', $request->integer('kategori_id')))
            ->when($request->filled('lokasi_id'), fn($builder) => $builder->where('lokasi_id', $request->integer('lokasi_id')))
            ->when($request->filled('status'), fn($builder) => $builder->where('status', $request->string('status')));

        if ($page === 'laporan-terpakai') {
            $query->whereNot('status', 'Dihapus');
        }

        return $query->orderBy('kode')->paginate(15)->withQueryString();
    }

    private function summaryCards(): array
    {
        $total = AsetTik::count();

        return [
            ['label' => 'Total Aset', 'value' => (string) $total, 'icon' => 'fas fa-cubes'],
            ['label' => 'Aset Aktif', 'value' => (string) AsetTik::where('status', 'Aktif')->count(), 'icon' => 'fas fa-check-circle'],
            ['label' => 'Maintenance', 'value' => (string) AsetTik::where('status', 'Maintenance')->count(), 'icon' => 'fas fa-tools'],
            ['label' => 'Rusak', 'value' => (string) AsetTik::where('status', 'Rusak')->count(), 'icon' => 'fas fa-exclamation-triangle'],
        ];
    }
}
