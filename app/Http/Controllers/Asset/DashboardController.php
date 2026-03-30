<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\AsetTik;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Utama
        $totalAset = AsetTik::count();
        $asetKeluarHariIni = 0; // Placeholder until mutasi table exists
        $asetJatuhTempo = 0; // Placeholder until peminjaman table exists
        $asetDipinjam = AsetTik::where('status', 'Dipinjam')->count();
        $asetRusak = AsetTik::where('kondisi', 'Rusak')->count();

        // Distribusi Kategori
        $kategoriDist = AsetTik::select('kategori', \DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->get();

        // Aset Terbaru (5 item)
        $asetTerbaru = AsetTik::latest()->take(5)->get();

        return view('aset.dashboard', compact(
            'totalAset',
            'asetKeluarHariIni',
            'asetJatuhTempo',
            'asetDipinjam',
            'asetRusak',
            'kategoriDist',
            'asetTerbaru'
        ));
    }
    public function kategoriPlaceholder()
    {
        return view('aset.placeholder', ['title' => 'Kategori & Lokasi', 'icon' => 'fa-tags']);
    }
}
