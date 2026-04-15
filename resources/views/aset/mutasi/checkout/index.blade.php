<x-app-layout title="Aset Keluar (Distribusi)">
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Aset Keluar (Distribusi / Pemasangan)</h1>
            <p class="text-gray-500">Distribusikan / tempatkan aset berstatus 'Aktif' ke OPD atau Lokasi lain secara permanen.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 text-green-700 border-l-4 border-green-500 rounded-r-xl font-medium shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 text-red-700 border-l-4 border-red-500 rounded-r-xl font-medium shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Check-Out Form -->
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-3xl shadow-xl shadow-gray-200/50 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 text-orange-50 text-8xl opacity-50 select-none">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-3 relative z-10">Form Distribusi Aset</h3>
                    
                    <form action="{{ route('aset.mutasi.checkout.store') }}" method="POST" class="space-y-5 relative z-10">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Aset Tersedia Gudang (Aktif)</label>
                            <select id="select-aset" name="aset_id" required class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 font-medium text-gray-700">
                                <option value="">Pilih Aset...</option>
                                @foreach($assets as $aset)
                                    <option value="{{ $aset->id }}">[{{ $aset->kode_aset }}] {{ $aset->nama_aset }} ({{ $aset->kategori }})</option>
                                @endforeach
                            </select>
                            @empty($assets)
                                <p class="text-xs text-red-500 mt-1">Tidak ada aset berstatus Aktif di gudang saat ini.</p>
                            @endempty
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tujuan OPD / Lokasi Pemasangan</label>
                            <input type="text" name="nama_peminjam" required placeholder="Contoh: Diskominfo, Kec. Buleleng"
                                class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Keluar / Pemasangan</label>
                            <input type="date" name="tanggal_pinjam" value="{{ date('Y-m-d') }}" required
                                class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500">
                        </div>

                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold py-3 mt-4 rounded-xl shadow-lg shadow-orange-200 hover:shadow-orange-300 transition-all transform hover:-translate-y-0.5">
                            Proses Aset Keluar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Active Check-Out Table -->
            <div class="md:col-span-2">
                <div class="bg-white p-6 rounded-3xl shadow-xl shadow-gray-200/50">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-3">Daftar Aset Terdistribusi (<span class="text-orange-500">{{ count($riwayatCheckout) }}</span>)</h3>
                    
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="p-4 font-bold rounded-l-xl">No. Ref</th>
                                    <th class="p-4 font-bold">Aset / Barang</th>
                                    <th class="p-4 font-bold">Tujuan / Lokasi</th>
                                    <th class="p-4 font-bold">Tgl. Keluar</th>
                                    <th class="p-4 font-bold rounded-r-xl">Status Terkini</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($riwayatCheckout as $trx)
                                    <tr class="hover:bg-orange-50/50 transition-colors">
                                        <td class="p-4">
                                            <span class="font-mono text-sm text-gray-600 font-bold">{{ $trx->no_peminjaman }}</span>
                                        </td>
                                        <td class="p-4">
                                            <div class="text-sm font-bold text-gray-800">{{ $trx->aset->nama_aset ?? 'Unknown' }}</div>
                                            <div class="text-xs font-mono text-blue-600 bg-blue-50 inline-block px-1 rounded mt-1">{{ $trx->aset->kode_aset ?? '-' }}</div>
                                        </td>
                                        <td class="p-4 text-sm text-gray-800 font-medium">{{ $trx->nama_peminjam }}</td>
                                        <td class="p-4 text-sm text-gray-600">
                                            <i class="far fa-calendar-alt text-gray-400 mr-1"></i> {{ \Carbon\Carbon::parse($trx->tanggal_pinjam)->format('d M Y') }}
                                        </td>
                                        <td class="p-4">
                                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-lg font-bold shadow-sm">Terdistribusi (Masih Terpakai)</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-400">Tidak ada aset yang tercatat terdistribusi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#select-aset', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: 'Ketik nama aset atau kode aset...',
                maxOptions: 50,
            });
        });
    </script>
    <style>
        /* Mengoverride styling bawaan TomSelect agar membaur dengan Tailwind */
        .ts-control {
            border: 0 !important;
            padding: 0.75rem 1rem !important; /* py-3 px-4 */
            border-radius: 0.75rem !important; /* rounded-xl */
            background-color: #f9fafb !important; /* bg-gray-50 */
            box-shadow: none !important;
            font-size: 0.875rem !important; /* text-sm */
        }
        .ts-control.focus {
            box-shadow: 0 0 0 2px #f97316 !important; /* focus:ring-orange-500 */
        }
    </style>
    @endpush
</x-app-layout>
