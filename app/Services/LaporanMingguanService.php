<?php

namespace App\Services;

use App\Models\LaporanMingguan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanMingguanService
{
    private const REPORT_LABELS = 'Tanggal|Nama Kegiatan|Lokasi|Nama Pelaksana|Keterangan|Hasil Kegiatan|Kendala|Tindak Lanjut|Status|Prioritas|Foto';

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
        // Ensure text is valid UTF-8 to avoid malformed character issues
        $text = $this->normalizeStructuredReportLabels(
            $this->removeTelegramMediaSenders($this->ensureUtf8($text))
        );
        $blocks = $this->splitIntoBlocks($text);

        $results = [];
        foreach ($blocks as $block) {
            $block = trim($block);
            if (!$this->hasReportContent($block)) continue;
            $results[] = $this->parseText($block);
        }

        return $results;
    }

    private function hasReportContent(string $text): bool
    {
        return $this->isDocumentMessage($text)
            || preg_match('/[\pL\pN]/u', $this->cleanChatNoise($text)) === 1;
    }

    private function removeTelegramMediaSenders(string $text): string
    {
        $lines = preg_split('/\R/', $text);
        $senderIndexes = [];

        foreach ($lines as $index => $line) {
            if (!preg_match('/^(Photo|Video|Audio|Sticker|GIF)$/i', trim($line))) {
                continue;
            }

            $senderIndex = $index - 1;
            while ($senderIndex >= 0 && $this->isDocumentExportNoiseLine($lines[$senderIndex])) {
                $senderIndex--;
            }

            $sender = trim($lines[$senderIndex] ?? '');
            $beforeSender = $senderIndex - 1;
            while ($beforeSender >= 0 && trim($lines[$beforeSender]) === '') {
                $beforeSender--;
            }

            $header = trim($lines[$beforeSender] ?? '');
            // Hanya hapus nama dalam header tanggal/jam pesan. Isi laporan
            // atau caption tanpa header Telegram harus tetap dipertahankan.
            if (preg_match('/^[\pL][\pL\pM\pN ._\x{0027}()-]{0,99}$/u', $sender)
                && ($this->isChatDateLine($header) || preg_match('/^\d{1,2}:\d{2}$/', $header))) {
                $senderIndexes[$senderIndex] = true;
            }
        }

        return implode("\n", array_filter(
            $lines,
            fn($_line, $index) => !isset($senderIndexes[$index]),
            ARRAY_FILTER_USE_BOTH
        ));
    }

    private function normalizeStructuredReportLabels(string $text): string
    {
        // Samakan label sebelum memisahkan kegiatan, termasuk label dalam satu baris.
        if (!preg_match('/\b(?:tanggal|nama\h+kegiatan|tindak\h+lanjut)\h*[*_]{0,2}\h*:/iu', $text)) {
            return $text;
        }

        $labels = [
            'Tanggal' => '(?:hari\h*[,\/]?\h*)?tanggal',
            'Nama Kegiatan' => 'nama\h+kegiatan',
            'Lokasi' => 'lokasi|tempat',
            'Nama Pelaksana' => '(?:nama\h+)?pelaksana|pic',
            'Keterangan' => 'ket\.\h*(?:kegiatan)?|keterangan(?:\h+kegiatan)?',
            'Hasil Kegiatan' => 'hasil(?:\h+kegiatan)?',
            'Kendala' => 'kendala',
            'Tindak Lanjut' => '(?:(?:keterangan|rencana)\h+)?tindak\h+lanjut',
            'Status' => 'status(?:\h+kegiatan)?',
            'Prioritas' => 'prioritas',
            'Foto' => 'foto|dokumentasi',
        ];
        $labelPattern = implode('|', array_map(fn($label) => '(?:' . $label . ')', $labels));

        $normalized = trim(preg_replace_callback(
            '/(?<![\pL\pN])[*_]{0,2}(?<label>' . $labelPattern . ')\h*[*_]{0,2}\h*:\h*[*_]{0,2}\h*/iu',
            function (array $match) use ($labels): string {
                foreach ($labels as $canonical => $pattern) {
                    if (preg_match('/^(?:' . $pattern . ')$/iu', $match['label'])) {
                        return "\n" . $canonical . ' : ';
                    }
                }

                return $match[0];
            },
            $text
        ));

        // Normalisasi label yang sudah berada di awal baris tidak membuat paragraf baru.
        return preg_replace('/\n{2,}(?=(?:' . self::REPORT_LABELS . ')\h*:)/i', "\n", $normalized);
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
        // Strategi awal: campuran laporan terstruktur dan kiriman dokumen.
        $mixedBlocks = $this->splitMixedStructuredAndDocumentBlocks($text);
        if (count($mixedBlocks) > 1) return $mixedBlocks;

        // Strategi 0: format laporan terstruktur WhatsApp — split sebelum setiap "Tanggal :"
        if (preg_match_all('/(?:^|\n)Tanggal\s*:/im', $text, $m) && count($m[0]) > 1) {
            $parts = preg_split('/(?=\n?Tanggal\s*:)/i', $text, -1, PREG_SPLIT_NO_EMPTY);
            $parts = array_values(array_filter($parts, fn($p) => preg_match('/Tanggal\s*:/i', $p)));
            if (count($parts) > 1) return $parts;
        }

        // Strategi 0b: export Telegram berisi beberapa kiriman dokumen.
        $documentBlocks = $this->splitTelegramDocumentBlocks($text);
        if (count($documentBlocks) > 1) return $documentBlocks;

        // Baris kosong dan bullet di dalam satu laporan tetap bagian dari kolomnya.
        if (preg_match('/^(?:Tanggal|Nama Kegiatan)\h*:/im', $text)) {
            return [$text];
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

    private function splitMixedStructuredAndDocumentBlocks(string $text): array
    {
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\R/', $text)),
            fn($line) => $line !== ''
        ));

        $starts = [];
        $inheritedDates = [];
        $hasStructuredStart = false;
        $hasActivityName = false;
        $reportDateLine = null;

        foreach ($lines as $i => $line) {
            if (preg_match('/^Tanggal\h*:/i', $line)) {
                $starts[] = $i;
                $hasStructuredStart = true;
                $hasActivityName = false;
                $reportDateLine = $line;
                continue;
            }

            if (preg_match('/^Nama Kegiatan\h*:/i', $line)) {
                // Satu tanggal dapat menaungi beberapa kegiatan. Nama berikutnya
                // selalu memulai entri baru agar kegiatan sebelumnya tidak tertimpa.
                if (!$hasStructuredStart || $hasActivityName) {
                    $starts[] = $i;
                    if ($reportDateLine !== null) {
                        $inheritedDates[$i] = $reportDateLine;
                    }
                }
                $hasStructuredStart = true;
                $hasActivityName = true;
                continue;
            }

            if ($this->isDocumentFileLine($line)) {
                $senderIndex = $this->findDocumentSenderLineIndex($lines, $i);
                $starts[] = $senderIndex ?? $i;
                $hasStructuredStart = false;
                $hasActivityName = false;
                $reportDateLine = null;
            }
        }

        $starts = array_values(array_unique($starts));
        sort($starts);

        if (count($starts) <= 1) return [];

        $blocks = [];
        foreach ($starts as $i => $start) {
            $end = ($starts[$i + 1] ?? count($lines)) - 1;
            $blockLines = array_slice($lines, $start, $end - $start + 1);
            if (isset($inheritedDates[$start])) {
                array_unshift($blockLines, $inheritedDates[$start]);
            }
            $chatDate = $this->findNearestChatDateLine($lines, $start);
            if ($chatDate !== null && !$this->isChatDateLine($blockLines[0] ?? '')) {
                array_unshift($blockLines, $chatDate);
            }
            $blocks[] = implode("\n", $blockLines);
        }

        return array_values(array_filter($blocks, fn($block) => trim($block) !== ''));
    }

    private function splitTelegramDocumentBlocks(string $text): array
    {
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\R/', $text)),
            fn($line) => $line !== ''
        ));

        $fileIndexes = [];
        foreach ($lines as $i => $line) {
            if ($this->isDocumentFileLine($line)) {
                $fileIndexes[] = $i;
            }
        }

        if (count($fileIndexes) <= 1) return [];

        $starts = [];
        foreach ($fileIndexes as $fileIndex) {
            $starts[] = $this->findDocumentSenderLineIndex($lines, $fileIndex) ?? $fileIndex;
        }

        $blocks = [];
        foreach ($starts as $i => $start) {
            $end = ($starts[$i + 1] ?? count($lines)) - 1;
            $blockLines = array_slice($lines, $start, $end - $start + 1);
            $chatDate = $this->findNearestChatDateLine($lines, $start);
            if ($chatDate !== null && !$this->isChatDateLine($blockLines[0] ?? '')) {
                array_unshift($blockLines, $chatDate);
            }
            $blocks[] = implode("\n", $blockLines);
        }

        return array_values(array_filter($blocks, fn($block) => $this->isDocumentMessage($block)));
    }

    public function parseText(string $text): array
    {
        // Normalize encoding to UTF-8 to avoid invalid byte sequences
        $text = $this->normalizeStructuredReportLabels(
            $this->removeTelegramMediaSenders($this->ensureUtf8($text))
        );

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

        if (!$this->hasReportContent($text)) {
            return $result;
        }

        // Prioritas utama: format laporan terstruktur WhatsApp
        if (preg_match('/^(?:Tanggal|Nama Kegiatan)\h*:/im', $text)) {
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

        // Tanggal chat dipakai sebagai konteks, bukan sebagai nama kegiatan.
        $text = $this->cleanChatNoise($text);

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

        if (preg_match('/(?:lokasi|gedung|ruang|lantai)\s*[:=\-]?\s*([A-Za-z0-9\s]+)(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['lokasi'] = trim($matches[1]);
        } elseif (preg_match('/\bdi\s+([A-Za-z0-9\s]{3,30})(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['lokasi'] = trim($matches[1]);
        }

        if (preg_match('/(?:kegiatan|pekerjaan)\s*[:=\-]?\s*([A-Za-z0-9\s]+)(?:\.|,|\n|$)/i', $text, $matches)) {
            $result['nama_kegiatan'] = $this->enrichNamaKegiatan(ucfirst(trim($matches[1])));
        } else {
            $lines = explode("\n", str_replace(['.', ','], "\n", $text));
            $first = trim($lines[0] ?? '');
            if (strlen($first) > 3) $result['nama_kegiatan'] = $this->enrichNamaKegiatan(ucfirst(substr($first, 0, 100)));
        }

        $fields = $this->extractReportFields($text);
        $result['keterangan_tindak_lanjut'] = $fields['tindak lanjut'] ?? '';
        if ($result['keterangan_tindak_lanjut'] === '-') {
            $result['keterangan_tindak_lanjut'] = '';
        }

        // Isi kolom berlabel berhenti pada label berikutnya.
        if (isset($fields['hasil kegiatan']) || isset($fields['keterangan'])) {
            $result['hasil_deskripsi'] = $fields['hasil kegiatan'] ?? $fields['keterangan'];
        } elseif (preg_match('/(?:hasil|deskripsi|keterangan)\s*[:=\-]?\s*(.*)/is', $text, $matches) && strlen(trim($matches[1])) > 10) {
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
     * Ensure the given text is valid UTF-8. Strip or convert invalid bytes.
     */
    private function ensureUtf8(string $text): string
    {
        if ($text === '') return $text;

        // Try to strip invalid UTF-8 sequences
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if ($clean !== false && $clean !== null) {
            return $clean;
        }

        // Fallback: try common single-byte encodings
        $converted = @mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $converted);
        return $clean === false ? '' : $clean;
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
        $namaKegiatan = trim($namaKegiatan);
        if (empty(trim($namaKegiatan))) return $namaKegiatan;

        // Jika sudah diawali kata kerja (prefiks me-, ber-, men-, meng-, mem-, meny-), biarkan
        if (preg_match('/^(?:meng|men|mem|meny|me|ber|di|ter|per)\w{2,}/i', $namaKegiatan)) {
            return $namaKegiatan;
        }

        // Bentuk kegiatan di awal judul lebih kuat daripada topik laporan/webinar di belakangnya.
        if (preg_match('/^pembuatan\h+(.+)$/isu', $namaKegiatan, $matches)) {
            return 'Membuat ' . trim($matches[1]);
        }
        if (preg_match('/^(?:desain|design)\b/iu', $namaKegiatan)) {
            return 'Membuat ' . lcfirst($namaKegiatan);
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
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/iu', $lower)) {
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

    private function isDocumentFileLine(string $line): bool
    {
        return (bool) preg_match('/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|odt|ods|odp|txt|csv|zip|rar)\b/i', $line);
    }

    private function isDocumentExportNoiseLine(string $line): bool
    {
        $line = trim($line);

        return $line === ''
            || preg_match('/^In reply to/i', $line)
            || preg_match('/^Not included/i', $line)
            || preg_match('/^change data exporting/i', $line)
            || preg_match('/^\d+(\.\d+)?\s*(B|KB|MB|GB)$/i', $line)
            || preg_match('/^\d{1,2}:\d{2}$/', $line);
    }

    private function isStructuredReportLine(string $line): bool
    {
        return (bool) preg_match('/^(?:' . self::REPORT_LABELS . ')\h*:/i', trim($line));
    }

    private function findDocumentSenderLineIndex(array $lines, int $fileLineIdx): ?int
    {
        for ($i = $fileLineIdx - 1; $i >= 0; $i--) {
            $candidate = trim($lines[$i]);
            if ($this->isStructuredReportLine($candidate)) {
                return null;
            }
            if ($this->isChatDateLine($candidate)) {
                return null;
            }
            if (!$this->isDocumentExportNoiseLine($candidate) && !$this->isDocumentFileLine($candidate)) {
                return $i;
            }
        }

        return null;
    }

    private function extractDocumentCaption(array $lines, int $fileLineIdx): string
    {
        for ($i = $fileLineIdx + 1; $i < count($lines); $i++) {
            $candidate = trim($lines[$i]);
            if ($this->isDocumentFileLine($candidate)) break;
            if ($this->isStructuredReportLine($candidate)) break;
            if (!$this->isDocumentExportNoiseLine($candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    private function findDocumentDate(array $lines, int $fileLineIdx, string $docName): ?string
    {
        for ($i = $fileLineIdx - 1; $i >= 0; $i--) {
            $candidate = trim($lines[$i]);

            if ($this->isStructuredReportLine($candidate)) {
                break;
            }

            if ($this->isChatDateLine($candidate)) {
                return $this->parseTanggalFromText($candidate);
            }
        }

        return $this->parseTanggalFromText($docName);
    }

    private function isChatDateLine(string $line): bool
    {
        $line = trim($line);

        if ($line === '') {
            return false;
        }

        if (preg_match('/^(?:senin|selasa|rabu|kamis|jumat|jum\'at|sabtu|minggu),?\s+\d{1,2}\s+[A-Za-z]+\s+\d{4}$/i', $line)) {
            return true;
        }

        if (preg_match('/^\d{1,2}\s+[A-Za-z]+\s+\d{4}$/i', $line)) {
            return true;
        }

        return (bool) preg_match('/^\[?\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4}(?:[, ]+\d{1,2}:\d{2})?\]?$/', $line);
    }

    private function findNearestChatDateLine(array $lines, int $beforeIndex): ?string
    {
        for ($i = $beforeIndex - 1; $i >= 0; $i--) {
            if ($this->isChatDateLine($lines[$i])) {
                return trim($lines[$i]);
            }
        }

        return null;
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

        $documentDate = $this->findDocumentDate($lines, $fileLineIdx, $docName);
        if ($documentDate) {
            $result['tanggal'] = $documentDate;
        }

        // Baris pengirim berada sebelum nama file. Pada export Telegram
        // bisa tersisip baris "In reply to this message".
        $senderName = '';
        if ($fileLineIdx !== null) {
            $senderLineIdx = $this->findDocumentSenderLineIndex($lines, $fileLineIdx);
            if ($senderLineIdx !== null) $senderName = $lines[$senderLineIdx];
        }
        $senderName = $this->cleanDocumentSenderName($senderName);

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

        if (empty($result['pic'])) {
            $result['pic'] = 'Belum diketahui';
        }

        // Nama kegiatan generik yang informatif
        $result['nama_kegiatan']   = 'Menyampaikan surat/dokumen terkait (' . $docName . ')';
        $caption = $this->extractDocumentCaption($lines, $fileLineIdx);
        $result['hasil_deskripsi'] = $caption !== ''
            ? $caption
            : $this->generateHasilOtomatis($result['nama_kegiatan'], 'Selesai');
        $result['prioritas']       = $this->detectPrioritas($caption !== '' ? $caption : $docName);

        $fields = $this->extractReportFields($text);
        $result['keterangan_tindak_lanjut'] = $fields['tindak lanjut'] ?? '';
        if ($result['keterangan_tindak_lanjut'] === '-') {
            $result['keterangan_tindak_lanjut'] = '';
        }

        return $result;
    }

    private function cleanDocumentSenderName(string $senderName): string
    {
        $senderName = trim($senderName);
        $senderName = preg_replace(
            '/^[^\pL\pN]*(?:(?:document|file|photo|video)\s+from|(?:dokumen|berkas|foto|video)\s+dari)\s+/iu',
            '',
            $senderName
        );

        return trim(preg_replace('/\s+/u', ' ', $senderName));
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

        $fields = $this->extractReportFields($text);

        if (!empty($fields['tanggal'])) {
            $parsed = $this->parseTanggalIndonesia($fields['tanggal']);
            if ($parsed) $result['tanggal'] = $parsed;
        }

        if (!empty($fields['nama kegiatan'])) {
            $result['nama_kegiatan'] = $this->enrichNamaKegiatan(ucfirst($fields['nama kegiatan']));
            $result['prioritas'] = $this->detectPrioritas($result['nama_kegiatan']);
        }

        $result['lokasi'] = $fields['lokasi'] ?? '';
        $result['pic'] = $fields['nama pelaksana'] ?? '';

        $hasil = $fields['hasil kegiatan'] ?? '';
        if ($hasil === '' || $hasil === '-') {
            $hasil = $fields['keterangan'] ?? '';
        }
        if ($hasil !== '' && $hasil !== '-') {
            $result['hasil_deskripsi'] = $this->cleanChatNoise($hasil);
            $result['status'] = stripos($hasil, 'selesai') !== false ? 'Selesai' : 'Berjalan';
        }

        $kendala = $fields['kendala'] ?? '';
        if ($kendala !== '-' && $kendala !== '') {
            $result['keterangan_tindak_lanjut'] = $kendala;
            $result['status'] = 'Tertunda';
        }

        if (array_key_exists('tindak lanjut', $fields)) {
            $result['keterangan_tindak_lanjut'] = $fields['tindak lanjut'] === '-' ? '' : $fields['tindak lanjut'];
        }

        // Status dan prioritas yang ditulis pengguna lebih kuat daripada tebakan kata kunci.
        foreach (['status' => ['Selesai', 'Berjalan', 'Tertunda'], 'prioritas' => ['Tinggi', 'Sedang', 'Rendah']] as $field => $allowed) {
            $value = ucfirst(strtolower($fields[$field] ?? ''));
            if (in_array($value, $allowed, true)) {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    private function extractReportFields(string $text): array
    {
        // Kolom kosong dan multiline berhenti pada label berikutnya dalam kegiatan ini.
        $labels = self::REPORT_LABELS;
        preg_match_all(
            '/^(' . $labels . ')\h*:\h*(.*?)(?=^(?:' . $labels . ')\h*:|\z)/imsu',
            $text,
            $matches,
            PREG_SET_ORDER
        );
        $fields = [];
        foreach ($matches as $match) {
            $label = strtolower($match[1]);
            $fields[$label] = $label === 'tanggal'
                ? trim($match[2])
                : $this->cleanChatNoise($match[2]);
        }

        return $fields;
    }

    /**
     * Konversi tanggal bahasa Indonesia ke format Y-m-d.
     * Contoh: "14 April 2026" → "2026-04-14"
     */
    private function parseTanggalIndonesia(string $dateStr): ?string
    {
        return $this->parseTanggalFromText($dateStr);
    }

    private function parseTanggalFromText(string $text): ?string
    {
        $monthsId = [
            'januari'  => 'january',  'februari' => 'february', 'maret'    => 'march',
            'april'    => 'april',    'mei'      => 'may',       'juni'     => 'june',
            'juli'     => 'july',     'agustus'  => 'august',   'september'=> 'september',
            'oktober'  => 'october',  'november' => 'november',  'desember' => 'december',
            'jan'      => 'jan',      'feb'      => 'feb',       'mar'      => 'mar',
            'apr'      => 'apr',      'jun'      => 'jun',       'jul'      => 'jul',
            'agu'      => 'aug',      'agt'      => 'aug',       'sep'      => 'sep',
            'okt'      => 'oct',      'nov'      => 'nov',       'des'      => 'dec',
        ];

        $patterns = [
            '/(?:senin|selasa|rabu|kamis|jumat|jum\'at|sabtu|minggu),?\s*(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i',
            '/(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i',
            '/\b(\d{4}[\/\-.]\d{1,2}[\/\-.]\d{1,2})\b/',
            '/\b(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4})\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $normalized = strtolower(trim($matches[1]));
                $normalized = str_replace('/', '-', $normalized);
                $normalized = strtr($normalized, $monthsId);
                $ts = strtotime($normalized);

                if ($ts) {
                    return date('Y-m-d', $ts);
                }
            }
        }

        return null;
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

            if ($this->isChatDateLine($l)) return false;
            if (preg_match('/^[-\x{2013}\x{2014}]+$/u', $l)) return false;

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
