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

        // Prioritas kedua: pesan berisi file/dokumen tanpa format kegiatan
        // Contoh: "Idhamartya Wulani\nNotula 20 April 2026.pdf\n197.7 KB"
        if ($this->isDocumentMessage($text)) {
            return $this->parseDocumentMessage($text);
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

        // Prioritas eksplisit dalam teks lebih diutamakan
        if (preg_match('/\b(tinggi|sedang|rendah)\b/i', $text, $matches)) {
            $result['prioritas'] = ucfirst(strtolower($matches[1]));
        }

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
            $result['nama_kegiatan'] = $this->enrichNamaKegiatan(ucfirst(trim($matches[1])));
        } else {
            $lines = explode("\n", str_replace(['.', ','], "\n", $text));
            $first = trim($lines[0] ?? '');
            if (strlen($first) > 3) $result['nama_kegiatan'] = $this->enrichNamaKegiatan(ucfirst(substr($first, 0, 100)));
        }

        // Auto-generate hasil/deskripsi berdasarkan konteks
        if (preg_match('/(?:hasil|deskripsi|keterangan)\s*[:=\-]?\s*(.*)/is', $text, $matches) && strlen(trim($matches[1])) > 10) {
            $result['hasil_deskripsi'] = trim($matches[1]);
        } elseif (!empty($result['nama_kegiatan'])) {
            $result['hasil_deskripsi'] = $this->generateHasilOtomatis($result['nama_kegiatan'], $result['status']);
        } else {
            $result['hasil_deskripsi'] = $text;
        }

        // Auto-detect prioritas dari konteks jika tidak disebutkan eksplisit
        if ($result['prioritas'] === 'Sedang') {
            $result['prioritas'] = $this->detectPrioritas($result['nama_kegiatan'] . ' ' . $text);
        }

        // Auto-generate tindak lanjut jika masih kosong
        if (empty($result['keterangan_tindak_lanjut']) && !empty($result['nama_kegiatan'])) {
            $result['keterangan_tindak_lanjut'] = $this->generateTindakLanjutOtomatis(
                $result['nama_kegiatan'],
                $result['status']
            );
        }

        return $result;
    }

    /**
     * Auto-generate kalimat hasil/deskripsi berdasarkan konteks nama kegiatan & status.
     */
    private function generateHasilOtomatis(string $namaKegiatan, string $status = 'Selesai'): string
    {
        $lower  = strtolower($namaKegiatan);
        $suffix = match ($status) {
            'Tertunda' => ' namun belum dapat diselesaikan karena terdapat kendala.',
            'Berjalan' => ' dan masih dalam proses pelaksanaan.',
            default    => ' dan telah selesai dilaksanakan.',
        };

        $hasilMap = [
            // Rapat / pertemuan
            ['kw' => ['rapat', 'meeting', 'pertemuan', 'audiensi', 'sinkronisasi'],
             'tpl' => 'Kegiatan rapat telah dilaksanakan bersama pihak-pihak terkait'],
            ['kw' => ['sosialisasi'],
             'tpl' => 'Kegiatan sosialisasi telah dilakukan kepada peserta terkait'],
            ['kw' => ['forum', 'seminar', 'webinar'],
             'tpl' => 'Kegiatan forum/seminar dihadiri dan materi telah diterima'],
            ['kw' => ['workshop', 'bimtek', 'pelatihan', 'training', 'diklat', 'kursus'],
             'tpl' => 'Kegiatan pelatihan/bimtek diikuti untuk meningkatkan kompetensi'],
            // Teknis
            ['kw' => ['instalasi', 'install', 'setup', 'deploy'],
             'tpl' => 'Proses instalasi/pemasangan perangkat telah dilaksanakan'],
            ['kw' => ['konfigurasi', 'config', 'setting'],
             'tpl' => 'Konfigurasi sistem/jaringan telah diterapkan dan diverifikasi'],
            ['kw' => ['perbaikan', 'repair', 'trouble', 'troubleshoot', 'kerusakan'],
             'tpl' => 'Identifikasi masalah dan perbaikan telah dilakukan pada sistem/perangkat'],
            ['kw' => ['maintenance', 'pemeliharaan', 'perawatan'],
             'tpl' => 'Pemeliharaan rutin perangkat/sistem telah dilaksanakan sesuai jadwal'],
            ['kw' => ['monitoring', 'pemantauan', 'pantau'],
             'tpl' => 'Pemantauan kondisi sistem/jaringan telah dilakukan'],
            ['kw' => ['backup', 'pencadangan'],
             'tpl' => 'Proses pencadangan data telah dilaksanakan'],
            ['kw' => ['update', 'upgrade', 'pembaruan', 'patch'],
             'tpl' => 'Pembaruan sistem/perangkat lunak telah diterapkan'],
            ['kw' => ['testing', 'pengujian', 'uji coba'],
             'tpl' => 'Pengujian sistem/fitur telah dilaksanakan'],
            ['kw' => ['migrasi', 'migration'],
             'tpl' => 'Proses migrasi data/sistem telah dilaksanakan'],
            // Dokumen / administratif
            ['kw' => ['laporan', 'notulen', 'notula', 'berita acara'],
             'tpl' => 'Dokumen laporan telah disusun sesuai ketentuan'],
            ['kw' => ['surat', 'dokumen', 'proposal', 'draf', 'sk', 'sop'],
             'tpl' => 'Dokumen/surat terkait telah diterima dan diproses'],
            ['kw' => ['inventaris', 'inventarisasi', 'pendataan', 'opname'],
             'tpl' => 'Pendataan dan inventarisasi perangkat telah dilaksanakan'],
            ['kw' => ['pengadaan', 'pembelian'],
             'tpl' => 'Proses pengadaan/pembelian barang telah dijalankan'],
            // Koordinasi
            ['kw' => ['koordinasi', 'konsultasi'],
             'tpl' => 'Koordinasi dengan pihak terkait telah dilaksanakan'],
            ['kw' => ['pendampingan', 'asistensi'],
             'tpl' => 'Pendampingan kepada pihak terkait telah dilaksanakan'],
        ];

        foreach ($hasilMap as $entry) {
            foreach ($entry['kw'] as $kw) {
                if (str_contains($lower, $kw)) {
                    return $entry['tpl'] . $suffix;
                }
            }
        }

        return ucfirst(lcfirst($namaKegiatan)) . $suffix;
    }

    /**
     * Deteksi prioritas kegiatan berdasarkan konteks teks.
     * Tinggi = mendesak / kritis. Rendah = rutin / administratif. Default = Sedang.
     */
    private function detectPrioritas(string $text): string
    {
        $lower = strtolower($text);

        // Kata-kata yang menandakan prioritas TINGGI (kritis / mendesak)
        $tinggi = [
            'darurat', 'urgent', 'segera', 'mendesak', 'kritis', 'down', 'mati',
            'gangguan', 'kerusakan', 'error', 'bug', 'trouble', 'failure', 'gagal',
            'tidak bisa', 'tidak berfungsi', 'server down', 'jaringan mati',
            'internet mati', 'listrik mati', 'bencana', 'insiden',
        ];

        // Kata-kata yang menandakan prioritas RENDAH (rutin / administratif)
        $rendah = [
            'laporan', 'notulen', 'notula', 'berita acara', 'surat', 'dokumen',
            'sosialisasi', 'pelatihan', 'training', 'workshop', 'seminar', 'webinar',
            'bimtek', 'diklat', 'orientasi', 'inventaris', 'inventarisasi',
            'administrasi', 'absensi', 'sop', 'sk', 'edaran',
        ];

        foreach ($tinggi as $kw) {
            if (str_contains($lower, $kw)) return 'Tinggi';
        }

        foreach ($rendah as $kw) {
            if (str_contains($lower, $kw)) return 'Rendah';
        }

        return 'Sedang';
    }

    /**
     * Tambahkan kata kerja yang sesuai di awal nama kegiatan jika belum ada.
     *
     * Contoh:
     *   "Rapat koordinasi" → "Menghadiri rapat koordinasi"
     *   "Maintenance server" → "Melakukan maintenance server"
     *   "Laporan mingguan" → "Menyusun laporan mingguan"
     */
    private function enrichNamaKegiatan(string $namaKegiatan): string
    {
        if (empty(trim($namaKegiatan))) return $namaKegiatan;

        // Jika sudah diawali kata kerja (prefiks me-, ber-, men-, meng-, mem-, meny-), biarkan
        if (preg_match('/^(?:meng|men|mem|meny|me|ber|di|ter|per)\w{2,}/i', $namaKegiatan)) {
            return $namaKegiatan;
        }

        $lower = strtolower($namaKegiatan);

        // -----------------------------------------------------------------------
        // Prioritas 1: Aktivitas TEKNIS — selalu gunakan "Melakukan" atau
        //              kata kerja teknis yang lebih spesifik.
        // Periksa ini SEBELUM memeriksa rapat/meeting agar "rapat teknis"
        // tidak salah masuk ke "Menghadiri".
        // -----------------------------------------------------------------------
        $technicalMap = [
            'Menginstal'            => ['instalasi', 'install', 'setup', 'deploy', 'deployment'],
            'Mengkonfigurasi'       => ['konfigurasi', 'config', 'setting jaringan', 'setting server'],
            'Memperbaiki'           => ['perbaikan', 'repair', 'trouble', 'troubleshoot', 'error', 'bug', 'kerusakan', 'gangguan'],
            'Melakukan maintenance' => ['maintenance', 'pemeliharaan', 'perawatan', 'servis'],
            'Memantau'              => ['monitoring', 'pemantauan', 'pantau', 'monitor', 'pengawasan'],
            'Melakukan backup'      => ['backup', 'pencadangan'],
            'Menguji'               => ['testing', 'pengujian', 'uji coba', 'ujicoba'],
            'Melakukan migrasi'     => ['migrasi', 'migration', 'pemindahan data'],
            'Memperbarui'           => ['update', 'upgrade', 'pembaruan', 'patching', 'patch'],
            'Membersihkan'          => ['pembersihan', 'cleaning', 'hapus data', 'delete data'],
            'Melakukan sinkronisasi'=> ['sinkronisasi', 'sync', 'singkronisasi'],
            'Melakukan sosialisasi' => ['sosialisasi teknis', 'sosialisasi sistem', 'sosialisasi aplikasi'],
            'Memeriksa'             => ['pengecekan', 'cek jaringan', 'cek server', 'cek sistem', 'check', 'audit', 'verifikasi', 'validasi', 'inspeksi'],
            'Menginventarisasi'     => ['inventarisasi', 'inventaris', 'opname', 'pendataan'],
            'Melakukan pengadaan'   => ['pengadaan', 'pembelian', 'pemesanan'],
            'Mempersiapkan'         => ['persiapan', 'preparasi'],
        ];

        foreach ($technicalMap as $verb => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $verb . ' ' . lcfirst($namaKegiatan);
                }
            }
        }

        // -----------------------------------------------------------------------
        // Prioritas 2: Kegiatan NON-TEKNIS (rapat, pelatihan, dokumen, dll.)
        // -----------------------------------------------------------------------
        $nonTechnicalMap = [
            // Rapat / pertemuan tatap muka (bukan teknis)
            'Menghadiri'       => ['rapat', 'meeting', 'pertemuan', 'audiensi', 'forum', 'kick off'],
            // Pelatihan / bimbingan teknis (bukan kegiatan teknis langsung)
            'Mengikuti'        => ['pelatihan', 'training', 'diklat', 'bimtek', 'workshop', 'seminar', 'webinar', 'kursus', 'orientasi', 'sosialisasi'],
            // Pembuatan dokumen
            'Menyusun'         => ['laporan', 'notulen', 'notula', 'berita acara', 'proposal', 'rancangan', 'draf', 'anggaran', 'rkakl', 'rka', 'sk', 'sop'],
            // Presentasi
            'Mempresentasikan' => ['presentasi', 'paparan', 'pemaparan', 'demo', 'demonstrasi'],
            // Koordinasi
            'Berkoordinasi'    => ['koordinasi', 'koordinir'],
            // Konsultasi
            'Berkonsultasi'    => ['konsultasi'],
            // Pendampingan
            'Mendampingi'      => ['pendampingan', 'asistensi'],
        ];

        foreach ($nonTechnicalMap as $verb => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $verb . ' ' . lcfirst($namaKegiatan);
                }
            }
        }

        // Default: tidak dikenali → "Melakukan"
        return 'Melakukan ' . lcfirst($namaKegiatan);
    }

    /**
     * Deteksi apakah teks merupakan pesan chat yang hanya berisi kiriman file/dokumen.
     * Ciri-ciri: terdapat nama file dengan ekstensi dokumen umum.
     */
    private function isDocumentMessage(string $text): bool
    {
        return (bool) preg_match(
            '/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|odt|ods|odp|txt|csv|zip|rar)\b/i',
            $text
        );
    }

    /**
     * Parse pesan chat yang hanya berisi kiriman file/dokumen (tanpa format kegiatan).
     *
     * Pola yang ditangani (Telegram / WhatsApp):
     *   Idhamartya Wulani
     *   Notula 20 April 2026.pdf
     *   197.7 KB   14:17
     *
     * Menghasilkan nama_kegiatan:
     *   "Menyampaikan surat/dokumen terkait (Notula 20 April 2026)"
     */
    private function parseDocumentMessage(string $text): array
    {
        $monthsId = [
            'januari'  =>'january',  'februari' =>'february', 'maret'    =>'march',
            'april'    =>'april',    'mei'      =>'may',       'juni'     =>'june',
            'juli'     =>'july',     'agustus'  =>'august',   'september'=>'september',
            'oktober'  =>'october',  'november' =>'november',  'desember' =>'december',
        ];

        $result = [
            'tanggal'                  => date('Y-m-d'),
            'nama_kegiatan'            => '',
            'lokasi'                   => '-',
            'hasil_deskripsi'          => '',
            'prioritas'                => 'Sedang',
            'pic'                      => '',
            'status'                   => 'Selesai',
            'keterangan_tindak_lanjut' => '',
        ];

        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($l) => $l !== ''
        ));

        // Cari nama file (baris yang mengandung ekstensi dokumen)
        $fileName = '';
        $fileLineIdx = null;
        foreach ($lines as $i => $line) {
            if (preg_match('/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|odt|ods|odp|txt|csv|zip|rar)\b/i', $line)) {
                $fileName    = $line;
                $fileLineIdx = $i;
                break;
            }
        }

        if (empty($fileName)) {
            // Fallback ke free-text parser biasa jika tidak ditemukan file
            return $result;
        }

        // Nama dokumen tanpa ekstensi & ukuran file
        $docName = trim(preg_replace('/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|odt|ods|odp|txt|csv|zip|rar)\b.*/i', '', $fileName));
        // Hapus ukuran file dari nama jika ikut terseret (mis. "Notula.pdf 197.7 KB")
        $docName = trim(preg_replace('/\d+(\.\d+)?\s*(KB|MB|GB)\s*$/i', '', $docName));

        // Coba ekstrak tanggal dari nama file: "Notula 20 April 2026"
        if (preg_match('/(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $docName, $m)) {
            $dateStr = strtolower($m[1]);
            $dateStr = strtr($dateStr, $monthsId);
            $ts      = strtotime($dateStr);
            if ($ts) $result['tanggal'] = date('Y-m-d', $ts);
        }

        // Baris pertama (sebelum nama file) = kemungkinan nama pengirim
        $senderName = '';
        if ($fileLineIdx !== null && $fileLineIdx > 0) {
            $candidate = $lines[$fileLineIdx - 1];
            // Abaikan jika baris itu terlihat seperti jam/tanggal/ukuran
            if (!preg_match('/^\d{1,2}[:\-\/]\d{2}/', $candidate)
                && !preg_match('/^\d+(\.\d+)?\s*(KB|MB)/i', $candidate)) {
                $senderName = $candidate;
            }
        }

        // Cari PIC yang cocok dari database
        if (!empty($senderName)) {
            $existingPicsRaw = LaporanMingguan::pluck('pic')->toArray();
            $allExistingPics = [];
            foreach ($existingPicsRaw as $rawPic) {
                foreach (array_map('trim', explode(',', $rawPic)) as $p) {
                    if (!empty($p) && strlen($p) > 2) $allExistingPics[] = $p;
                }
            }
            // Urutkan dari nama terpanjang dulu agar pencocokan paling spesifik diutamakan
            $allExistingPics = array_unique($allExistingPics);
            usort($allExistingPics, fn($a, $b) => strlen($b) - strlen($a));

            $matched = null;

            // Tahap 1: cocokkan nama persis (case-insensitive)
            foreach ($allExistingPics as $knownPic) {
                if (strcasecmp($senderName, $knownPic) === 0) {
                    $matched = $knownPic;
                    break;
                }
            }

            // Tahap 2: nama pengirim mengandung nama PIC sebagai kata utuh
            // Contoh: "Bruno Fernandes" → cocok dengan "Bruno" (kata pertama)
            if (!$matched) {
                foreach ($allExistingPics as $knownPic) {
                    if (preg_match('/\b' . preg_quote($knownPic, '/') . '\b/i', $senderName)) {
                        $matched = $knownPic;
                        break;
                    }
                }
            }

            // Tahap 3: nama PIC mengandung nama pengirim sebagai kata utuh
            // Contoh: "Bruno" → cocok dengan "Bruno Fernandes" di DB
            if (!$matched) {
                foreach ($allExistingPics as $knownPic) {
                    if (preg_match('/\b' . preg_quote($senderName, '/') . '\b/i', $knownPic)) {
                        $matched = $knownPic;
                        break;
                    }
                }
            }

            if ($matched) {
                $result['pic'] = $matched;
            } else {
                // Tidak ada yang cocok — gunakan nama pengirim apa adanya
                $result['pic'] = $senderName;
            }
        }

        // Nama kegiatan generik yang informatif
        $result['nama_kegiatan']   = 'Menyampaikan surat/dokumen terkait (' . $docName . ')';
        $result['hasil_deskripsi'] = $this->generateHasilOtomatis($result['nama_kegiatan'], 'Selesai');
        $result['prioritas']       = $this->detectPrioritas($docName);

        // Auto tindak lanjut
        if (empty($result['keterangan_tindak_lanjut'])) {
            $result['keterangan_tindak_lanjut'] = $this->generateTindakLanjutOtomatis($result['nama_kegiatan'], 'Selesai');
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

        // Nama Kegiatan — ditambahkan kata kerja kontekstual jika belum ada
        if (preg_match('/Nama\s+Kegiatan\s*:\s*(.+)/i', $text, $m)) {
            $result['nama_kegiatan'] = $this->enrichNamaKegiatan(ucfirst(trim($m[1])));
            // Auto-detect prioritas dari nama kegiatan
            $result['prioritas'] = $this->detectPrioritas($result['nama_kegiatan']);
        }

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

        // Auto-generate tindak lanjut berbasis konteks jika masih kosong
        if (empty($result['keterangan_tindak_lanjut']) && !empty($result['nama_kegiatan'])) {
            $result['keterangan_tindak_lanjut'] = $this->generateTindakLanjutOtomatis(
                $result['nama_kegiatan'],
                $result['status']
            );
        }

        return $result;
    }

    /**
     * Auto-generate kalimat tindak lanjut berbasis konteks nama kegiatan & status.
     */
    private function generateTindakLanjutOtomatis(string $namaKegiatan, string $status = 'Selesai'): string
    {
        $lower = strtolower($namaKegiatan);

        // Status Tertunda / Berjalan → kalimat generik sesuai status
        if ($status === 'Tertunda') {
            return 'Menunggu penyelesaian kendala yang ada sebelum kegiatan dapat dilanjutkan.';
        }
        if ($status === 'Berjalan') {
            return 'Kegiatan masih dalam proses pelaksanaan dan akan diselesaikan sesuai jadwal.';
        }

        // -----------------------------------------------------------------------
        // Lapisan 1: Kegiatan TEKNIS — diperiksa lebih dulu
        // -----------------------------------------------------------------------
        $teknis = [
            ['kw' => ['backup', 'pencadangan'],
             'kalimat' => 'Data berhasil dicadangkan dan disimpan di lokasi yang aman.'],
            ['kw' => ['instalasi', 'install', 'setup', 'deploy', 'deployment'],
             'kalimat' => 'Sistem/perangkat telah berhasil dipasang dan siap digunakan.'],
            ['kw' => ['konfigurasi', 'config'],
             'kalimat' => 'Konfigurasi telah diterapkan dan diuji coba dengan hasil baik.'],
            ['kw' => ['perbaikan', 'repair', 'trouble', 'troubleshoot', 'kerusakan', 'gangguan', 'bug', 'error'],
             'kalimat' => 'Permasalahan telah berhasil diselesaikan dan sistem kembali berjalan normal.'],
            ['kw' => ['maintenance', 'pemeliharaan', 'perawatan', 'servis'],
             'kalimat' => 'Perangkat telah selesai dipelihara dan kembali beroperasi secara optimal.'],
            ['kw' => ['monitoring', 'pemantauan', 'pantau', 'monitor'],
             'kalimat' => 'Hasil pemantauan telah dicatat dan akan dijadikan bahan evaluasi.'],
            ['kw' => ['update', 'upgrade', 'pembaruan', 'patching', 'patch'],
             'kalimat' => 'Pembaruan berhasil diterapkan dan sistem telah diuji coba.'],
            ['kw' => ['migrasi', 'migration'],
             'kalimat' => 'Proses migrasi telah selesai dan data telah diverifikasi.'],
            ['kw' => ['sinkronisasi', 'sync'],
             'kalimat' => 'Data telah berhasil disinkronisasi antara sistem/server terkait.'],
            ['kw' => ['testing', 'pengujian', 'uji coba', 'ujicoba'],
             'kalimat' => 'Hasil pengujian telah didokumentasikan untuk ditindaklanjuti.'],
            ['kw' => ['pengecekan', 'cek jaringan', 'cek server', 'cek sistem', 'audit', 'verifikasi', 'validasi', 'inspeksi'],
             'kalimat' => 'Hasil pengecekan telah dicatat dan akan disampaikan kepada pihak terkait.'],
            ['kw' => ['inventaris', 'inventarisasi', 'pendataan', 'opname'],
             'kalimat' => 'Data inventaris telah diperbarui sesuai kondisi terkini.'],
            ['kw' => ['pengadaan', 'pembelian', 'pemesanan'],
             'kalimat' => 'Pengadaan telah selesai diproses dan menunggu serah terima barang.'],
            ['kw' => ['persiapan', 'preparasi'],
             'kalimat' => 'Persiapan telah selesai dilakukan dan kegiatan siap dilaksanakan.'],
            ['kw' => ['sosialisasi sistem', 'sosialisasi aplikasi', 'sosialisasi teknis'],
             'kalimat' => 'Sosialisasi telah disampaikan dan peserta telah memahami materi yang diberikan.'],
        ];

        foreach ($teknis as $entry) {
            foreach ($entry['kw'] as $kw) {
                if (str_contains($lower, $kw)) {
                    return $entry['kalimat'];
                }
            }
        }

        // -----------------------------------------------------------------------
        // Lapisan 2: Kegiatan NON-TEKNIS
        // -----------------------------------------------------------------------
        $nonTeknis = [
            ['kw' => ['rapat', 'meeting', 'pertemuan', 'audiensi'],
             'kalimat' => 'Hasil rapat akan ditindaklanjuti sesuai kesepakatan bersama.'],
            ['kw' => ['seminar', 'webinar', 'forum', 'diskusi'],
             'kalimat' => 'Hasil diskusi dan rekomendasi akan dijadikan bahan tindak lanjut.'],
            ['kw' => ['workshop', 'pelatihan', 'training', 'diklat', 'bimtek', 'kursus', 'orientasi'],
             'kalimat' => 'Ilmu dan keterampilan yang diperoleh akan diterapkan dalam pelaksanaan tugas.'],
            ['kw' => ['sosialisasi'],
             'kalimat' => 'Hasil sosialisasi akan disebarluaskan kepada pihak-pihak yang belum hadir.'],
            ['kw' => ['laporan', 'notulen', 'notula', 'berita acara'],
             'kalimat' => 'Dokumen telah diselesaikan dan siap untuk diserahkan kepada pihak terkait.'],
            ['kw' => ['surat', 'dokumen', 'proposal', 'draf', 'sk', 'sop'],
             'kalimat' => 'Dokumen telah diterima dan akan diproses lebih lanjut.'],
            ['kw' => ['presentasi', 'paparan'],
             'kalimat' => 'Materi presentasi telah disampaikan dan mendapatkan tanggapan dari peserta.'],
            ['kw' => ['koordinasi'],
             'kalimat' => 'Hasil koordinasi akan ditindaklanjuti oleh masing-masing pihak terkait.'],
            ['kw' => ['konsultasi'],
             'kalimat' => 'Hasil konsultasi telah diperoleh dan akan dijadikan acuan pelaksanaan.'],
            ['kw' => ['pendampingan', 'asistensi'],
             'kalimat' => 'Pendampingan telah diberikan dan pihak terkait siap menjalankan tugasnya.'],
        ];

        foreach ($nonTeknis as $entry) {
            foreach ($entry['kw'] as $kw) {
                if (str_contains($lower, $kw)) {
                    return $entry['kalimat'];
                }
            }
        }

        return 'Kegiatan telah selesai dilaksanakan.';
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

            // Kata "Photo" atau "Video" saja
            if (preg_match('/^(Photo|Video|Audio|Sticker|GIF)$/i', $l)) return false;

            // "Not included..."
            if (preg_match('/^Not included/i', $l)) return false;

            // "In reply to this message"
            if (preg_match('/^In reply to/i', $l)) return false;

            // "change data exporting settings..."
            if (preg_match('/^change data exporting/i', $l)) return false;

            // Dimensi gambar — berbagai format pemisah (×, x, X) dan unicode ×
            // Contoh: "799×502, 95.2 KB" | "507x593" | "393\xc3\x97623, 31.2 KB"
            if (preg_match('/^\d+\s*[\x{00D7}xX\*]\s*\d+/u', $l)) return false;
            // Fallback: pola angka-separator-angka yang diikuti ukuran KB/MB
            if (preg_match('/^\d+\s*[^\w\s]\s*\d+\s*,?\s*\d+(\.\d+)?\s*(KB|MB)/i', $l)) return false;

            // Ukuran file mandiri: "95.2 KB", "8.4 MB", "40.4 KB"
            if (preg_match('/^\d+(\.\d+)?\s*(B|KB|MB|GB)$/i', $l)) return false;
            // Ukuran file dengan koma setelah dimensi: "31.2 KB" (sisa setelah dimensi)
            if (preg_match('/^[\d.,\s]+(KB|MB|GB)$/i', $l)) return false;

            // File WhatsApp media atau PDF
            if (preg_match('/^WhatsApp\s+(Image|Video|Audio|Document)/i', $l)) return false;
            if (preg_match('/\.(pdf|docx?|xlsx?|pptx?)$/i', $l)) return false;

            // "This message was deleted" / pesan sistem
            if (preg_match('/^This message was/i', $l)) return false;
            if (preg_match('/^<Media omitted>/i', $l)) return false;
            if (preg_match('/^\[?\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4}.*\]?$/', $l)) return false;

            return true;
        });

        return trim(implode("\n", $clean));
    }
}

