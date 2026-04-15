<x-app-layout title="Penarikan Aset (Masuk)">
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Aset Masuk (Pengembalian / Penarikan)</h1>
            <p class="text-gray-500">Catat penarikan aset dari OPD / Lokasi Pemasangan kembali ke unit IT/Gudang.</p>
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
            
            <!-- Check-In Form -->
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-3xl shadow-xl shadow-gray-200/50 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 text-teal-50 text-8xl opacity-50 select-none">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-3 relative z-10">Form Penarikan Aset</h3>
                    
                    <form action="{{ route('aset.mutasi.checkin.store') }}" method="POST" class="space-y-5 relative z-10">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Aset Terdistribusi (Di Lapangan)</label>
                            <select id="select-aset-tarik" name="peminjaman_id" required class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 font-medium text-gray-700">
                                <option value="">Pilih Aset Keluar...</option>
                                @foreach($peminjaman as $trx)
                                    <option value="{{ $trx->id }}">
                                        [{{ $trx->aset->kode_aset ?? '??' }}] {{ $trx->aset->nama_aset ?? '??' }} 
                                        - Lokasi: {{ $trx->nama_peminjam }}
                                    </option>
                                @endforeach
                            </select>
                            @empty($peminjaman)
                                <p class="text-xs text-red-500 mt-1">Tidak ada aset yang terdistribusi di OPD saat ini.</p>
                            @endempty
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Ditarik</label>
                            <input type="date" name="tanggal_kembali" value="{{ date('Y-m-d') }}" required
                                class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Kondisi Saat Dikembalikan</label>
                            <select name="kondisi_saat_kembali" required class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500">
                                <option value="Baik">Baik</option>
                                <option value="Cukup">Cukup (Minor Defect)</option>
                                <option value="Rusak">Rusak</option>
                            </select>
                        </div>

                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-teal-500 to-emerald-500 text-white font-bold py-3 mt-4 rounded-xl shadow-lg shadow-teal-200 hover:shadow-teal-300 transition-all transform hover:-translate-y-0.5">
                            Selesaikan Penarikan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Return History Table -->
            <div class="md:col-span-2">
                <div class="bg-white p-6 rounded-3xl shadow-xl shadow-gray-200/50">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-3">Riwayat Penarikan Terbaru</h3>
                    
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="p-4 font-bold rounded-l-xl">No. Ref</th>
                                    <th class="p-4 font-bold">Aset / Barang</th>
                                    <th class="p-4 font-bold">Asal Lokasi</th>
                                    <th class="p-4 font-bold">Kondisi / Tgl Tarik</th>
                                    <th class="p-4 font-bold rounded-r-xl">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($riwayatCheckin as $trx)
                                    <tr class="hover:bg-teal-50/50 transition-colors">
                                        <td class="p-4">
                                            <span class="font-mono text-sm text-gray-600">{{ $trx->no_peminjaman }}</span>
                                        </td>
                                        <td class="p-4">
                                            <div class="text-sm font-bold text-gray-800">{{ $trx->aset->nama_aset ?? 'Unknown' }}</div>
                                            <div class="text-xs font-mono text-blue-600">{{ $trx->aset->kode_aset ?? '-' }}</div>
                                        </td>
                                        <td class="p-4 text-sm text-gray-600">{{ $trx->nama_peminjam }}</td>
                                        <td class="p-4 text-xs font-medium">
                                            @php
                                                $kondisiClass = match($trx->kondisi_saat_kembali) {
                                                    'Baik' => 'bg-green-100 text-green-700',
                                                    'Cukup' => 'bg-yellow-100 text-yellow-700',
                                                    'Rusak' => 'bg-red-100 text-red-700',
                                                    default => 'bg-gray-100 text-gray-700'
                                                };
                                            @endphp
                                            <div class="{{ $kondisiClass }} px-2 py-0.5 rounded-lg inline-block shadow-sm">
                                                {{ $trx->kondisi_saat_kembali }}
                                            </div>
                                            <div class="mt-1 text-gray-500">{{ \Carbon\Carbon::parse($trx->tanggal_kembali)->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="p-4">
                                            <span class="text-xs bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-1 rounded-lg font-bold shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> Ditarik/Dikembalikan
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-400">Belum ada riwayat aset dikembalikan/ditarik.</td>
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
            new TomSelect('#select-aset-tarik', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: 'Cari aset yang terdistribusi...',
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
            box-shadow: 0 0 0 2px #14b8a6 !important; /* focus:ring-teal-500 */
        }
    </style>
    @endpush
</x-app-layout>
