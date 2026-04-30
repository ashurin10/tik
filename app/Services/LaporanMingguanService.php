<?php

namespace App\Services;

use App\Models\LaporanMingguan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        // Gunakan SQL aggregation alih-alih memuat semua record ke memory
        $totalKegiatan = LaporanMingguan::count();
        $statusSelesai = LaporanMingguan::where('status', 'Selesai')->count();
        $statusBerjalan = LaporanMingguan::where('status', 'Berjalan')->count();
        $statusTertunda = LaporanMingguan::where('status', 'Tertunda')->count();

        $prioritasTinggi = LaporanMingguan::where('prioritas', 'Tinggi')->count();
        $prioritasSedang = LaporanMingguan::where('prioritas', 'Sedang')->count();
        $prioritasRendah = LaporanMingguan::where('prioritas', 'Rendah')->count();

        // Top PICs: query langsung ke database
        $topPics = collect(DB::select("
            SELECT
                TRIM(value) as pic,
                COUNT(*) as count
            FROM laporan_mingguans,
                 json_each(json_array(TRIM(pic)))
            GROUP BY TRIM(value)
            ORDER BY count DESC
            LIMIT 5
        "))->pluck('count', 'pic')->toArray();

        // Fallback jika SQLite tidak mendukung json_each untuk string biasa
        if (empty($topPics)) {
            $topPics = $this->getTopPicsFallback();
        }

        $lokasiCounts = LaporanMingguan::select('lokasi', DB::raw('COUNT(*) as count'))
            ->groupBy('lokasi')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'lokasi');

        $monthlyTrend = $this->getMonthlyTrend();
        $weeklyTrend = $this->getWeeklyTrend();

        $recentActivities = LaporanMingguan::latest('tanggal')->limit(5)->get();
        $completionRate = $totalKegiatan > 0 ? round(($statusSelesai / $totalKegiatan) * 100) : 0;

        return compact(
            'totalKegiatan', 'statusSelesai', 'statusBerjalan', 'statusTertunda',
            'prioritasTinggi', 'prioritasSedang', 'prioritasRendah',
            'topPics', 'lokasiCounts', 'monthlyTrend', 'weeklyTrend',
            'recentActivities', 'completionRate'
        );
    }

    private function getTopPicsFallback(): array
    {
        $pics = LaporanMingguan::pluck('pic');
        $counts = [];

        foreach ($pics as $rawPic) {
            $parts = array_map('trim', explode(',', $rawPic));
            foreach ($parts as $pic) {
                if (!empty($pic)) {
                    $counts[$pic] = ($counts[$pic] ?? 0) + 1;
                }
            }
        }

        arsort($counts);
        return array_slice($counts, 0, 5, true);
    }

    private function getMonthlyTrend(): array
    {
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth()->format('Y-m-d');
            $end = $date->copy()->endOfMonth()->format('Y-m-d');
            $label = $date->locale('id')->isoFormat('MMM Y');

            $count = LaporanMingguan::whereBetween('tanggal', [$start, $end])->count();

            $trend[] = ['label' => $label, 'count' => $count];
        }

        return $trend;
    }

    private function getWeeklyTrend(): array
    {
        $trend = [];

        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();
            $label = $startOfWeek->locale('id')->isoFormat('D MMM') . ' - ' . $endOfWeek->locale('id')->isoFormat('D MMM');

            $count = LaporanMingguan::whereBetween('tanggal', [
                $startOfWeek->format('Y-m-d'),
                $endOfWeek->format('Y-m-d')
            ])->count();

            $trend[] = ['label' => $label, 'count' => $count];
        }

        return $trend;
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

    /**
     * Parse satu atau lebih kegiatan dari teks mentah.
     * Mengembalikan array of activity objects.
     */
    public function parseMultipleTexts(string $text): array
    {
        $blocks = $this->splitIntoBlocks($text);

        $results = [];
        foreach ($blocks as $block) {
            $block = trim($block);
            if (strlen($block) < 5) continue;
            $results[] = $this->parseText($block);
        }

        return $results ?: [$this->parseText($text)];
    }

    /**
     * Pisahkan teks menjadi beberapa blok kegiatan.
     * Strategi 0 (prioritas): format laporan WhatsApp (split per "Tanggal :")
     * Strategi 1: daftar bernomor
     * Strategi 2: bullet/poin
     * Strategi 3: paragraf ganda
     */
    private function splitIntoBlocks(string $text): array
    {
        // Strategi 0: format laporan terstruktur WhatsApp — split sebelum setiap "Tanggal :"
        if (preg_match_all('/(?:^|\n)Tanggal\s*:/im', $text, $m) && count($m[0]) > 1) {
            $parts = preg_split('/(?=\n?Tanggal\s*:)/i', $text, -1, PREG_SPLIT_NO_EMPTY);
            $parts = array_values(array_filter($parts, fn($p) => preg_match('/Tanggal\s*:/i', $p)));
            if (count($parts) > 1) return $parts;
        }

        // Strategi 1: daftar bernomor  "1. ...", "2. ..."
        if (preg_match_all('/(?:^|\n)\s*\d+[\.)\-]\s+/m', $text, $m) && count($m[0]) > 1) {
            $parts = preg_split('/(?:^|\n)\s*\d+[\.)\-]\s+/m', $text, -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) > 1) return $parts;
        }

        // Strategi 2: bullet/poin  "- ...", "• ...", "* ..."
        if (preg_match_all('/(?:^|\n)\s*[-•*]\s+/m', $text, $m) && count($m[0]) > 1) {
            $parts = preg_split('/(?:^|\n)\s*[-•*]\s+/m', $text, -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) > 1) return $parts;
        }

        // Strategi 3: paragraf dipisah baris kosong
        $paragraphs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (count($paragraphs) > 1) {
            return $paragraphs;
        }

        // Fallback: kembalikan sebagai satu blok
        return [$text];
    }

    public function parseText(string $text): array
    {
        $result = [
            'tanggal'                  => '',
            'nama_kegiatan'            => '',
            'lokasi'                   => '',
            'hasil_deskripsi'          => '',
            'prioritas'                => 'Sedang',
            'pic'                      => '',
            'status'                   => 'Selesai',
            'keterangan_tindak_lanjut' => ''
        ];

        if (empty(trim($text))) {
            return $result;
        }

        // Prioritas utama: format laporan terstruktur WhatsApp
        if (preg_match('/Tanggal\s*:/i', $text)) {
            return $this->parseStructuredReport($text);
        }

        // --- Parsing teks bebas (fallback) ---
        $monthsId = ['januari'=>'january','februari'=>'february','maret'=>'march','april'=>'april','mei'=>'may','juni'=>'june','juli'=>'july','agustus'=>'august','september'=>'september','oktober'=>'october','november'=>'november','desember'=>'december','jan'=>'jan','feb'=>'feb','mar'=>'mar','apr'=>'apr','jun'=>'jun','jul'=>'jul','agu'=>'aug','agt'=>'aug','sep'=>'sep','okt'=>'oct','nov'=>'nov','des'=>'dec'];

        if (preg_match('/(?:hari\s+ini)/i', $text)) {
            $result['tanggal'] = date('Y-m-d');
        } elseif (preg_match('/(?:kemarin)/i', $text)) {
            $result['tanggal'] = date('Y-m-d', strtotime('-1 day'));
        } elseif (preg_match('/(\d{1,2}[\-\/\s]{1,3}(?:[A-Za-z]+|\d{1,2})[\-\/\s]{1,3}\d{2,4}|\d{4}[\-\/\s]{1,3}\d{1,2}[\-\/\s]{1,3}\d{1,2})/', $text, $matches)) {
            try {
                $dateStr = strtolower(str_replace('/', '-', $matches[1]));
                $dateStr = strtr($dateStr, $monthsId);
                $parsed  = strtotime($dateStr);
                if ($parsed) $result['tanggal'] = date('Y-m-d', $parsed);
            } catch (\Exception $e) {}
        }

        if (empty($result['tanggal'])) $result['tanggal'] = date('Y-m-d');

        if (preg_match('/\b(selesai|berjalan|tertunda)\b/i', $text, $matches))
            $result['status'] = ucfirst(strtolower($matches[1]));

        if (preg_match('/\b(tinggi|sedang|rendah)\b/i', $text, $matches))
            $result['prioritas'] = ucfirst(strtolower($matches[1]));

        $picFound        = [];
        $existingPicsRaw = LaporanMingguan::pluck('pic')->toArray();
        $allExistingPics = [];
        foreach ($existingPicsRaw as $rawPic) {
            foreach (array_map('trim', explode(',', $rawPic)) as $p) {
                if (!empty($p) && strlen($p) > 2) $allExistingPics[] = strtolower($p);
            }
        }
        $allExistingPics = array_unique($allExistingPics);
        $textLower = strtolower($text);
        foreach ($allExistingPics as $knownPic) {
            if (preg_match('/\b' . preg_quote($knownPic, '/') . '\b/i', $textLower))
                $picFound[] = ucwords($knownPic);
        }

        if (count($picFound) > 0) {
            $result['pic'] = implode(', ', $picFound);
        } elseif (preg_match('/(?:pic|oleh|dikerjakan|staff)\s*[:=\-]?\s*([A-Za-z0-9,\s&]+)(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['pic'] = trim($matches[1]);
        }

        if (preg_match('/(?:lokasi|gedung|ruang|lantai)\s*[:=\-]?\s*([A-Za-z0-9\s]+)(?:\.|,|\n|$)/i', $text, $matches)
            || preg_match('/di\s+([A-Za-z0-9\s]{3,20})(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['lokasi'] = trim($matches[0]);
        }

        if (preg_match('/(?:kegiatan|pekerjaan)\s*[:=\-]?\s*([A-Za-z0-9\s]+)(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['nama_kegiatan'] = ucfirst(trim($matches[1]));
        } else {
            $lines = explode("\n", str_replace(['.', ','], "\n", $text));
            $first = trim($lines[0] ?? '');
            if (strlen($first) > 3) $result['nama_kegiatan'] = ucfirst(substr($first, 0, 100));
        }

        if (preg_match('/(?:hasil|deskripsi|keterangan)\s*[:=\-]?\s*(.*)/is', $text, $matches)) {
            $result['hasil_deskripsi'] = trim($matches[1]);
        } else {
            $result['hasil_deskripsi'] = $text;
        }

        return $result;
    }

    /**
     * Parse blok laporan terstruktur format WhatsApp:
     *   Tanggal : 14 April 2026
     *   Nama Kegiatan : ...
     *   Lokasi : ...
     *   Nama Pelaksana : ...
     *   Keterangan : ...
     *   Hasil Kegiatan : ...
     *   Kendala : ...
     */
    private function parseStructuredReport(string $text): array
    {
        $result = [
            'tanggal'                  => date('Y-m-d'),
            'nama_kegiatan'            => '',
            'lokasi'                   => '',
            'hasil_deskripsi'          => '',
            'prioritas'                => 'Sedang',
            'pic'                      => '',
            'status'                   => 'Selesai',
            'keterangan_tindak_lanjut' => ''
        ];

        // Tanggal — mendukung "14 April 2026" dan "Selasa, 14 April 2026"
        if (preg_match('/Tanggal\s*:\s*(?:[A-Za-z]+,\s*)?(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $text, $m)) {
            $parsed = $this->parseTanggalIndonesia(trim($m[1]));
            if ($parsed) $result['tanggal'] = $parsed;
        }

        // Nama Kegiatan
        if (preg_match('/Nama\s+Kegiatan\s*:\s*(.+)/i', $text, $m))
            $result['nama_kegiatan'] = ucfirst(trim($m[1]));

        // Lokasi
        if (preg_match('/Lokasi\s*:\s*(.+)/i', $text, $m))
            $result['lokasi'] = trim($m[1]);

        // Nama Pelaksana → PIC
        if (preg_match('/Nama\s+Pelaksana\s*:\s*(.+)/i', $text, $m))
            $result['pic'] = trim($m[1]);

        // Hasil Kegiatan (bisa multi-baris, sampai Kendala atau akhir blok)
        if (preg_match('/Hasil\s+Kegiatan\s*:\s*([\s\S]+?)(?=\nKendala\s*:|$)/i', $text, $m)) {
            $hasil = $this->cleanChatNoise(trim($m[1]));
            $result['hasil_deskripsi'] = $hasil;
            $result['status']          = stripos($hasil, 'selesai') !== false ? 'Selesai' : 'Berjalan';
        }

        // Kendala → tindak_lanjut (abaikan jika nilainya "-" atau kosong)
        if (preg_match('/Kendala\s*:\s*(.+)/i', $text, $m)) {
            $kendala = trim($m[1]);
            if ($kendala !== '-' && !empty($kendala)) {
                $result['keterangan_tindak_lanjut'] = $kendala;
                $result['status']                   = 'Tertunda';
            }
        }

        // Auto-generate kesimpulan jika keterangan masih kosong (tidak ada kendala)
        if (empty($result['keterangan_tindak_lanjut']) && !empty($result['nama_kegiatan'])) {
            $result['keterangan_tindak_lanjut'] = $this->generateKesimpulan(
                $result['tanggal'],
                $result['nama_kegiatan'],
                $result['hasil_deskripsi']
            );
        }

        return $result;
    }

    /**
     * Buat kalimat tindak lanjut singkat berdasarkan hasil kegiatan.
     * Contoh: "Kegiatan selesai dilaksanakan." atau isi hasil bersih.
     */
    private function generateKesimpulan(string $tanggal, string $namaKegiatan, string $hasil): string
    {
        if (!empty($hasil)) {
            // Ambil baris pertama, bersihkan nomor list (1. 2. dst)
            $hasilBersih = trim(preg_replace('/^\d+[\.\)]\s*/m', '', strtok($hasil, "\n")));
            if (!empty($hasilBersih)) {
                // Pastikan diakhiri titik
                return rtrim($hasilBersih, '.') . '.';
            }
        }

        // Fallback jika hasil kosong
        return 'Kegiatan selesai dilaksanakan.';
    }

    /**
     * Konversi tanggal bahasa Indonesia ke format Y-m-d.
     * Contoh: "14 April 2026" → "2026-04-14"
     */
    private function parseTanggalIndonesia(string $dateStr): ?string
    {
        $monthsId = [
            'januari'  => 'january',  'februari' => 'february', 'maret'    => 'march',
            'april'    => 'april',    'mei'      => 'may',       'juni'     => 'june',
            'juli'     => 'july',     'agustus'  => 'august',   'september'=> 'september',
            'oktober'  => 'october',  'november' => 'november',  'desember' => 'december',
        ];
        $normalized = strtolower(trim($dateStr));
        $normalized = strtr($normalized, $monthsId);
        $ts = strtotime($normalized);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /**
     * Bersihkan baris-baris noise dari chat WhatsApp:
     * - Jam pesan  : "08:49"
     * - Photo      : "Photo"
     * - Metadata   : "Not included, change data exporting settings..."
     * - Reply info : "In reply to this message"
     * - Dimensi    : "799×502, 95.2 KB"
     * - Ukuran file: "95.2 KB", "8.4 MB"
     * - File PDF/WhatsApp media
     */
    private function cleanChatNoise(string $text): string
    {
        $lines = explode("\n", $text);

        $clean = array_filter($lines, function (string $line): bool {
            $l = trim($line);
            if ($l === '') return false;

            // Jam pesan: "08:49", "17:11"
            if (preg_match('/^\d{1,2}:\d{2}$/', $l)) return false;

            // Kata "Photo" saja
            if (preg_match('/^Photo$/i', $l)) return false;

            // "Not included..."
            if (preg_match('/^Not included/i', $l)) return false;

            // "In reply to this message"
            if (preg_match('/^In reply to/i', $l)) return false;

            // "change data exporting settings..."
            if (preg_match('/^change data exporting/i', $l)) return false;

            // Dimensi gambar: "799×502, 95.2 KB"
            if (preg_match('/^\d+[×x]\d+/i', $l)) return false;

            // Ukuran file mandiri: "95.2 KB", "8.4 MB"
            if (preg_match('/^\d+(\.\d+)?\s*(KB|MB)$/i', $l)) return false;

            // File WhatsApp media atau PDF
            if (preg_match('/^WhatsApp (Image|Video|Audio)/i', $l)) return false;
            if (preg_match('/\.pdf$/i', $l)) return false;

            return true;
        });

        return trim(implode("\n", $clean));
    }
}

