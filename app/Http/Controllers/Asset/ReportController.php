<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function log()
    {
        return view('aset.placeholder', [
            'title' => 'Log Audit Sistem',
            'icon' => 'fa-history'
        ]);
    }

    public function opname()
    {
        return view('aset.placeholder', [
            'title' => 'Laporan Stok Opname',
            'icon' => 'fa-file-excel'
        ]);
    }

    public function rekap()
    {
        return view('aset.placeholder', [
            'title' => 'Laporan Keluar-Masuk & Mutasi',
            'icon' => 'fa-sync-alt'
        ]);
    }
}
