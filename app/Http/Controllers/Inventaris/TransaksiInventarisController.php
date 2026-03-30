<?php

namespace App\Http\Controllers\Inventaris;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Http\Requests\StoreTransaksiInventarisRequest;
use App\Services\InventarisService;

class TransaksiInventarisController extends Controller
{
    protected $inventarisService;

    public function __construct(InventarisService $inventarisService)
    {
        $this->inventarisService = $inventarisService;
    }

    public function store(StoreTransaksiInventarisRequest $request, Inventaris $inventaris)
    {
        try {
            $this->inventarisService->storeTransaksi($inventaris, $request->validated());
            return back()->with('success', 'Transaksi berhasil disimpan. Stok telah diperbarui otomatis.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
