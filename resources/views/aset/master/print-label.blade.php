<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label: {{ $aset->kode_aset }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,700,800&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f3f4f6;
        }
        .action-buttons {
            position: absolute;
            top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-print { background: #4f46e5; }
        .btn-close { background: #6b7280; }

        .label-wrapper {
            background: white;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .label-table {
            width: 100%;
            max-width: 550px;
            border-collapse: collapse;
            border: 2px solid black;
            color: black;
            font-family: Arial, sans-serif;
        }
        
        .label-table td {
            border: 1px solid black;
            vertical-align: middle;
        }

        .logo-col {
            width: 80px;
            text-align: center;
            border-right: 2px solid black;
            padding: 5px;
        }
        
        .logo-col img {
            max-width: 55px;
            height: auto;
            margin: 5px auto;
            display: block;
        }

        .header-cell {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            border-bottom: 2px solid black;
            padding: 8px;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }

        .content-cell {
            padding: 8px;
            vertical-align: middle;
        }

        .info-table {
            width: 100%;
            border: none;
            font-size: 12px;
            line-height: 1.4;
        }
        .info-table td {
            border: none;
            padding: 3px 2px;
            vertical-align: top;
        }
        .info-table .label {
            width: 85px;
            font-weight: bold;
        }
        .info-table .colon {
            width: 10px;
            font-weight: bold;
        }
        .info-table .value {
            font-weight: normal;
        }

        .qr-col {
            width: 80px;
            text-align: center;
            border-left: 2px solid black;
            padding: 5px;
        }
        .qr-col img {
            width: 100%;
            max-width: 70px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        @media print {
            body { display: block; background: white; margin: 0; }
            .action-buttons { display: none !important; }
            .label-wrapper {
                padding: 0;
                box-shadow: none;
                border-radius: 0;
                width: 40mm;
                height: 15mm;
                overflow: hidden;
                box-sizing: border-box;
            }
            .label-table {
                width: 40mm;
                height: 15mm;
                max-width: 40mm;
                max-height: 15mm;
                border-collapse: collapse;
                border: 0.1mm solid black;
                table-layout: fixed;
            }
            .label-table td {
                padding: 0.2mm;
                border: 0.1mm solid black;
            }
            .logo-col { width: 5mm; text-align: center; border-right: 0.1mm solid black; }
            .logo-col img { max-width: 4mm; max-height: 5.5mm; margin: 0.4mm auto; display: block; object-fit: contain; }
            .header-cell {
                font-size: 1.3mm;
                line-height: 1.1;
                padding: 0.4mm;
                border-bottom: 0.1mm solid black;
                text-align: center;
                font-weight: bold;
            }
            .content-cell {
                padding: 0.4mm;
                vertical-align: top;
            }
            .info-table { font-size: 1.25mm; line-height: 1.1; width: 100%; border: none; table-layout: auto; }
            .info-table td { padding: 0.1mm; border: none; vertical-align: top; }
            .info-table .label { font-weight: bold; white-space: nowrap; width: 1%; }
            .info-table .colon { font-weight: bold; text-align: center; width: 1%; }
            .info-table .value { font-weight: normal; word-wrap: break-word; }
            .qr-col { width: 8.5mm; padding: 0.2mm; border-left: 0.1mm solid black; text-align: center; vertical-align: middle; }
            .qr-col img { width: 7.5mm; height: 7.5mm; margin: 0 auto; display: block; }
            @page {
                size: 40mm 15mm;
                margin: 0;
            }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="action-buttons">
        <button onclick="window.print()" class="btn btn-print">Print Label</button>
        <button onclick="window.close()" class="btn btn-close">Tutup Tab</button>
    </div>

    <div class="label-wrapper">
        <table class="label-table">
            <tr>
                <td rowspan="2" class="logo-col">
                    <img src="/images/logo-subang-new.jpg" alt="Sbg">
                    <img src="/images/logo-diskominfo-new.png" alt="Kominfo">
                </td>
                <td colspan="2" class="header-cell">
                    DISKOMINFO KAB. SUBANG<br>
                    BIDANG TIK DAN PERSANDIAN
                </td>
            </tr>
            <tr>
                <td class="content-cell">
                    <table class="info-table">
                        <tr>
                            <td class="label">KODE ASET</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $aset->kode_aset }}</td>
                        </tr>
                        <tr>
                            <td class="label">NAMA ASET</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $aset->nama_aset }}</td>
                        </tr>
                        <tr>
                            <td class="label">TAHUN ASET</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $aset->tahun_pengadaan ?? (optional($aset->created_at)->format('Y') ?? '-') }}</td>
                        </tr>
                    </table>
                </td>
                <td class="qr-col">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('aset.master.data.show', $aset)) }}" alt="QR Code">
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
