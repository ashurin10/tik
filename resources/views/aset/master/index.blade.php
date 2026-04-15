<x-app-layout title="Master Data Aset">
    <div x-data="{ showImportModal: false, showDeleteModal: false, deleteHashid: '', deleteName: '', showQrModal: false }" class="p-6">

        @if(session('success'))
            <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl border-l-4 border-green-500 font-bold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-xl border-l-4 border-red-500 font-bold flex items-center gap-3">
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
                <button @click="showQrModal = true"
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-purple-200 hover:from-purple-700 hover:to-indigo-700 transition flex items-center gap-2">
                    <i class="fas fa-qrcode"></i> Scan QR
                </button>
                <a href="{{ route('aset.master.data.create') }}"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Aset
                </a>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('aset.master.data.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <option value="Terpakai" {{ request('status') == 'Terpakai' ? 'selected' : '' }}>Terpakai</option>
                    <option value="Rusak" {{ request('status') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
                <button type="submit" class="bg-gray-100 text-gray-600 font-bold px-4 rounded-xl hover:bg-gray-200 transition">
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
                                    <div class="text-xs text-blue-600 font-mono bg-blue-50 inline-block px-2 py-0.5 rounded mt-1">
                                        {{ $aset->kode_aset }}
                                    </div>
                                    @if($aset->nomor_seri)
                                        <div class="text-xs text-gray-500 font-mono mt-1">
                                            SN: {{ $aset->nomor_seri }}
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="text-sm text-gray-600">{{ $aset->kategori }}</span>
                                    <div class="text-xs text-gray-400">{{ $aset->jenis }}</div>
                                </td>
                                <td class="p-4">
                                    @php
                                        $condColor = match ($aset->kondisi) {
                                            'Baik'  => 'text-green-600',
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
                                            'Aktif'       => 'bg-green-100 text-green-700',
                                            'Terpakai'    => 'bg-blue-100 text-blue-700',
                                            'Maintenance' => 'bg-orange-100 text-orange-700',
                                            'Pensiun'     => 'bg-red-100 text-red-700',
                                            default       => 'bg-gray-100 text-gray-700',
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
                                            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('aset.master.data.edit', $aset) }}"
                                            class="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button
                                            @click="showDeleteModal = true; deleteHashid = '{{ $aset->hashid }}'; deleteName = '{{ addslashes($aset->nama_aset) }}'"
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

        <!-- ====== Delete Modal ====== -->
        <div x-show="showDeleteModal" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div @click.away="showDeleteModal = false"
                class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-red-50">
                    <h3 class="text-lg font-bold text-red-700"><i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>Konfirmasi Hapus</h3>
                    <button type="button" @click="showDeleteModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-1">Anda yakin ingin menghapus aset:</p>
                    <p class="font-bold text-gray-800 text-lg mb-4" x-text="deleteName"></p>
                    <p class="text-sm text-red-600 bg-red-50 p-3 rounded-xl border border-red-100">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tindakan ini tidak dapat dibatalkan. Semua data terkait aset ini akan dihapus permanen.
                    </p>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                    <button type="button" @click="showDeleteModal = false"
                        class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition">Batal</button>
                    <form :action="'{{ url('aset/master/data') }}/' + deleteHashid" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-200 transition">
                            <i class="fas fa-trash mr-1"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ====== Import Modal ====== -->
        <div x-show="showImportModal" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div @click.away="showImportModal = false"
                class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-file-import text-blue-600 mr-2"></i>Import Data Massal Aset</h3>
                    <button @click="showImportModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form action="{{ route('aset.master.data.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    <div>
                        <p class="text-sm text-gray-600 mb-4">
                            Sistem hanya dapat membaca struktur format CSV persis dengan template yang disediakan. Nomor aset (INV-YYYY-XXX) akan di-generate secara otomatis oleh sistem.
                        </p>
                        <a href="{{ route('aset.master.data.template') }}"
                            class="inline-flex text-sm text-blue-600 hover:text-blue-800 font-bold mb-6 gap-2 bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 transition-colors">
                            <i class="fas fa-download mt-0.5"></i> Download Template CSV (Contoh Format)
                        </a>
                        <label class="block mb-2 text-sm font-bold text-gray-700">Upload File CSV (.csv) <span class="text-red-500">*</span></label>
                        <input type="file" name="file_import" accept=".csv" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-200 rounded-xl" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showImportModal = false"
                            class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition">Batal</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ====== QR Scanner Modal ====== -->
        <div x-show="showQrModal" x-cloak x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm">
            <div @click.away="showQrModal = false; qrClose();"
                class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-purple-50 to-indigo-50 shrink-0">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-qrcode text-purple-600 mr-2"></i>Tambah Aset via QR / Barcode
                    </h3>
                    <button onclick="qrClose()" @click="showQrModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5 overflow-y-auto">

                    <!-- Tab switcher: Upload vs Camera vs Manual -->
                    <div class="flex bg-gray-100 rounded-2xl p-1 gap-1">
                        <button type="button" onclick="qrSwitchTab('upload')" id="tab-upload"
                            class="flex-1 py-2 px-3 rounded-xl text-sm font-bold transition-all bg-white shadow text-purple-700">
                            <i class="fas fa-image mr-1"></i> Upload Foto
                        </button>
                        <button type="button" onclick="qrSwitchTab('camera')" id="tab-camera"
                            class="flex-1 py-2 px-3 rounded-xl text-sm font-bold transition-all text-gray-500 hover:text-gray-700">
                            <i class="fas fa-camera mr-1"></i> Kamera Live
                        </button>
                        <button type="button" onclick="qrSwitchTab('manual')" id="tab-manual"
                            class="flex-1 py-2 px-3 rounded-xl text-sm font-bold transition-all text-gray-500 hover:text-gray-700">
                            <i class="fas fa-keyboard mr-1"></i> Manual
                        </button>
                    </div>

                    <!-- Tab: Upload Foto (default, works on HTTP) -->
                    <div id="qr-tab-upload">
                        <div class="bg-purple-50 p-3 rounded-xl text-purple-700 text-sm flex items-start gap-2 mb-4">
                            <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
                            <div>Foto QR Code / Barcode dari perangkat, lalu upload di sini. Bekerja di semua jaringan tanpa HTTPS.</div>
                        </div>

                        <!-- Drop zone -->
                        <label for="qr-file-input" id="qr-dropzone"
                            class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-purple-300 rounded-2xl cursor-pointer bg-purple-50 hover:bg-purple-100 transition group">
                            <div class="flex flex-col items-center justify-center py-4" id="qr-dropzone-idle">
                                <div class="w-14 h-14 rounded-full bg-white shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-cloud-upload-alt text-purple-500 text-2xl"></i>
                                </div>
                                <p class="text-sm font-bold text-purple-700">Klik atau seret foto QR ke sini</p>
                                <p class="text-xs text-purple-500 mt-1">JPG, PNG, GIF — Max 5MB</p>
                                <p class="text-xs text-gray-400 mt-2">💡 Di HP: tekan & pilih Galeri atau Kamera</p>
                            </div>
                            <div id="qr-dropzone-loading" style="display:none;" class="flex flex-col items-center justify-center py-4">
                                <div class="w-10 h-10 border-4 border-purple-300 border-t-purple-600 rounded-full animate-spin mb-3"></div>
                                <p class="text-sm font-bold text-purple-700">Membaca QR Code…</p>
                            </div>
                        </label>
                        <input type="file" id="qr-file-input" accept="image/*" capture="environment" class="hidden" onchange="qrScanFile(this.files[0])">

                        <!-- File result preview -->
                        <div id="qr-file-preview" class="mt-3" style="display:none;">
                            <img id="qr-preview-img" class="w-full max-h-40 object-contain rounded-xl border border-gray-200 bg-gray-50" />
                        </div>
                    </div>

                    <!-- Tab: Kamera Live (hanya HTTPS) -->
                    <div id="qr-tab-camera" style="display:none;">
                        <div class="bg-yellow-50 p-3 rounded-xl text-yellow-800 text-sm flex items-start gap-2 mb-4">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0"></i>
                            <div>Kamera live hanya bekerja lewat <strong>HTTPS atau localhost</strong>. Jika diakses via IP lokal, gunakan tab <strong>Upload Foto</strong>.</div>
                        </div>
                        <div id="qr-reader" class="rounded-2xl overflow-hidden border-2 border-dashed border-gray-200 bg-gray-900" style="min-height: 280px;"></div>
                    </div>

                    <!-- Tab: Manual -->
                    <div id="qr-tab-manual" style="display:none;">
                        <div class="bg-gray-50 p-3 rounded-xl text-gray-600 text-sm mb-4">
                            Ketik serial number atau kode aset secara manual, lalu klik tombol lanjut.
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="qr-manual-input"
                                class="flex-1 bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 transition-all text-gray-700"
                                placeholder="Contoh: SN-ABC123...">
                            <button type="button" onclick="submitManualQr()"
                                class="bg-purple-600 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 transition">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 ml-1">Tekan Enter untuk lanjut</p>
                    </div>

                    <!-- Result Area (shared) -->
                    <div id="qr-result-area" style="display:none;">
                        <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center shrink-0">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                                <h4 class="font-bold text-green-800">Data Berhasil Terbaca!</h4>
                            </div>
                            <div id="qr-result-content" class="text-sm text-green-700 bg-white rounded-xl p-3 font-mono break-all border border-green-100 mb-3"></div>
                            <button type="button" onclick="qrRedirect()"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-xl transition shadow-lg shadow-green-200">
                                <i class="fas fa-arrow-right mr-2"></i>Lanjut ke Form Tambah Aset
                            </button>
                        </div>
                    </div>

                </div>

                <div class="flex justify-end px-6 py-4 border-t border-gray-100 shrink-0">
                    <button type="button" onclick="qrClose()" @click="showQrModal = false"
                        class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition">Tutup</button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        // ── State ──────────────────────────────────────────────
        let liveScanner = null;
        let liveScannerRunning = false;
        let lastScannedText = '';
        const createUrl = "{{ route('aset.master.data.create') }}";

        // ── Tabs ───────────────────────────────────────────────
        function qrSwitchTab(tab) {
            ['upload', 'camera', 'manual'].forEach(t => {
                document.getElementById('qr-tab-' + t).style.display = 'none';
                const btn = document.getElementById('tab-' + t);
                btn.classList.remove('bg-white', 'shadow', 'text-purple-700');
                btn.classList.add('text-gray-500');
            });

            document.getElementById('qr-tab-' + tab).style.display = 'block';
            const active = document.getElementById('tab-' + tab);
            active.classList.add('bg-white', 'shadow', 'text-purple-700');
            active.classList.remove('text-gray-500');

            // Stop camera if leaving camera tab
            if (tab !== 'camera') {
                stopLiveScanner();
            } else {
                startLiveScanner();
            }

            // Reset result
            document.getElementById('qr-result-area').style.display = 'none';
        }

        // ── Close modal ────────────────────────────────────────
        function qrClose() {
            stopLiveScanner();
            document.getElementById('qr-result-area').style.display = 'none';
            document.getElementById('qr-dropzone-idle').style.display = 'flex';
            document.getElementById('qr-dropzone-loading').style.display = 'none';
            document.getElementById('qr-file-preview').style.display = 'none';
            document.getElementById('qr-file-input').value = '';
            document.getElementById('qr-manual-input').value = '';
            // Reset tab to upload
            qrSwitchTab('upload');
        }

        // ── Upload / File Scan ─────────────────────────────────
        function qrScanFile(file) {
            if (!file) return;

            // Show loading
            document.getElementById('qr-dropzone-idle').style.display = 'none';
            document.getElementById('qr-dropzone-loading').style.display = 'flex';
            document.getElementById('qr-result-area').style.display = 'none';

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('qr-preview-img');
                img.src = e.target.result;
                document.getElementById('qr-file-preview').style.display = 'block';
            };
            reader.readAsDataURL(file);

            // Scan the file
            Html5Qrcode.scanFile(file, true)
                .then(decodedText => {
                    document.getElementById('qr-dropzone-idle').style.display = 'flex';
                    document.getElementById('qr-dropzone-loading').style.display = 'none';
                    onQrSuccess(decodedText);
                })
                .catch(err => {
                    document.getElementById('qr-dropzone-idle').style.display = 'flex';
                    document.getElementById('qr-dropzone-loading').style.display = 'none';
                    alert('QR/Barcode tidak terbaca dari gambar ini.\nCoba foto lebih dekat dan pastikan kode terlihat jelas.\n\nDetail: ' + err);
                    // Reset file input
                    document.getElementById('qr-file-input').value = '';
                    document.getElementById('qr-file-preview').style.display = 'none';
                });
        }

        // Drag & drop support
        document.addEventListener('DOMContentLoaded', () => {
            const dropzone = document.getElementById('qr-dropzone');
            const fileInput = document.getElementById('qr-file-input');

            if (dropzone) {
                dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('border-purple-500', 'bg-purple-100'); });
                dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-purple-500', 'bg-purple-100'));
                dropzone.addEventListener('drop', e => {
                    e.preventDefault();
                    dropzone.classList.remove('border-purple-500', 'bg-purple-100');
                    const file = e.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) qrScanFile(file);
                });
            }

            // Manual input Enter key
            const manualInput = document.getElementById('qr-manual-input');
            if (manualInput) {
                manualInput.addEventListener('keydown', e => {
                    if (e.key === 'Enter') { e.preventDefault(); submitManualQr(); }
                });
            }
        });

        // ── Live Camera Scan ───────────────────────────────────
        function startLiveScanner() {
            if (liveScannerRunning) return;

            const reader = document.getElementById('qr-reader');
            if (!reader) return;

            liveScanner = new Html5Qrcode("qr-reader");
            liveScannerRunning = true;

            liveScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 220, height: 220 }, aspectRatio: 1.0 },
                (text) => { stopLiveScanner(); onQrSuccess(text); },
                () => {}
            ).catch(err => {
                liveScannerRunning = false;
                reader.innerHTML = `
                    <div class="flex flex-col items-center justify-center p-6 text-center" style="min-height:280px;">
                        <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-3">
                            <i class="fas fa-video-slash text-red-500 text-xl"></i>
                        </div>
                        <p class="text-white font-bold mb-1">Kamera tidak dapat diakses</p>
                        <p class="text-yellow-400 text-sm mb-2">Akses kamera memerlukan HTTPS atau localhost.</p>
                        <p class="text-gray-400 text-xs">Gunakan tab <strong class="text-white">Upload Foto</strong> untuk jaringan lokal (IP).</p>
                    </div>`;
            });
        }

        function stopLiveScanner() {
            if (liveScanner && liveScannerRunning) {
                liveScanner.stop().then(() => {
                    liveScannerRunning = false;
                    liveScanner.clear();
                }).catch(() => { liveScannerRunning = false; });
            }
        }

        // ── Manual ─────────────────────────────────────────────
        function submitManualQr() {
            const val = document.getElementById('qr-manual-input').value.trim();
            if (val) onQrSuccess(val);
        }

        // ── On Success ─────────────────────────────────────────
        function onQrSuccess(text) {
            lastScannedText = text;
            document.getElementById('qr-result-area').style.display = 'block';
            document.getElementById('qr-result-content').textContent = text;
        }

        // ── Redirect to create ─────────────────────────────────
        function qrRedirect() {
            if (!lastScannedText) return;

            let params = new URLSearchParams();
            try {
                const json = JSON.parse(lastScannedText);
                const map = {
                    nama: 'nama_aset', name: 'nama_aset', nama_aset: 'nama_aset',
                    sn: 'nomor_seri', serial: 'nomor_seri', serial_number: 'nomor_seri', nomor_seri: 'nomor_seri',
                    merk: 'merk', brand: 'merk',
                    model: 'model_tipe', model_tipe: 'model_tipe',
                    kategori: 'kategori', category: 'kategori',
                    jenis: 'jenis', type: 'jenis',
                    lokasi: 'lokasi', location: 'lokasi',
                };
                for (const [k, v] of Object.entries(json)) {
                    const key = map[k.toLowerCase()] || k;
                    if (v) params.append(key, v);
                }
            } catch {
                // Plain text → nomor seri
                params.append('nomor_seri', lastScannedText);
            }

            window.location.href = createUrl + '?' + params.toString();
        }
    </script>
    @endpush
</x-app-layout>