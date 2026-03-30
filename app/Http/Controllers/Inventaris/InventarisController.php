<?php

namespace App\Http\Controllers\Inventaris;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Http\Requests\StoreInventarisRequest;
use App\Http\Requests\UpdateInventarisRequest;

class InventarisController extends Controller
{
    public function index()
    {
        $inventaris = Inventaris::latest()->paginate(10);
        return view('inventaris.index', compact('inventaris'));
    }

    public function create()
    {
        return view('inventaris.create');
    }

    public function store(StoreInventarisRequest $request)
    {
        Inventaris::create($request->validated());
        return redirect()->route('inventaris.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Inventaris $inventaris)
    {
        $transaksi = $inventaris->transaksi()->latest()->get();
        return view('inventaris.show', compact('inventaris', 'transaksi'));
    }

    public function edit(Inventaris $inventaris)
    {
        return view('inventaris.edit', compact('inventaris'));
    }

    public function update(UpdateInventarisRequest $request, Inventaris $inventaris)
    {
        $inventaris->update($request->validated());
        return redirect()->route('inventaris.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Inventaris $inventaris)
    {
        $inventaris->delete();
        return redirect()->route('inventaris.index')->with('success', 'Barang berhasil dihapus.');
    }
}
