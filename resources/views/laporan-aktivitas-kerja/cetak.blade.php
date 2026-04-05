<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Aktivitas Kerja - {{ $pic }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 2px solid #000;
            padding: 4px 6px;
        }
        th {
            font-weight: bold;
            text-align: center;
        }
        /* Make specific headers 1px border like standard tables if needed, but screenshot shows very bold borders */
        table { border: 2px solid #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Pegawai Table Layout */
        .pegawai-table td { border: 2px solid #000; }
        .w-5 { width: 5%; text-align: center; }
        .w-25 { width: 25%; }
        .w-70 { width: 70%; }
        
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .signatures table {
            border: none;
        }
        .signatures td {
            border: none;
            text-align: center;
            width: 50%;
            vertical-align: bottom;
            padding-top: 60px;
        }

        /* Tanda Tangan (Same as Laporan Mingguan) */
        .ttd-container {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
            font-size: 11pt;
        }
        .ttd-box {
            float: right;
            width: 320px;
            text-align: left;
            margin-right: -10px;
        }
        .ttd-tanggal {
            margin-bottom: 3px;
        }
        .ttd-jabatan {
            margin-bottom: 30px;
            height: 45px; /* Ensures fixed height roughly for up to 3 lines */
        }
        .ttd-placeholder {
            margin-bottom: 35px;
            white-space: pre;
        }
        .ttd-nama {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        @media print {
            body { padding: 0; }
            @page { size: portrait; margin: 1cm; }
        }
    </style>
</head>
<body>

    <table class="pegawai-table">
        <tr>
            <td colspan="3" class="header-title">
                REKAPITULASI<br>
                LAPORAN AKTIVITAS KERJA
            </td>
        </tr>
        <tr>
            <td colspan="3" class="header-title">DATA PEGAWAI</td>
        </tr>
        <tr>
            <td class="w-5 font-bold">1</td>
            <td class="w-25 font-bold">Nama</td>
            <td class="w-70 font-bold">{{ $pic }}</td>
        </tr>
        <tr>
            <td class="w-5 font-bold">2</td>
            <td class="w-25 font-bold">NIP</td>
            <td class="w-70 font-bold">{{ $nip }}</td>
        </tr>
        <tr>
            <td class="w-5 font-bold">3</td>
            <td class="w-25 font-bold">Pangkat/ Gol Ruang</td>
            <td class="w-70 font-bold">{{ $pangkat }}</td>
        </tr>
        <tr>
            <td class="w-5 font-bold">4</td>
            <td class="w-25 font-bold">Jabatan</td>
            <td class="w-70 font-bold">{{ $jbtn }}</td>
        </tr>
        <tr>
            <td class="w-5 font-bold">5</td>
            <td class="w-25 font-bold">Unit Kerja</td>
            <td class="w-70 font-bold">{{ $unit }}</td>
        </tr>
    </table>

    <div class="flex-between">
        <div>KEGIATAN BULAN : {{ $bulan->locale('id')->isoFormat('MMMM YYYY') }}</div>
        <div>Jml Hari Kerja : {{ $jml_hari }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>HARI / TANGGAL</th>
                <th>JAM</th>
                <th>URAIAN KEGIATAN</th>
                <th>VOLUME KEGIATAN</th>
                <th>JML MENIT</th>
                <th>KET</th>
            </tr>
            <tr>
                <th></th>
                <th>1</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $currentDate = ''; 
                $dailyTotal = 0;
                $totalMonth = 0;
                $totalTJ = 0;
                $totalTT = 0;
                $no = 1;
                $rowCount = 0;
            @endphp
            
            @forelse($laks as $index => $row)
                @php
                    $start = \Carbon\Carbon::parse($row->tanggal . ' ' . $row->jam_mulai);
                    $end = \Carbon\Carbon::parse($row->tanggal . ' ' . $row->jam_selesai);
                    $minutes = $start->diffInMinutes($end);
                    
                    $isJumat = \Carbon\Carbon::parse($row->tanggal)->dayOfWeekIso == 5;
                    $breakStart = \Carbon\Carbon::parse($row->tanggal . ($isJumat ? ' 11:30:00' : ' 12:00:00'));
                    $breakEnd = \Carbon\Carbon::parse($row->tanggal . ' 13:00:00');
                    
                    $overlapStart = $start->copy()->max($breakStart);
                    $overlapEnd = $end->copy()->min($breakEnd);
                    if ($overlapStart < $overlapEnd) {
                        $minutes -= $overlapStart->diffInMinutes($overlapEnd);
                    }
                    
                    $isNewDate = $currentDate != $row->tanggal;
                    
                    if($isNewDate && $index > 0) {
                        echo '<tr><td colspan="5" class="text-center font-bold">Total Aktivitas Kerja Harian (menit)</td><td class="text-center font-bold">'.$dailyTotal.'</td><td></td></tr>';
                        $dailyTotal = 0;
                        $no++;
                    }
                    $currentDate = $row->tanggal;
                    $dailyTotal += $minutes;
                    $totalMonth += $minutes;
                    
                    $ket = strtolower(trim($row->keterangan ?? ''));
                    if ($ket === 'tj') { $totalTJ += $minutes; }
                    elseif ($ket === 'tt') { $totalTT += $minutes; }
                @endphp
                <tr>
                    @if($isNewDate)
                        <td class="text-center">{{ $no }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                    <td class="text-center">{{ substr($row->jam_mulai, 0, 5) }}-{{ substr($row->jam_selesai, 0, 5) }}</td>
                    <td>{{ $row->uraian_kegiatan }}</td>
                    <td class="text-center">1 Kegiatan</td>
                    <td class="text-center">{{ $minutes }}</td>
                    <td class="text-center">{{ $row->keterangan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data aktivitas kerja bulan ini.</td>
                </tr>
            @endforelse
            
            @if(count($laks) > 0)
                <tr>
                    <td colspan="5" class="text-center font-bold">Total Aktivitas Kerja Harian (menit)</td>
                    <td class="text-center font-bold">{{ $dailyTotal }}</td>
                    <td></td>
                </tr>
            @endif
            
            <!-- Footer Totals -->
            <tr>
                <td colspan="5" class="text-center font-bold">Total Aktivitas TJ (Tugas Jabatan)</td>
                <td class="text-center font-bold">{{ $totalTJ }}</td>
                <td class="text-center font-bold">{{ $totalMonth > 0 ? round($totalTJ / $totalMonth * 100) : 0 }}%</td>
            </tr>
            <tr>
                <td colspan="5" class="text-center font-bold">Total Aktivitas TT (Tugas Tambahan)</td>
                <td class="text-center font-bold">{{ $totalTT }}</td>
                <td class="text-center font-bold">{{ $totalMonth > 0 ? round($totalTT / $totalMonth * 100) : 0 }}%</td>
            </tr>
            <tr>
                <td colspan="5" class="text-center font-bold">Total Aktivitas (TJ + TT)</td>
                <td class="text-center font-bold">{{ $totalMonth }}</td>
                <td class="text-center font-bold"></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right font-bold">Capaian Prestasi Kerja</td>
                <td class="text-center font-bold">100%</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan Block -->
    <table style="width: 100%; margin-top: 40px; page-break-inside: avoid; border: none; font-size: 11pt;">
        <tr>
            <!-- TTD Kiri (Atasan) -->
            <td style="width: 50%; border: none; vertical-align: top; padding: 0; text-align: left;">
                <div style="width: 320px;">
                    <div style="margin-bottom: 3px;">Ditandatangani secara elektronik oleh</div>
                    <div class="ttd-jabatan">{{ $jabatan_atasan }}</div>
                    
                    <br><br>
                    <div class="ttd-placeholder" style="padding-left: 1em;">&nbsp;${ttd_pengirim2}</div>
                    <br><br>

                    <div class="ttd-nama">{{ $nama_atasan }}</div>
                    @if(!empty($pangkat_atasan))
                    <div class="ttd-pangkat">{{ $pangkat_atasan }}</div>
                    @endif
                </div>
            </td>

            <!-- TTD Kanan (Pembuat) -->
            <td style="width: 50%; border: none; vertical-align: top; padding: 0; text-align: right;">
                <div style="width: 320px; display: inline-block; text-align: left;">
                    <div style="margin-bottom: 3px;">Ditandatangani secara elektronik oleh</div>
                    <div class="ttd-jabatan">{{ $jbtn }}</div>
                    
                    <br><br>
                    <div class="ttd-placeholder" style="padding-left: 1em;">&nbsp;${ttd_pengirim1}</div>
                    <br><br>

                    <div class="ttd-nama">{{ $nama_pembuat }}</div>
                    @if(!empty($pangkat))
                    <div class="ttd-pangkat">{{ $pangkat }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Script to Auto Print -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
