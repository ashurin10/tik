<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Mingguan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 20mm 20mm 20mm; /* Extra bottom margin for footer */
        }
        body {
            font-family: "Arial", sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Kop Surat */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: center; /* Center everything horizontally */
            padding-bottom: 5px;
            margin-bottom: 2px;
        }
        .kop-surat-inner {
            border-bottom: 5px solid #000; /* Made bottom bolder to match image */
            padding-bottom: 2px;
            margin-bottom: 20px;
        }
        .logo-container {
            width: 100px;
            margin-right: 15px; /* Sits right next to the text like the image */
            flex-shrink: 0;
        }
        .logo {
            width: 100%;
            height: auto;
        }
        .kop-text {
            text-align: center;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 16pt; /* Make Government font bigger */
            font-weight: normal;
        }
        .kop-text h1 {
            margin: 2px 0 5px 0;
            font-size: 18pt; /* Make Dinosaur bolder/bigger */
            font-weight: bold;
        }
        .kop-text p {
            margin: 0;
            font-size: 11pt; /* Make address slightly bigger */
        }

        /* Info Laporan */
        .info-buku {
            margin-bottom: 15px;
            margin-top: 10px;
        }
        .info-title {
            font-weight: bold;
            font-size: 11pt;
            margin: 0 0 2px 0;
        }
        .info-periode {
            font-weight: bold;
            font-size: 11pt;
            margin: 0;
        }

        /* Table Laporan */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }
        tr { 
            page-break-inside: avoid; 
            page-break-after: auto; 
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 10pt;
        }
        th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        
        .col-no { width: 3%; text-align: center; }
        .col-tanggal { width: 9%; }
        .col-kegiatan { width: 22%; }
        .col-lokasi { width: 12%; text-align: center; }
        .col-hasil { width: 12%; }
        .col-prioritas { width: 7%; text-align: center; }
        .col-pic { width: 12%; }
        .col-status { width: 7%; text-align: center; }
        .col-tindak { width: 16%; }

        /* Tanda Tangan */
        .ttd-container {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
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
            margin-bottom: 35px; /* Space for signature */
            min-height: 15px;
        }
        .ttd-placeholder {
            margin-bottom: 35px;
            white-space: pre;
        }
        .ttd-nama {
            font-weight: bold;
            margin-bottom: 2px;
        }


        /* Watermark (Optional for print robustness) */
        @media print {
            .no-print { display: none; }
            th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Container Utama -->
    <div class="kop-surat-inner">
        <div class="kop-surat">
            <div class="logo-container">
                <!-- Gunakan logo lokal dari folder public/logo.png agar aman dari blokir internet saat mencetak -->
                <img src="{{ asset('logo.png') }}" alt="Logo Subang" class="logo" onerror="this.style.display='none'">
            </div>
            <div class="kop-text">
                <h2>PEMERINTAH DAERAH KABUPATEN SUBANG</h2>
                <h1>DINAS KOMUNIKASI DAN INFORMATIKA</h1>
                <p>Jalan Mayjen Soetoyo Nomor 46, Subang, Jawa Barat 41211</p>
                <p>Telepon (0260) 411318, Faksimile (0260) 411318</p>
            </div>
        </div>
    </div>

    <!-- Informasi Laporan -->
    <div class="info-buku">
        <p class="info-title">Laporan Kegiatan TIK dan Persandian Mingguan</p>
        <p class="info-periode">Periode : {{ \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM Y') }} - {{ \Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM Y') }}</p>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-kegiatan">Nama Kegiatan</th>
                <th class="col-lokasi">Lokasi</th>
                <th class="col-hasil">Hasil/Deskripsi<br>Singkat</th>
                <th class="col-prioritas">Prioritas</th>
                <th class="col-pic">PIC</th>
                <th class="col-status">Status</th>
                <th class="col-tindak">Keterangan/Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporans as $index => $row)
            <tr>
                <td class="col-no">{{ $index + 1 }}</td>
                <td class="col-tanggal">{{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td>
                <td class="col-kegiatan">{{ $row->nama_kegiatan }}</td>
                <td class="col-lokasi">{{ $row->lokasi }}</td>
                <td class="col-hasil">{{ $row->hasil_deskripsi ?? '-' }}</td>
                <td class="col-prioritas">{{ $row->prioritas }}</td>
                <td class="col-pic">{{ str_replace(',', ', ', $row->pic) }}</td>
                <td class="col-status">{{ $row->status }}</td>
                <td class="col-tindak">{{ $row->keterangan_tindak_lanjut ?? '-' }}</td>
            </tr>
            @endforeach
            @if(count($laporans) === 0)
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada kegiatan pada periode ini.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Tanda Tangan Block -->
    @if(!empty($penandatangan))
    <div class="ttd-container">
        <div class="ttd-box">
            <div class="ttd-tanggal">Subang, {{ !empty($tanggalTtd) ? \Carbon\Carbon::parse($tanggalTtd)->locale('id')->isoFormat('D MMMM Y') : \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
            <div style="margin-bottom: 3px;">Ditandatangani secara elektronik oleh</div>
            <div class="ttd-jabatan">{{ $jabatan }}</div>
            
            <br><br>
            <div class="ttd-placeholder" style="padding-left: 1em;">&nbsp;${ttd_pengirim}</div>
            <br><br>

            <div class="ttd-nama">{{ $penandatangan }}</div>
            @if(!empty($pangkat))
            <div class="ttd-pangkat">{{ $pangkat }}</div>
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>
    @endif

</body>
</html>
