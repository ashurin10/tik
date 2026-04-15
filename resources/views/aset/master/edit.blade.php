<x-app-layout :title="'Edit Aset — ' . $aset->nama_aset">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Aset: {{ $aset->kode_aset }}</h1>
            </div>
            <a href="{{ route('aset.master.data.show', $aset) }}"
                class="text-gray-500 hover:text-gray-700 font-medium">
                &larr; Batal
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('aset.master.data.update', $aset) }}" method="POST" enctype="multipart/form-data"
            x-data="{ activeTab: 'info' }" class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            @csrf
            @method('PUT')

            <!-- Tabs -->
            <div class="bg-gray-50 border-b border-gray-100 px-6 pt-4 flex gap-6">
                <button type="button" @click="activeTab = 'info'"
                    :class="activeTab === 'info' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="pb-3 font-bold text-sm transition-all focus:outline-none">
                    1. Informasi Dasar
                </button>
                <button type="button" @click="activeTab = 'specs'"
                    :class="activeTab === 'specs' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="pb-3 font-bold text-sm transition-all focus:outline-none">
                    2. Spesifikasi Teknis
                </button>
            </div>

            <div class="p-8">
                <!-- Tab 1: Info Dasar -->
                <div x-show="activeTab === 'info'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Kode Aset / Label Barang <span class="text-xs text-gray-400 font-normal">(Tidak dapat diubah)</span></label>
                            <input type="text" value="{{ $aset->kode_aset }}" readonly disabled
                                class="w-full bg-gray-100 border-0 rounded-xl px-4 py-3 text-sm text-gray-500 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Nama Aset <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_aset" value="{{ old('nama_aset', $aset->nama_aset) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                        </div>
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Tahun Pengadaan</label>
                            <input type="number" name="tahun_pengadaan" value="{{ old('tahun_pengadaan', $aset->tahun_pengadaan) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="YYYY" min="1900" max="{{ date('Y') + 1 }}">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Kategori <span
                                    class="text-red-500">*</span></label>
                            <select name="kategori"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                                @foreach(['Hardware', 'Software', 'Jaringan', 'Aksesoris'] as $kat)
                                    <option value="{{ $kat }}" {{ old('kategori', $aset->kategori) == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Jenis / Tipe <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="jenis" value="{{ old('jenis', $aset->jenis) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Merk / Brand</label>
                            <input type="text" name="merk" value="{{ old('merk', $aset->merk) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Model / Tipe Unit</label>
                            <input type="text" name="model_tipe" value="{{ old('model_tipe', $aset->model_tipe) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Kondisi <span
                                    class="text-red-500">*</span></label>
                            <select name="kondisi"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                                @foreach(['Baik', 'Cukup', 'Rusak'] as $kond)
                                    <option value="{{ $kond }}" {{ old('kondisi', $aset->kondisi) == $kond ? 'selected' : '' }}>{{ $kond }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Status Aset <span
                                    class="text-red-500">*</span></label>
                            <select name="status"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                                @foreach(['Aktif', 'Terpakai', 'Maintenance', 'Pensiun'] as $stat)
                                    <option value="{{ $stat }}" {{ old('status', $aset->status) == $stat ? 'selected' : '' }}>
                                        {{ $stat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-2 md:col-span-2">
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Pemilik Aset</label>
                            <input type="text" name="pemilik_aset"
                                value="{{ old('pemilik_aset', $aset->pemilik_aset) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="PT. ABC, Pribadi, Klien, dll">
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Unit Pengguna <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="unit_pengguna"
                                value="{{ old('unit_pengguna', $aset->unit_pengguna) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Penanggung Jawab (PIC) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="penanggung_jawab"
                                value="{{ old('penanggung_jawab', $aset->penanggung_jawab) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Lokasi / Ruangan</label>
                            <input type="text" name="lokasi" value="{{ old('lokasi', $aset->lokasi) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="button" @click="activeTab = 'specs'"
                            class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition">
                            Lanjut: Spesifikasi &rarr;
                        </button>
                    </div>
                </div>

                <!-- Tab 2: Spesifikasi -->
                <div x-show="activeTab === 'specs'" style="display: none;" class="space-y-6">
                    @php
                        $specs = $aset->spesifikasi ?? [];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Nomor Seri (SN)</label>
                            <input type="text" name="nomor_seri" value="{{ old('nomor_seri', $aset->nomor_seri) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Processor / CPU</label>
                            <input type="text" name="spesifikasi[cpu]"
                                value="{{ old('spesifikasi.cpu', $specs['cpu'] ?? '') }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Memory (RAM)</label>
                            <input type="text" name="spesifikasi[ram]"
                                value="{{ old('spesifikasi.ram', $specs['ram'] ?? '') }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Storage (HDD/SSD)</label>
                            <input type="text" name="spesifikasi[storage]"
                                value="{{ old('spesifikasi.storage', $specs['storage'] ?? '') }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Operating System</label>
                            <input type="text" name="spesifikasi[os]"
                                value="{{ old('spesifikasi.os', $specs['os'] ?? '') }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">IP Address</label>
                            <input type="text" name="spesifikasi[ip]"
                                value="{{ old('spesifikasi.ip', $specs['ip'] ?? '') }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Catatan Tambahan</label>
                        <textarea name="catatan" rows="3"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">{{ old('catatan', $aset->catatan) }}</textarea>
                    </div>

                    <div class="flex justify-between mt-8 border-t border-gray-100 pt-6">
                        <button type="button" @click="activeTab = 'info'"
                            class="text-gray-600 font-bold hover:text-gray-800">
                            &larr; Kembali
                        </button>
                        <button type="submit"
                            class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-2"></i> Update Data Aset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>