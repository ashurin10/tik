<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label {{ $aset->kode }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .label-sheet {
                margin: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="font-sans bg-gray-100 text-gray-900 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-xl">
        <div class="no-print flex items-center justify-between mb-4">
            <a href="{{ route('aset-tik.data-aset') }}" class="text-sm font-bold text-gray-600 hover:text-blue-600">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                <i class="fas fa-print"></i>
                Cetak Label
            </button>
        </div>

        <div class="label-sheet bg-white border border-gray-200 rounded-xl shadow-xl p-6">
            <div class="border-2 border-gray-900 rounded-lg p-4">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 pb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Manajemen Aset TIK</p>
                        <h1 class="text-2xl font-bold mt-1">{{ $aset->nama }}</h1>
                        <p class="font-mono text-sm text-gray-600 mt-1">{{ $aset->kode }}</p>
                    </div>
                    <div class="w-24 h-24 border border-gray-300 rounded-lg flex flex-col items-center justify-center text-center p-2">
                        <i class="fas fa-qrcode text-4xl text-gray-700"></i>
                        <span class="font-mono text-[9px] text-gray-600 mt-1 break-all">{{ $aset->tracking_code }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 py-4 text-sm">
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Tracking</p>
                        <p class="font-mono font-bold">{{ $aset->tracking_code }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Serial Number</p>
                        <p class="font-mono">{{ $aset->serial_number ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Kategori</p>
                        <p class="font-semibold">{{ $aset->kategori?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Lokasi</p>
                        <p class="font-semibold">{{ $aset->lokasi?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Kondisi</p>
                        <p class="font-semibold">{{ $aset->kondisi }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-400">Status</p>
                        <p class="font-semibold">{{ $aset->status }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3 text-xs text-gray-500">
                    Scan/cari kode tracking di menu QR/Barcode Tracking untuk melihat detail dan riwayat aset.
                </div>
            </div>
        </div>
    </div>
</body>

</html>
