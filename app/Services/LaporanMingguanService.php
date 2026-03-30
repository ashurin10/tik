<?php

namespace App\Services;

use App\Models\LaporanMingguan;
use Carbon\Carbon;

class LaporanMingguanService
{
    public function getDuplicateKeys($laporans)
    {
        return $laporans
            ->groupBy(fn($l) => implode('|', [
                $l->tanggal,
                $l->nama_kegiatan,
                $l->lokasi,
                $l->hasil_deskripsi,
                $l->prioritas,
                $l->pic,
                $l->status,
                $l->keterangan_tindak_lanjut
            ]))
            ->filter(fn($group) => $group->count() > 1)
            ->keys()
            ->toArray();
    }

    public function prepareDataForStorage(array $validatedData): array
    {
        $validatedData['pic'] = implode(', ', $validatedData['pic']);
        $validatedData['nama_kegiatan'] = ucfirst($validatedData['nama_kegiatan']);
        return $validatedData;
    }

    public function getDashboardStats()
    {
        $allLaporans = LaporanMingguan::orderBy('tanggal', 'desc')->get();

        $totalKegiatan = $allLaporans->count();

        $statusSelesai = $allLaporans->where('status', 'Selesai')->count();
        $statusBerjalan = $allLaporans->where('status', 'Berjalan')->count();
        $statusTertunda = $allLaporans->where('status', 'Tertunda')->count();

        $prioritasTinggi = $allLaporans->where('prioritas', 'Tinggi')->count();
        $prioritasSedang = $allLaporans->where('prioritas', 'Sedang')->count();
        $prioritasRendah = $allLaporans->where('prioritas', 'Rendah')->count();

        $picCounts = [];
        foreach ($allLaporans as $lap) {
            $pics = array_map('trim', explode(',', $lap->pic));
            foreach ($pics as $pic) {
                if (!empty($pic)) {
                    $picCounts[$pic] = ($picCounts[$pic] ?? 0) + 1;
                }
            }
        }
        arsort($picCounts);
        $topPics = array_slice($picCounts, 0, 5, true);

        $lokasiCounts = $allLaporans->groupBy('lokasi')->map(function ($group) {
            return $group->count();
        })->sortDesc()->take(5);

        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');
            $label = $date->locale('id')->isoFormat('MMM Y');
            $count = $allLaporans->filter(function ($l) use ($month) {
                return Carbon::parse($l->tanggal)->format('Y-m') === $month;
            })->count();
            $monthlyTrend[] = ['label' => $label, 'count' => $count];
        }

        $weeklyTrend = [];
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();
            $label = $startOfWeek->locale('id')->isoFormat('D MMM') . ' - ' . $endOfWeek->locale('id')->isoFormat('D MMM');
            $count = $allLaporans->filter(function ($l) use ($startOfWeek, $endOfWeek) {
                $t = Carbon::parse($l->tanggal);
                return $t->between($startOfWeek, $endOfWeek);
            })->count();
            $weeklyTrend[] = ['label' => $label, 'count' => $count];
        }

        $recentActivities = $allLaporans->take(5);
        $completionRate = $totalKegiatan > 0 ? round(($statusSelesai / $totalKegiatan) * 100) : 0;

        return compact(
            'totalKegiatan', 'statusSelesai', 'statusBerjalan', 'statusTertunda',
            'prioritasTinggi', 'prioritasSedang', 'prioritasRendah',
            'topPics', 'lokasiCounts', 'monthlyTrend', 'weeklyTrend',
            'recentActivities', 'completionRate'
        );
    }

    public function searchPics(string $query)
    {
        if (empty($query)) return [];

        $reports = LaporanMingguan::where('pic', 'like', '%' . $query . '%')->get(['pic']);
        $allPics = [];
        foreach ($reports as $report) {
            $parts = array_map('trim', explode(',', $report->pic));
            foreach ($parts as $part) {
                if (stripos($part, $query) !== false && !empty($part)) {
                    $allPics[] = $part;
                }
            }
        }

        $uniquePics = array_values(array_unique($allPics));
        return array_slice($uniquePics, 0, 10);
    }

    public function searchKegiatan(string $query)
    {
        if (empty($query)) return [];

        return LaporanMingguan::where('nama_kegiatan', 'like', '%' . $query . '%')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->unique('nama_kegiatan')
            ->take(10)
            ->values();
    }

    public function parseText(string $text)
    {
        $result = [
            'tanggal' => '',
            'nama_kegiatan' => '',
            'lokasi' => '',
            'hasil_deskripsi' => '',
            'prioritas' => 'Sedang',
            'pic' => '',
            'status' => 'Selesai',
            'keterangan_tindak_lanjut' => ''
        ];

        if (empty(trim($text))) {
            return $result;
        }

        $monthsId = ['januari'=>'january','februari'=>'february','maret'=>'march','april'=>'april','mei'=>'may','juni'=>'june','juli'=>'july','agustus'=>'august','september'=>'september','oktober'=>'october','november'=>'november','desember'=>'december', 'jan'=>'jan','feb'=>'feb','mar'=>'mar','apr'=>'apr','jun'=>'jun','jul'=>'jul','agu'=>'aug','agt'=>'aug','sep'=>'sep','okt'=>'oct','nov'=>'nov','des'=>'dec'];
        
        if (preg_match('/(?:hari\s+ini)/i', $text)) {
            $result['tanggal'] = date('Y-m-d');
        } elseif (preg_match('/(?:kemarin)/i', $text)) {
            $result['tanggal'] = date('Y-m-d', strtotime('-1 day'));
        } elseif (preg_match('/(\d{1,2}[\-\/\s]{1,3}(?:[A-Za-z]+|\d{1,2})[\-\/\s]{1,3}\d{2,4}|\d{4}[\-\/\s]{1,3}\d{1,2}[\-\/\s]{1,3}\d{1,2})/', $text, $matches)) {
            try {
                $dateStr = strtolower(str_replace('/', '-', $matches[1]));
                $dateStr = strtr($dateStr, $monthsId);
                $parsed = strtotime($dateStr);
                if ($parsed) {
                    $result['tanggal'] = date('Y-m-d', $parsed);
                }
            } catch (\Exception $e) {}
        }
        
        if (empty($result['tanggal'])) {
            $result['tanggal'] = date('Y-m-d');
        }
        
        if (preg_match('/\b(selesai|berjalan|tertunda)\b/i', $text, $matches)) {
            $result['status'] = ucfirst(strtolower($matches[1]));
        }

        if (preg_match('/\b(tinggi|sedang|rendah)\b/i', $text, $matches)) {
            $result['prioritas'] = ucfirst(strtolower($matches[1]));
        }

        $picFound = [];
        $existingPicsRaw = LaporanMingguan::pluck('pic')->toArray();
        $allExistingPics = [];
        foreach ($existingPicsRaw as $rawPic) {
            $parts = array_map('trim', explode(',', $rawPic));
            foreach ($parts as $p) {
                if (!empty($p) && strlen($p) > 2) {
                    $allExistingPics[] = strtolower($p);
                }
            }
        }
        $allExistingPics = array_unique($allExistingPics);
        
        $textLower = strtolower($text);
        foreach ($allExistingPics as $knownPic) {
            if (preg_match('/\b' . preg_quote($knownPic, '/') . '\b/i', $textLower)) {
                $picFound[] = ucwords($knownPic);
            }
        }
        
        if (count($picFound) > 0) {
            $result['pic'] = implode(', ', $picFound);
        } elseif (preg_match('/(?:pic|oleh|dikerjakan|staff)\s*[:=\-]?\s*([A-Za-z0-9,\s&]+)(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['pic'] = trim($matches[1]);
        }

        if (preg_match('/(?:lokasi|gedung|ruang|lantai)\s*[:=\-]?\s*([A-Za-z0-9\s]+)(?:\.|,|\n|$)/i', $text, $matches) || preg_match('/di\s+([A-Za-z0-9\s]{3,20})(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['lokasi'] = trim($matches[0]);
        }

        if (preg_match('/(?:kegiatan|pekerjaan)\s*[:=\-]?\s*([A-Za-z0-9\s]+)(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['nama_kegiatan'] = ucfirst(trim($matches[1]));
        } else {
            $lines = explode("\n", str_replace(['.', ','], "\n", $text));
            $first = trim($lines[0] ?? '');
            if (strlen($first) > 3) {
                $result['nama_kegiatan'] = ucfirst(substr($first, 0, 100));
            }
        }

        if (preg_match('/(?:hasil|deskripsi|keterangan)\s*[:=\-]?\s*(.*)/is', $text, $matches)) {
            $result['hasil_deskripsi'] = trim($matches[1]);
        } else {
            $result['hasil_deskripsi'] = $text;
        }

        return $result;
    }
}
