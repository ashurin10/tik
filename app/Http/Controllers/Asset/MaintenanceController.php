<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function jadwal()
    {
        return view('aset.placeholder', [
            'title' => 'Jadwal Maintenance',
            'icon' => 'fa-calendar-check'
        ]);
    }

    public function kondisi()
    {
        return view('aset.placeholder', [
            'title' => 'Kondisi & Depresiasi Aset',
            'icon' => 'fa-heartbeat'
        ]);
    }
}
