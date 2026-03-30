<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MutasiController extends Controller
{
    public function checkout()
    {
        return view('aset.placeholder', [
            'title' => 'Check-Out (Aset Keluar)',
            'icon' => 'fa-sign-out-alt'
        ]);
    }

    public function checkin()
    {
        return view('aset.placeholder', [
            'title' => 'Check-In (Aset Masuk)',
            'icon' => 'fa-sign-in-alt'
        ]);
    }

    public function approval()
    {
        return view('aset.placeholder', [
            'title' => 'Request Approval Mutasi/Peminjaman',
            'icon' => 'fa-clipboard-check'
        ]);
    }
}
