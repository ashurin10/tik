<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Mingguan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        body {
            font-family: "Arial", sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Kop Surat */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 5px;
            margin-bottom: 2px;
        }
        .kop-surat-inner {
            border-bottom: 3px solid #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .logo-container {
            width: 76px;
            margin-right: 15px;
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
            font-size: 14pt;
            font-weight: normal;
        }
        .kop-text h1 {
            margin: 2px 0 5px 0;
            font-size: 16pt;
            font-weight: bold;
        }
        .kop-text p {
            margin: 0;
            font-size: 10pt;
        }

        /* Info Laporan */
        .info-buku {
            margin-bottom: 10px;
            margin-top: 8px;
            break-inside: avoid;
            break-after: avoid;
        }
        .info-title {
            font-weight: bold;
            font-size: 10pt;
            margin: 0 0 2px 0;
        }
        .info-periode {
            font-weight: bold;
            font-size: 10pt;
            margin: 0;
        }

        /* Table Laporan */
        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 0;
            page-break-inside: auto;
        }
        tbody {
            break-inside: auto;
        }
        .report-end {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .signature-cell {
            border: 0;
            padding: 0;
            white-space: normal;
        }
        tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
            font-size: 9.5pt;
            line-height: 1.25;
            overflow-wrap: anywhere;
            word-break: normal;
        }
        th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            overflow-wrap: normal;
        }
        
        .col-no { width: 3%; text-align: center; }
        .col-tanggal { width: 8%; }
        .col-kegiatan { width: 23%; }
        .col-lokasi { width: 9%; text-align: center; }
        .col-hasil { width: 17%; }
        .col-prioritas { width: 6%; text-align: center; }
        .col-pic { width: 9%; }
        .col-status { width: 6%; text-align: center; }
        .col-tindak { width: 19%; }
        td.col-kegiatan, td.col-lokasi, td.col-hasil, td.col-pic, td.col-tindak {
            white-space: pre-line;
        }

        /* Tanda Tangan */
        .ttd-container {
            width: 100%;
            font-size: 10pt;
            line-height: 1.25;
            margin-top: 6mm;
            display: flow-root;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .ttd-box {
            width: 80mm;
            max-width: 100%;
            margin-left: auto;
            text-align: left;
        }
        .ttd-tanggal {
            margin-bottom: 3px;
        }
        .ttd-jabatan {
            min-height: 15px;
        }
        .ttd-placeholder {
            padding-block: 3lh;
            white-space: pre;
        }
        .ttd-nama {
            font-weight: bold;
            margin-bottom: 2px;
        }


        @media screen {
            html { background: #e5e7eb; }
            body {
                box-sizing: border-box;
                max-width: 297mm;
                margin: 24px auto;
                padding: 12mm;
            }
        }

        @media print {
            .no-print { display: none; }
            th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }
            thead {
                display: table-header-group;
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
        <colgroup>
            <col class="col-no">
            <col class="col-tanggal">
            <col class="col-kegiatan">
            <col class="col-lokasi">
            <col class="col-hasil">
            <col class="col-prioritas">
            <col class="col-pic">
            <col class="col-status">
            <col class="col-tindak">
        </colgroup>
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
                <th class="col-tindak">Keterangan/<br>Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporans as $row)
            <tr>
                <td class="col-no">{{ $loop->iteration }}</td>
                <td class="col-tanggal">{{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td>
                <td class="col-kegiatan">{{ $row->nama_kegiatan }}</td>
                <td class="col-lokasi">{{ $row->lokasi }}</td>
                <td class="col-hasil">{{ $row->hasil_deskripsi ?? '-' }}</td>
                <td class="col-prioritas">{{ $row->prioritas }}</td>
                <td class="col-pic">{{ str_replace(',', ', ', $row->pic) }}</td>
                <td class="col-status">{{ $row->status }}</td>
                <td class="col-tindak">{{ $row->keterangan_tindak_lanjut ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada kegiatan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($penandatangan))
    <div class="ttd-container">
        <div class="ttd-box">
            <div class="ttd-tanggal">Subang, {{ !empty($tanggalTtd) ? \Carbon\Carbon::parse($tanggalTtd)->locale('id')->isoFormat('D MMMM Y') : \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
            <div style="margin-bottom: 3px;">Ditandatangani secara elektronik oleh</div>
            <div class="ttd-jabatan">{{ $jabatan }}</div>
            <div class="ttd-placeholder">    ${ttd_pengirim}</div>
            <div class="ttd-nama">{{ $penandatangan }}</div>
            @if(!empty($pangkat))
            <div class="ttd-pangkat">{{ $pangkat }}</div>
            @endif
        </div>
    </div>
    @endif

    <script>
        // Keep the signature with the final activity, including a continuation of an oversized row.
        function prepareSignature() {
            const signature = document.querySelector('.ttd-container');
            if (!signature || signature.closest('table')) return;

            const table = document.querySelector('table');
            const body = table.tBodies[0];
            const lastRow = body.lastElementChild;
            const end = table.createTBody();
            end.className = 'report-end';

            // Measure at the actual A4 landscape content width, even on a narrow screen.
            const originalWidth = table.style.width;
            table.style.width = '273mm';
            const ruler = document.createElement('div');
            ruler.style.height = '186mm';
            document.body.append(ruler);
            const available = ruler.getBoundingClientRect().height
                - table.tHead.getBoundingClientRect().height
                - signature.getBoundingClientRect().height
                - parseFloat(getComputedStyle(signature).marginTop) - 4;
            ruler.remove();

            if (lastRow.cells.length === 9 && lastRow.getBoundingClientRect().height > available) {
                const tail = lastRow.cloneNode(true);
                tail.setAttribute('aria-label', 'Lanjutan kegiatan terakhir');
                for (const cell of tail.cells) cell.textContent = '';
                end.append(tail);

                for (let i = 0; i < lastRow.cells.length; i++) {
                    const source = lastRow.cells[i];
                    const target = tail.cells[i];
                    const text = source.textContent;
                    target.textContent = text;
                    if (tail.getBoundingClientRect().height <= available) {
                        target.textContent = '';
                        continue;
                    }

                    // Find a suffix that fits, preserving every character of the source text.
                    let low = 0, high = text.length;
                    while (low < high) {
                        const middle = Math.floor((low + high) / 2);
                        target.textContent = text.slice(middle);
                        if (tail.getBoundingClientRect().height <= available) high = middle;
                        else low = middle + 1;
                    }
                    // Prefer a word boundary without making the remaining suffix taller.
                    const space = text.slice(low).search(/\s/);
                    const split = space >= 0 ? low + space + 1 : low;
                    source.textContent = text.slice(0, split);
                    target.textContent = text.slice(split);
                }
            } else {
                end.append(lastRow);
            }

            const cell = end.insertRow().insertCell();
            cell.colSpan = 9;
            cell.className = 'signature-cell';
            cell.append(signature);
            table.style.width = originalWidth;
        }

        window.addEventListener('beforeprint', prepareSignature);
    </script>
</body>
</html>
