<x-app-layout title="Tambah Aset Baru">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Registrasi Aset Baru</h1>
                <p class="text-gray-500 text-sm">Tambahkan aset TIK baru ke dalam inventaris.</p>
            </div>
            <a href="{{ route('aset.master.data.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">
                &larr; Kembali
            </a>
        </div>

        {{-- QR scan prefill notice --}}
        @if(request('nomor_seri') || request('nama_aset') || request('merk'))
            <div class="mb-4 bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-xl flex items-center gap-3">
                <i class="fas fa-qrcode text-purple-600 text-lg"></i>
                <div>
                    <p class="text-sm font-bold text-purple-800">Data dari QR/Barcode berhasil dimuat!</p>
                    <p class="text-xs text-purple-600">Periksa dan lengkapi field lainnya sebelum menyimpan.</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">Terdapat kesalahan input:</h3>
                        <ul class="list-disc list-inside text-sm text-red-700 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('aset.master.data.store') }}" method="POST" enctype="multipart/form-data"
            x-data="{ activeTab: '{{ (request('nomor_seri') && !request('nama_aset')) ? 'specs' : 'info' }}' }" class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            @csrf

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
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Nama Aset <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_aset" value="{{ old('nama_aset', request('nama_aset')) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="Contoh: Laptop Dell Latitude 5520" required>
                        </div>
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Tahun Pengadaan</label>
                            <input type="number" name="tahun_pengadaan" value="{{ old('tahun_pengadaan', request('tahun_pengadaan', date('Y'))) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="YYYY" min="1900" max="{{ date('Y') + 1 }}">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Kategori <span
                                    class="text-red-500">*</span></label>
                            @php $qrKategori = old('kategori', request('kategori')); @endphp
                            <select name="kategori"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                                <option value="">Pilih Kategori</option>
                                <option value="Hardware" {{ $qrKategori == 'Hardware' ? 'selected' : '' }}>Hardware
                                </option>
                                <option value="Software" {{ $qrKategori == 'Software' ? 'selected' : '' }}>Software
                                </option>
                                <option value="Jaringan" {{ $qrKategori == 'Jaringan' ? 'selected' : '' }}>Jaringan
                                </option>
                                <option value="Aksesoris" {{ $qrKategori == 'Aksesoris' ? 'selected' : '' }}>Aksesoris
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Jenis / Tipe <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="jenis" value="{{ old('jenis', request('jenis')) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="Laptop, Server, Router, dll" required>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Merk / Brand</label>
                            <input type="text" name="merk" value="{{ old('merk', request('merk')) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="Dell, HP, Lenovo, Cisco, dll">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Model / Tipe Unit</label>
                            <input type="text" name="model_tipe" value="{{ old('model_tipe', request('model_tipe')) }}"
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
                                <option value="Baik" {{ old('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Cukup" {{ old('kondisi') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                <option value="Rusak" {{ old('kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Status Aset <span
                                    class="text-red-500">*</span></label>
                            <select name="status"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required>
                                <option value="Aktif" {{ old('status', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="Dipinjam" {{ old('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam
                                </option>
                                <option value="Maintenance" {{ old('status') == 'Maintenance' ? 'selected' : '' }}>
                                    Maintenance</option>
                                <option value="Pensiun" {{ old('status') == 'Pensiun' ? 'selected' : '' }}>Pensiun
                                </option>
                            </select>
                        </div>

                        <div class="col-span-2 md:col-span-2">
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Pemilik Aset</label>
                            <input type="text" name="pemilik_aset" value="{{ old('pemilik_aset', request('pemilik_aset')) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="PT. ABC, Pribadi, Klien, dll">
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Unit Pengguna <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="unit_pengguna" value="{{ old('unit_pengguna', request('unit_pengguna')) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required placeholder="Divisi/Bagian">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Penanggung Jawab (PIC) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab', request('penanggung_jawab')) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                required placeholder="Nama PIC">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Lokasi / Ruangan</label>
                            <input type="text" name="lokasi" value="{{ old('lokasi', request('lokasi')) }}"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700"
                                placeholder="Gedung A, Lt 2, R. Server">
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
                    <div class="bg-blue-50 p-4 rounded-xl text-blue-800 text-sm mb-4 flex items-start gap-2">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <div>
                            Spesifikasi teknis bersifat fleksibel. Isi field yang relevan saja.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Nomor Seri (SN)</label>
                            <div class="flex gap-2">
                                <input type="text" name="nomor_seri" id="input-nomor-seri" value="{{ old('nomor_seri', request('nomor_seri')) }}"
                                    class="flex-1 bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700 {{ request('nomor_seri') ? 'ring-2 ring-purple-400 bg-purple-50' : '' }}"
                                    placeholder="Serial Number Pabrikan">
                                <button type="button" onclick="openInlineScanner()"
                                    class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white px-4 py-3 rounded-xl hover:from-purple-600 hover:to-indigo-600 transition shadow-md shadow-purple-200"
                                    title="Scan QR/Barcode">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Processor / CPU</label>
                            <input type="text" name="spesifikasi[cpu]"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Memory (RAM)</label>
                            <input type="text" name="spesifikasi[ram]"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Storage (HDD/SSD)</label>
                            <input type="text" name="spesifikasi[storage]"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Operating System</label>
                            <input type="text" name="spesifikasi[os]"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">IP Address</label>
                            <input type="text" name="spesifikasi[ip]"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Catatan Tambahan</label>
                        <textarea name="catatan" rows="3"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex justify-between mt-8 border-t border-gray-100 pt-6">
                        <button type="button" @click="activeTab = 'info'"
                            class="text-gray-600 font-bold hover:text-gray-800">
                            &larr; Kembali
                        </button>
                        <button type="submit"
                            class="bg-green-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-green-200 hover:bg-green-700 transition">
                            <i class="fas fa-save mr-2"></i> Simpan Aset Baru
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Inline QR Scanner Modal (for create page) -->
        <div id="inline-qr-modal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-purple-50 to-indigo-50">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-qrcode text-purple-600 mr-2"></i>
                        Scan Nomor Seri
                    </h3>
                    <button onclick="closeInlineScanner()" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div id="inline-qr-reader" class="rounded-2xl overflow-hidden border-2 border-dashed border-gray-200 bg-gray-900" style="min-height: 280px;"></div>
                    <p class="text-xs text-gray-500 text-center">Arahkan kamera ke QR Code / Barcode serial number</p>
                </div>
                <div class="flex justify-end px-6 py-4 border-t border-gray-100">
                    <button type="button" onclick="closeInlineScanner()"
                        class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let inlineScanner = null;
        let inlineScannerRunning = false;

        function openInlineScanner() {
            document.getElementById('inline-qr-modal').style.display = 'flex';

            setTimeout(() => {
                if (inlineScannerRunning) return;

                inlineScanner = new Html5Qrcode("inline-qr-reader");
                inlineScannerRunning = true;

                inlineScanner.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 220, height: 220 },
                        aspectRatio: 1.0,
                    },
                    (decodedText) => {
                        // Got a scan! Fill the SN field
                        document.getElementById('input-nomor-seri').value = decodedText;
                        document.getElementById('input-nomor-seri').classList.add('ring-2', 'ring-green-400', 'bg-green-50');

                        closeInlineScanner();
                    },
                    (error) => { /* scanning... */ }
                ).catch((err) => {
                    inlineScannerRunning = false;
                    document.getElementById('inline-qr-reader').innerHTML = `
                        <div class="flex flex-col items-center justify-center p-6 text-center" style="min-height: 280px;">
                            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-3">
                                <i class="fas fa-video-slash text-red-500 text-xl"></i>
                            </div>
                            <p class="text-white font-bold mb-1">Kamera Tidak Tersedia</p>
                            <p class="text-gray-400 text-xs">${err}</p>
                        </div>
                    `;
                });
            }, 300);
        }

        function closeInlineScanner() {
            if (inlineScanner && inlineScannerRunning) {
                inlineScanner.stop().then(() => {
                    inlineScannerRunning = false;
                    inlineScanner.clear();
                }).catch(() => {
                    inlineScannerRunning = false;
                });
            }
            document.getElementById('inline-qr-modal').style.display = 'none';
        }
    </script>
    @endpush
</x-app-layout>