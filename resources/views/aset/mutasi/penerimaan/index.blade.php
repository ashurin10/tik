<x-app-layout title="Aset Masuk (Pengadaan)">
    <div x-data="{ showImportModal: false }" class="p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Aset Masuk (Penerimaan)</h1>
                <p class="text-gray-500 text-sm">Catat & registrasikan batch aset baru, dan histori masuknya.</p>
            </div>

            <div class="flex items-center gap-3">
                <button @click="showImportModal = true"
                    class="bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl font-bold border border-gray-200 shadow-sm hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-file-csv"></i> Import Aset Masuk
                </button>
                <a href="{{ route('aset.mutasi.penerimaan.create') }}"
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 hover:shadow-blue-300 transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Aset Masuk
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 text-green-700 border-l-4 border-green-500 rounded-r-xl font-medium shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 text-red-700 border-l-4 border-red-500 rounded-r-xl font-medium shadow-sm flex items-start gap-3">
                <i class="fas fa-exclamation-triangle mt-1"></i>
                <div class="flex-1">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Table Area -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            <h3 class="text-lg font-bold text-gray-800 p-6 border-b border-gray-100">Riwayat Batch Penerimaan (<span class="text-blue-600">{{ count($riwayatPenerimaan) }}</span>)</h3>
            
            <div class="overflow-x-auto custom-scrollbar p-6 pt-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4 font-bold rounded-l-xl">No. Transaksi</th>
                            <th class="p-4 font-bold">Tanggal</th>
                            <th class="p-4 font-bold">Sumber</th>
                            <th class="p-4 font-bold">Diterima Oleh</th>
                            <th class="p-4 font-bold rounded-r-xl">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($riwayatPenerimaan as $item)
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td class="p-4">
                                    <span class="font-mono text-sm text-blue-600 font-bold bg-blue-50 px-2 py-1 rounded border border-blue-100 shadow-sm">{{ $item->no_transaksi }}</span>
                                </td>
                                <td class="p-4 text-sm text-gray-600 font-medium whitespace-nowrap">
                                    <i class="far fa-calendar-alt mr-1 text-gray-400"></i> {{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') }}
                                </td>
                                <td class="p-4">
                                    @php
                                        $sumberClass = match($item->sumber_aset) {
                                            'Pembelian Baru' => 'bg-green-100 text-green-700 border border-green-200',
                                            'Mutasi Masuk Pindah HO' => 'bg-orange-100 text-orange-700 border border-orange-200',
                                            'Hibah / Hadiah' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                            default => 'bg-gray-100 text-gray-700 border border-gray-200'
                                        };
                                    @endphp
                                    <span class="text-xs px-2 py-1 rounded-md font-bold shadow-sm whitespace-nowrap {{ $sumberClass }}">{{ $item->sumber_aset }}</span>
                                </td>
                                <td class="p-4 text-sm text-gray-800 font-bold whitespace-nowrap">{{ $item->diterima_oleh }}</td>
                                <td class="p-4 text-xs text-gray-500 max-w-sm truncate" title="{{ $item->keterangan }}">
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-gray-400">
                                    <div class="mb-3"><i class="fas fa-box-open text-4xl text-gray-200"></i></div>
                                    Belum ada riwayat penerimaan barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====== Import Modal ====== -->
        <div x-show="showImportModal" x-transition x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div @click.away="showImportModal = false"
                class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-file-import text-blue-600 mr-2"></i>Import Aset Masuk Baru</h3>
                    <button @click="showImportModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Need to post to penerimaan.import -->
                <form action="{{ route('aset.mutasi.penerimaan.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[0.8rem] text-gray-700 font-bold mb-1">Sumber Aset <span class="text-red-500">*</span></label>
                        <select name="sumber_aset" required class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Sumber...</option>
                            <option value="Pembelian Baru">Pembelian Baru</option>
                            <option value="Mutasi Masuk Pindah HO">Mutasi Masuk / Pindahan</option>
                            <option value="Hibah / Hadiah">Hibah / Hadiah</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[0.8rem] text-gray-700 font-bold mb-1">Tanggal Masuk <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_masuk" value="{{ date('Y-m-d') }}" required
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[0.8rem] text-gray-700 font-bold mb-1">Diterima Oleh <span class="text-red-500">*</span></label>
                            <input type="text" name="diterima_oleh" value="{{ Auth::user()->name ?? '' }}" required
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="border-t border-gray-100 pt-4 mt-2">
                        <p class="text-xs text-gray-600 mb-3 bg-blue-50 p-3 rounded-xl border border-blue-100">
                            <strong>Note:</strong> File CSV harus sama dengan struktur template <a href="{{ route('aset.master.data.template') }}" class="text-blue-600 font-bold hover:underline">Import Master Aset</a>. 
                            Setiap baris di CSV akan di-tambahkan sebagai aset baru.
                        </p>
                        <label class="block mb-2 text-[0.8rem] font-bold text-gray-700">Upload File CSV (.csv) <span class="text-red-500">*</span></label>
                        <input type="file" name="file_import" accept=".csv" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 border border-gray-200 rounded-xl bg-[#f4f5f7]" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showImportModal = false"
                            class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition">Batal</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition">
                            Proses Data Aset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
