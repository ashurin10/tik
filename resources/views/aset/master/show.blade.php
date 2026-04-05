<x-app-layout :title="$aset->nama_aset">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    {{ $aset->nama_aset }}
                    <span
                        class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-lg font-mono">{{ $aset->kode_aset }}</span>
                </h1>
                <p class="text-gray-500 text-sm mt-1">Detail informasi aset.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('aset.master.data.index') }}"
                    class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-xl font-bold hover:bg-gray-50 transition">
                    Kembali
                </a>
                <a href="{{ route('aset.master.data.edit', $aset) }}"
                    class="bg-orange-500 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-orange-200 hover:bg-orange-600 transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Card -->
                <div
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex justify-between items-center">
                    <div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider mb-1">Status Saat Ini</div>
                        <div class="text-2xl font-bold text-gray-800">{{ $aset->status }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider mb-1 text-right">Kondisi
                        </div>
                        <div
                            class="text-2xl font-bold {{ $aset->kondisi == 'Baik' ? 'text-green-600' : ($aset->kondisi == 'Rusak' ? 'text-red-600' : 'text-orange-500') }} text-right">
                            {{ $aset->kondisi }}
                        </div>
                    </div>
                </div>

                <!-- Detail Info -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-lg text-gray-800 mb-6 border-b pb-2">Informasi Aset</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
                        <div>
                            <span
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kategori</span>
                            <span class="text-gray-800 font-medium">{{ $aset->kategori }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tahun Pengadaan</span>
                            <span class="text-gray-800 font-medium">{{ $aset->tahun_pengadaan ?? (optional($aset->created_at)->format('Y') ?? '-') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jenis /
                                Tipe</span>
                            <span class="text-gray-800 font-medium">{{ $aset->jenis }}</span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Merk</span>
                            <span class="text-gray-800 font-medium">{{ $aset->merk ?? '-' }}</span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Model</span>
                            <span class="text-gray-800 font-medium">{{ $aset->model_tipe ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pemilik
                                Aset</span>
                            <span class="text-gray-800 font-medium">{{ $aset->pemilik_aset ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Specifications -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-lg text-gray-800 mb-6 border-b pb-2">Spesifikasi Teknis</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-gray-500">Nomor Seri (SN)</span>
                            <span class="font-mono font-bold text-gray-800">{{ $aset->nomor_seri ?? '-' }}</span>
                        </div>

                        @if(is_array($aset->spesifikasi))
                            @foreach($aset->spesifikasi as $key => $val)
                                <div class="flex justify-between py-2 border-b border-gray-50">
                                    <span class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="font-medium text-gray-800">{{ $val }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-gray-500 italic">Tidak ada data spesifikasi detail.</div>
                        @endif
                    </div>

                    <div class="mt-6">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Catatan</span>
                        <p class="text-gray-600 bg-gray-50 p-4 rounded-xl text-sm leading-relaxed">
                            {{ $aset->catatan ?? 'Tidak ada catatan.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Ownership & History -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col">
                    <h3 class="font-bold text-gray-800 mb-4 w-full text-left">Kode QR Aset</h3>
                    <div class="flex items-start gap-3">
                        <div class="border-4 border-gray-900 rounded-xl p-2 bg-white flex items-center justify-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(route('aset.master.data.show', $aset)) }}"
                                alt="QR Code" class="w-28 object-contain rounded">
                        </div>
                        <a href="{{ route('aset.master.data.print-label', $aset) }}" target="_blank"
                            class="w-10 h-10 mt-1 bg-gray-50 text-gray-500 hover:bg-purple-50 hover:text-purple-600 border border-gray-200 hover:border-purple-200 rounded-xl flex justify-center items-center transition-all shadow-sm"
                            title="Cetak Kode QR">
                            <i class="fas fa-print text-sm"></i>
                        </a>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4">Kepemilikan</h3>

                    <div class="space-y-4">
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Unit
                                Pengguna</span>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-building text-xs"></i>
                                </div>
                                <span class="text-gray-800 font-bold">{{ $aset->unit_pengguna }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Penanggung
                                Jawab</span>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                                    <i class="fas fa-user-tie text-xs"></i>
                                </div>
                                <span class="text-gray-800 font-bold">{{ $aset->penanggung_jawab }}</span>
                            </div>
                        </div>

                        <div>
                            <span
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Lokasi</span>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt text-xs"></i>
                                </div>
                                <span class="text-gray-800 font-bold">{{ $aset->lokasi ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 opacity-70">
                    <h3 class="font-bold text-gray-800 mb-4">Riwayat Mutasi</h3>
                    <div class="text-sm text-gray-500 italic text-center py-4">
                        Fitur Riwayat Mutasi belum diaktifkan (Fase 4).
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>