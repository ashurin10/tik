<x-app-layout>
    <!-- Alpine.js wrapper for modal state -->
    <div x-data="{ showImportModal: false }" class="p-6">

        @if(session('success'))
            <div
                class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl border-l-4 border-green-500 font-bold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="mb-4 bg-red-50 text-red-700 p-4 rounded-xl border-l-4 border-red-500 font-bold flex items-center gap-3">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Master Data Aset</h1>
                <p class="text-gray-500 text-sm">Kelola seluruh data inventaris aset TIK.</p>
            </div>

            <div class="flex items-center gap-3">
                <button @click="showImportModal = true"
                    class="bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl font-bold border border-gray-200 shadow-sm hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-file-csv"></i> Import CSV
                </button>
                <a href="{{ route('aset.master.data.create') }}"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Aset
                </a>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('aset.master.data.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aset..."
                        class="w-full pl-10 border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                </div>
                <select name="kategori" class="border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Kategori</option>
                    <option value="Hardware" {{ request('kategori') == 'Hardware' ? 'selected' : '' }}>Hardware</option>
                    <option value="Software" {{ request('kategori') == 'Software' ? 'selected' : '' }}>Software</option>
                    <option value="Jaringan" {{ request('kategori') == 'Jaringan' ? 'selected' : '' }}>Jaringan</option>
                </select>
                <select name="status" class="border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Dipinjam" {{ request('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="Rusak" {{ request('status') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
                <button type="submit"
                    class="bg-gray-100 text-gray-600 font-bold px-4 rounded-xl hover:bg-gray-200 transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="p-4 font-bold text-gray-600 text-sm">Kode & Nama</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Kategori</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Kondisi</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Status</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Lokasi & PIC</th>
                            <th class="p-4 font-bold text-gray-600 text-sm text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($assets as $aset)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <div class="font-bold text-gray-800">{{ $aset->nama_aset }}</div>
                                    <div
                                        class="text-xs text-blue-600 font-mono bg-blue-50 inline-block px-2 py-0.5 rounded mt-1">
                                        {{ $aset->kode_aset }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-sm text-gray-600">{{ $aset->kategori }}</span>
                                    <div class="text-xs text-gray-400">{{ $aset->jenis }}</div>
                                </td>
                                <td class="p-4">
                                    @php
                                        $condColor = match ($aset->kondisi) {
                                            'Baik' => 'text-green-600',
                                            'Cukup' => 'text-orange-500',
                                            'Rusak' => 'text-red-600',
                                            default => 'text-gray-600',
                                        };
                                    @endphp
                                    <span class="font-bold text-xs {{ $condColor }}">
                                        <i class="fas fa-circle text-[8px] mr-1"></i> {{ $aset->kondisi }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $statusClass = match ($aset->status) {
                                            'Aktif' => 'bg-green-100 text-green-700',
                                            'Dipinjam' => 'bg-blue-100 text-blue-700',
                                            'Maintenance' => 'bg-orange-100 text-orange-700',
                                            'Pensiun' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusClass }}">
                                        {{ $aset->status }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="text-sm text-gray-800">{{ $aset->lokasi ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $aset->penanggung_jawab }}</div>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('aset.master.data.show', $aset) }}"
                                            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('aset.master.data.edit', $aset) }}"
                                            class="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    <div class="mb-2"><i class="fas fa-box-open text-3xl text-gray-300"></i></div>
                                    Belum ada data aset.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assets->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $assets->withQueryString()->links() }}
                </div>
            @endif
        </div>

        <!-- Import Modal -->
        <div x-show="showImportModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            x-transition>
            <div @click.away="showImportModal = false"
                class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-file-import text-blue-600 mr-2"></i>
                        Import Data Massal Aset</h3>
                    <button @click="showImportModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('aset.master.data.import') }}" method="POST" enctype="multipart/form-data"
                    class="p-6 space-y-6">
                    @csrf
                    <div>
                        <p class="text-sm text-gray-600 mb-4">
                            Sistem hanya dapat membaca struktur format CSV persis dengan template yang disediakan. Nomor
                            aset (INV-YYYY-XXX) akan di-generate secara otomatis oleh sistem.
                        </p>

                        <a href="{{ route('aset.master.data.template') }}"
                            class="inline-flex text-sm text-blue-600 hover:text-blue-800 font-bold mb-6 gap-2 bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 transition-colors">
                            <i class="fas fa-download mt-0.5"></i> Download Template CSV (Contoh Format)
                        </a>

                        <label class="block mb-2 text-sm font-bold text-gray-700">Upload File CSV (.csv) <span
                                class="text-red-500">*</span></label>
                        <input type="file" name="file_import" accept=".csv" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-200 rounded-xl" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showImportModal = false"
                            class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition">Batal</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition">Mulai
                            Import</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>