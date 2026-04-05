<x-app-layout title="Laporan Mingguan">
    <div x-data="{ 
        showModal: false, 
        editId: null,
        deleteId: null,
        showDeleteModal: false,
        showExportModal: false,
        filterBulan: '',
        filterSearch: '',
        matchesFilter(tanggal, searchTarget) {
            const bulanOk = !this.filterBulan || tanggal.startsWith(this.filterBulan);
            const searchOk = !this.filterSearch.trim() || searchTarget.toLowerCase().includes(this.filterSearch.toLowerCase());
            return bulanOk && searchOk;
        },
        exportForm: {
            start_date: '',
            end_date: '',
            penandatangan: '',
            jabatan: ''
        },
        showAiModal: false,
        rawText: '',
        isAnalyzing: false,
        openAiModal() {
            this.rawText = '';
            this.showAiModal = true;
        },
        async parseRawText() {
            if (!this.rawText.trim()) return;
            this.isAnalyzing = true;
            try {
                const response = await fetch('{{ route('laporan-mingguan.parse-text') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ text: this.rawText })
                });
                const data = await response.json();
                
                this.form.tanggal = data.tanggal || '';
                this.form.nama_kegiatan = data.nama_kegiatan || '';
                this.form.lokasi = data.lokasi || '';
                this.form.prioritas = data.prioritas || 'Sedang';
                this.form.status = data.status || 'Berjalan';
                this.form.hasil_deskripsi = data.hasil_deskripsi || '';
                this.form.keterangan_tindak_lanjut = data.keterangan_tindak_lanjut || '';
                
                this.pics = data.pic ? data.pic.split(/(?:dan|&|,)/i).map(p => p.trim()).filter(p => p) : [];
                
                this.showAiModal = false;
                this.editId = null; 
                this.isSubmitted = false;
                this.showModal = true;
            } catch (error) {
                console.error('Error parsing text:', error);
                alert('Terjadi kesalahan saat mengekstrak data.');
            } finally {
                this.isAnalyzing = false;
            }
        },
        pics: [],  
        newPic: '', 
        suggestions: [],
        showSuggestions: false,
        kegiatanSuggestions: [],
        showKegiatanSuggestions: false,
        isSubmitted: false,
        form: {
            tanggal: '',
            nama_kegiatan: '',
            lokasi: '',
            prioritas: 'Sedang',
            status: 'Berjalan',
            hasil_deskripsi: '',
            keterangan_tindak_lanjut: ''
        },
        openCreateModal() {
            this.editId = null;
            this.pics = [];
            this.form.tanggal = '';
            this.form.nama_kegiatan = '';
            this.form.lokasi = '';
            this.form.prioritas = 'Sedang';
            this.form.status = 'Berjalan';
            this.form.hasil_deskripsi = '';
            this.form.keterangan_tindak_lanjut = '';
            this.isSubmitted = false;
            this.showModal = true;
        },
        openEditModal(data) {
            this.editId = data.hashid;
            this.form.tanggal = data.tanggal;
            this.form.nama_kegiatan = data.nama_kegiatan;
            this.form.lokasi = data.lokasi;
            this.form.prioritas = data.prioritas;
            this.form.status = data.status;
            this.form.hasil_deskripsi = data.hasil_deskripsi || '';
            this.form.keterangan_tindak_lanjut = data.keterangan_tindak_lanjut || '';
            
            // Handle PICs array
            this.pics = data.pic ? data.pic.split(',').map(p => p.trim()).filter(p => p) : [];
            
            this.isSubmitted = false;
            this.showModal = true;
        },
        addPic() {  
            if(this.newPic.trim() !== '') { 
                this.pics.push(this.newPic.trim()); 
                this.newPic = ''; 
                this.showSuggestions = false;
            } 
        }, 
        removePic(index) { 
            this.pics.splice(index, 1); 
        },
        async fetchSuggestions() {
            if (this.newPic.trim().length === 0) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }
            try {
                const response = await fetch(`/laporan-mingguan/pics/search?q=${encodeURIComponent(this.newPic)}`);
                const data = await response.json();
                this.suggestions = data;
                this.showSuggestions = data.length > 0;
            } catch (error) {
                console.error('Error fetching PIC suggestions:', error);
            }
        },
        selectSuggestion(suggestion) {
            this.newPic = suggestion;
            this.addPic();
        },
        async fetchKegiatanSuggestions() {
            if (this.form.nama_kegiatan.trim().length === 0) {
                this.kegiatanSuggestions = [];
                this.showKegiatanSuggestions = false;
                return;
            }
            try {
                const response = await fetch(`/laporan-mingguan/kegiatan/search?q=${encodeURIComponent(this.form.nama_kegiatan)}`);
                const data = await response.json();
                this.kegiatanSuggestions = data;
                this.showKegiatanSuggestions = data.length > 0;
            } catch (error) {
                console.error('Error fetching Kegiatan suggestions:', error);
            }
        },
        selectKegiatanSuggestion(kegiatan) {
            this.form.nama_kegiatan = kegiatan.nama_kegiatan;
            this.form.lokasi = kegiatan.lokasi;
            this.form.prioritas = kegiatan.prioritas;
            this.form.status = kegiatan.status;
            this.form.hasil_deskripsi = kegiatan.hasil_deskripsi || '';
            this.form.keterangan_tindak_lanjut = kegiatan.keterangan_tindak_lanjut || '';
            
            // Auto-fill PIC list as well
            if (kegiatan.pic) {
                this.pics = kegiatan.pic.split(',').map(p => p.trim()).filter(p => p);
            }
            
            this.showKegiatanSuggestions = false;
        },
        submitForm(e) {
            this.isSubmitted = true;
            // Validate required fields
            if (!this.form.tanggal || !this.form.nama_kegiatan || !this.form.lokasi || this.pics.length === 0) {
                return; // Stop submission if invalid
            }
            e.target.submit(); // Submit via HTML form natively
        },
        openDeleteModal(id) {
            this.deleteId = id;
            this.showDeleteModal = true;
        },
        closeDeleteModal() {
            this.deleteId = null;
            this.showDeleteModal = false;
        },
        async deleteLaporan() {
            if (!this.deleteId) return;
            
            try {
                const response = await fetch(`/laporan-mingguan/${this.deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        const row = document.getElementById(`row-${this.deleteId}`);
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';
                            setTimeout(() => row.remove(), 300);
                        }
                    }
                }
            } catch (error) {
                console.error('Error deleting data:', error);
                alert('Terjadi kesalahan saat menghapus data.');
            } finally {
                this.closeDeleteModal();
            }
        }
    }" class="p-6">

        @if(session('success'))
            <div
                class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl border-l-4 border-green-500 font-bold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Laporan Mingguan</h1>
                <p class="text-gray-500 text-sm">Kelola dan ekspor data laporan mingguan.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="openAiModal()"
                    class="bg-purple-600 text-white px-4 py-2.5 rounded-xl font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 transition flex items-center gap-2">
                    <i class="fas fa-magic"></i> Ekstrak Teks
                </button>
                <button type="button" @click="showExportModal = true"
                    class="bg-white text-gray-700 px-5 py-2.5 rounded-xl font-bold border border-gray-200 shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                    <i class="fas fa-print text-blue-600"></i> Cetak Laporan
                </button>
                <button @click="openCreateModal()"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Data
                </button>
            </div>
        </div>

        <!-- Live Filter Bar -->
        <div class="bg-white rounded-2xl shadow-md shadow-gray-200/40 border border-gray-100 px-5 py-4 mb-5 flex flex-col sm:flex-row items-center gap-3">
            <div class="flex items-center gap-2 flex-1 w-full">
                <label class="text-[0.7rem] font-bold text-gray-500 whitespace-nowrap"><i class="fas fa-calendar-alt mr-1 text-blue-400"></i>Bulan</label>
                <input type="month" x-model="filterBulan"
                    class="bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
            </div>
            <div class="flex items-center gap-2 flex-1 w-full">
                <label class="text-[0.7rem] font-bold text-gray-500 whitespace-nowrap"><i class="fas fa-search mr-1 text-blue-400"></i>Cari</label>
                <input type="text" x-model="filterSearch"
                    placeholder="Nama kegiatan, lokasi, atau PIC…"
                    class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
            </div>
            <button type="button" x-show="filterBulan || filterSearch" @click="filterBulan = ''; filterSearch = ''"
                class="shrink-0 bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-200 transition flex items-center gap-2">
                <i class="fas fa-times text-xs"></i> Reset
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="p-4 font-bold text-gray-600 text-sm">Tanggal</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Nama Kegiatan</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Lokasi</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Hasil / Deskripsi</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Prioritas</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">PIC</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Status</th>
                            <th class="p-4 font-bold text-gray-600 text-sm">Tindak Lanjut</th>
                            <th class="p-4 font-bold text-gray-600 text-sm text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $dupKeys = $duplicateKeys ?? [];
                        @endphp
                        @forelse($laporans as $row)
                            @php
                                $key = implode('|', [
                                    $row->tanggal,
                                    $row->nama_kegiatan,
                                    $row->lokasi,
                                    $row->hasil_deskripsi,
                                    $row->prioritas,
                                    $row->pic,
                                    $row->status,
                                    $row->keterangan_tindak_lanjut
                                ]);
                                $isDuplicate = in_array($key, $dupKeys);
                                $searchTarget = strtolower($row->nama_kegiatan . ' ' . $row->lokasi . ' ' . $row->pic);
                            @endphp
                            <tr id="row-{{ $row->hashid }}"
                                x-show="matchesFilter('{{ $row->tanggal }}', '{{ addslashes($searchTarget) }}')"
                                class="hover:bg-gray-50 transition group {{ $isDuplicate ? 'bg-amber-50 hover:bg-amber-100' : '' }}">
                                <td class="p-4 text-sm text-gray-800 whitespace-nowrap">{{ $row->tanggal }}</td>
                                <td class="p-4 text-sm font-bold text-gray-800">
                                    {{ $row->nama_kegiatan }}
                                    @if($isDuplicate)
                                        <span class="ml-1.5 inline-flex items-center gap-1 text-[0.6rem] font-bold text-amber-700 bg-amber-100 border border-amber-300 px-1.5 py-0.5 rounded-md" title="Terdapat kegiatan dengan data yang sama persis (semua kolom)">
                                            <i class="fas fa-exclamation-triangle text-[0.55rem]"></i> Duplikat
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm text-gray-600">{{ $row->lokasi }}</td>
                                <td class="p-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $row->hasil_deskripsi }}">
                                    {{ $row->hasil_deskripsi ?? '-' }}
                                </td>
                                <td class="p-4 text-sm text-gray-800">
                                    @php
                                        $prioritasColor = match ($row->prioritas) {
                                            'Tinggi' => 'text-red-600 bg-red-100',
                                            'Sedang' => 'text-orange-600 bg-orange-100',
                                            'Rendah' => 'text-green-600 bg-green-100',
                                            default => 'text-gray-600 bg-gray-100'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $prioritasColor }}">
                                        {{ $row->prioritas }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-800">{{ $row->pic }}</td>
                                <td class="p-4 text-sm text-gray-800">
                                    @php
                                        $statusClass = match ($row->status) {
                                            'Selesai' => 'bg-green-100 text-green-700',
                                            'Berjalan' => 'bg-blue-100 text-blue-700',
                                            'Tertunda' => 'bg-orange-100 text-orange-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusClass }}">
                                        {{ $row->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-600">{{ $row->keterangan_tindak_lanjut ?? '-' }}</td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click='openEditModal(@json($row))'
                                            class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100/50 shadow-sm flex items-center justify-center"
                                            title="Edit Laporan">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="openDeleteModal('{{ $row->hashid }}')"
                                            class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors border border-red-100/50 shadow-sm flex items-center justify-center"
                                            title="Hapus Laporan">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-gray-500">
                                    <div class="mb-2"><i class="fas fa-folder-open text-3xl text-gray-300"></i></div>
                                    Belum ada data laporan mingguan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Data Modal -->
        <div x-show="showModal" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            x-transition>
            <div @click.away="showModal = false"
                class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100/50 w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div
                    class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white shadow-sm z-10">
                    <div>
                        <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1"
                            x-text="editId ? 'EDIT DATA LAPORAN' : 'PENGISIAN DATA BARU'"></p>
                        <h3 class="text-xl font-bold text-[#111827]"
                            x-text="editId ? 'Edit Laporan Mingguan' : 'Tambah Laporan Mingguan'"></h3>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="overflow-y-auto p-8" class="custom-scrollbar">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-xl border-l-4 border-red-500 text-sm">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form :action="editId ? `/laporan-mingguan/${editId}` : '{{ route('laporan-mingguan.store') }}'"
                        method="POST" id="form-laporan" class="space-y-4" @submit.prevent="submitForm">
                        @csrf
                        <template x-if="editId">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Tanggal -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Tanggal <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="tanggal" x-model="form.tanggal" required
                                    class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700"
                                    :class="{'ring-2 ring-red-500': isSubmitted && !form.tanggal}">
                                <template x-if="isSubmitted && !form.tanggal">
                                    <p class="text-[0.65rem] text-red-500 mt-1.5 ml-1 font-medium"><i
                                            class="fas fa-exclamation-circle mr-1"></i>Tanggal wajib diisi.</p>
                                </template>
                            </div>

                            <!-- PIC -->
                            <div class="col-span-2 md:col-span-1 relative">
                                <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">PIC <span
                                        class="text-red-500">*</span></label>
                                <div class="relative flex items-center" @click.away="showSuggestions = false">
                                    <input type="text" x-model="newPic" @input.debounce.300ms="fetchSuggestions"
                                        @focus="fetchSuggestions" @keydown.enter.prevent="addPic"
                                        placeholder="Ketik nama & tekan Enter/+"
                                        class="w-full bg-[#f4f5f7] border-0 rounded-xl pl-4 pr-12 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700">
                                    <button type="button" @click="addPic"
                                        class="absolute right-2 w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-lg md:hover:bg-blue-200 transition-colors shadow-sm"
                                        title="Tambah PIC Baru">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>

                                    <!-- Suggestions Dropdown -->
                                    <ul x-show="showSuggestions && suggestions.length > 0" x-transition.opacity
                                        class="absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-100 shadow-lg rounded-xl max-h-48 overflow-y-auto py-1">
                                        <template x-for="suggestion in suggestions" :key="suggestion">
                                            <li @click="selectSuggestion(suggestion)"
                                                class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm text-gray-700 transition-colors flex items-center gap-2">
                                                <i class="fas fa-search text-gray-400 text-xs"></i>
                                                <span x-text="suggestion"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <!-- Template rendering for added PICs -->
                                <div class="mt-3 flex flex-col gap-2">
                                    <template x-for="(pic, index) in pics" :key="index">
                                        <div
                                            class="flex items-center justify-between bg-white border border-gray-200 shadow-sm px-3 py-2 rounded-xl text-sm text-gray-700">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-user-circle text-gray-400"></i>
                                                <span x-text="pic" class="font-medium"></span>
                                            </div>
                                            <button type="button" @click="removePic(index)"
                                                class="text-gray-400 hover:text-red-500 hover:bg-red-50 w-7 h-7 flex items-center justify-center rounded-lg transition-colors focus:outline-none"
                                                title="Hapus PIC">
                                                <i class="fas fa-trash-alt text-[0.7rem]"></i>
                                            </button>
                                            <input type="hidden" name="pic[]" :value="pic">
                                        </div>
                                    </template>
                                </div>
                                <!-- Hidden input validation message placeholder if array is empty -->
                                <template x-if="isSubmitted && pics.length === 0">
                                    <p class="text-[0.65rem] text-red-500 mt-1.5 ml-1 font-medium"><i
                                            class="fas fa-exclamation-circle mr-1"></i>Minimal 1 PIC harus ditambahkan.
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- Nama Kegiatan -->
                        <div class="relative" @click.away="showKegiatanSuggestions = false">
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Nama Kegiatan <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_kegiatan" x-model="form.nama_kegiatan"
                                @input="form.nama_kegiatan = form.nama_kegiatan.charAt(0).toUpperCase() + form.nama_kegiatan.slice(1); fetchKegiatanSuggestions()" @focus="fetchKegiatanSuggestions"
                                required placeholder="Contoh: Maintenance Server"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700"
                                :class="{'ring-2 ring-red-500': isSubmitted && !form.nama_kegiatan}" autocomplete="off">
                            <template x-if="isSubmitted && !form.nama_kegiatan">
                                <p class="text-[0.65rem] text-red-500 mt-1.5 ml-1 font-medium"><i
                                        class="fas fa-exclamation-circle mr-1"></i>Nama Kegiatan wajib diisi.</p>
                            </template>

                            <!-- Suggestions Dropdown for Kegiatan -->
                            <ul x-show="showKegiatanSuggestions && kegiatanSuggestions.length > 0" x-transition.opacity
                                class="absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-100 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] rounded-xl max-h-56 overflow-y-auto py-2">
                                <li
                                    class="px-4 py-1.5 text-[0.65rem] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 sticky top-0 backdrop-blur-sm -mt-2 mb-1">
                                    Riwayat Kegiatan
                                </li>
                                <template x-for="kegiatan in kegiatanSuggestions" :key="kegiatan.id">
                                    <li @click="selectKegiatanSuggestion(kegiatan)"
                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0 group">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors shrink-0">
                                                <i class="fas fa-history text-xs"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-gray-800 truncate"
                                                    x-text="kegiatan.nama_kegiatan"></p>
                                                <p class="text-[0.65rem] text-gray-500 truncate mt-0.5"
                                                    x-text="'Lokasi: ' + kegiatan.lokasi + ' • Oleh: ' + kegiatan.pic">
                                                </p>
                                            </div>
                                            <div
                                                class="shrink-0 text-[0.65rem] font-bold text-blue-500 bg-blue-50 px-2 py-1 rounded hidden group-hover:block">
                                                Auto-fill
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Lokasi -->
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Lokasi <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="lokasi" x-model="form.lokasi" required
                                placeholder="Contoh: Ruang Server Lt. 2"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700"
                                :class="{'ring-2 ring-red-500': isSubmitted && !form.lokasi}">
                            <template x-if="isSubmitted && !form.lokasi">
                                <p class="text-[0.65rem] text-red-500 mt-1.5 ml-1 font-medium"><i
                                        class="fas fa-exclamation-circle mr-1"></i>Lokasi wajib diisi.</p>
                            </template>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Prioritas -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Prioritas <span
                                        class="text-red-500">*</span></label>
                                <select name="prioritas" x-model="form.prioritas" required
                                    class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700">
                                    <option value="Tinggi">Tinggi</option>
                                    <option value="Sedang" selected>Sedang</option>
                                    <option value="Rendah">Rendah</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Status <span
                                        class="text-red-500">*</span></label>
                                <select name="status" x-model="form.status" required
                                    class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700">
                                    <option value="Selesai">Selesai</option>
                                    <option value="Berjalan" selected>Berjalan</option>
                                    <option value="Tertunda">Tertunda</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hasil / Deskripsi -->
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Hasil / Deskripsi
                                Singkat</label>
                            <textarea name="hasil_deskripsi" x-model="form.hasil_deskripsi" rows="2"
                                placeholder="Tuliskan deskripsi ringkas"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700 resize-none"></textarea>
                        </div>

                        <!-- Tindak Lanjut -->
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Tindak
                                Lanjut</label>
                            <textarea name="keterangan_tindak_lanjut" x-model="form.keterangan_tindak_lanjut" rows="2"
                                placeholder="Tindak lanjut jika ada"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-[#f4f5f7] transition-all text-gray-700 resize-none"></textarea>
                        </div>
                    </form>
                </div>

                <div class="px-8 py-5 border-t border-gray-100 flex items-center justify-between bg-white z-10">
                    <button type="button" @click="showModal = false"
                        class="text-xs font-bold text-gray-400 hover:text-gray-800 transition-colors uppercase tracking-wider">
                        BATAL
                    </button>
                    <button type="submit" form="form-laporan"
                        class="inline-flex items-center gap-3 bg-[#0d6efd] hover:bg-blue-700 text-white pl-2 pr-6 py-2 rounded-full font-medium shadow-[0_8px_20px_rgb(13,110,253,0.3)] transition-all hover:-translate-y-0.5 text-sm">
                        <div class="w-7 h-7 bg-white rounded-full flex items-center justify-center text-[#0d6efd]">
                            <i class="fas" :class="editId ? 'fa-save' : 'fa-arrow-right'" class="text-[0.7rem]"></i>
                        </div>
                        <span x-text="editId ? 'Perbarui Data' : 'Simpan Data'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Data (Realtime) -->
        <div x-show="showDeleteModal" style="display: none;"
            class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
                @click="closeDeleteModal()"></div>

            <!-- Modal Content -->
            <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] w-full max-w-sm mx-auto overflow-hidden z-10 relative">

                <div class="p-8 text-center">
                    <div
                        class="w-20 h-20 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-5">
                        <i class="fas fa-exclamation-triangle text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Hapus Data?</h3>
                    <p class="text-sm text-gray-500 mb-8 font-medium leading-relaxed">
                        Tindakan ini tidak dapat dibatalkan. Laporan ini akan dihapus secara permanen dari sistem.
                    </p>

                    <div class="flex gap-3">
                        <button @click="closeDeleteModal()" type="button"
                            class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">
                            Batal
                        </button>
                        <button @click="deleteLaporan()" type="button"
                            class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-200 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-trash-alt"></i> Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Cetak / Export PDF -->
        <div x-show="showExportModal" style="display: none;"
            class="fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div x-show="showExportModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
                @click="showExportModal = false"></div>

            <div x-show="showExportModal" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] w-full max-w-lg mx-auto overflow-hidden z-10 relative">

                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white">
                    <div>
                        <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1">PENGATURAN
                            CETAK</p>
                        <h3 class="text-xl font-bold text-[#111827]">Cetak Laporan Mingguan</h3>
                    </div>
                    <button @click="showExportModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('laporan-mingguan.export') }}" method="GET" target="_blank"
                    @submit="showExportModal = false" class="p-8 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[0.7rem] font-bold text-gray-700 mb-1.5 ml-1">Dari Tanggal <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="start_date" required
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                        <div>
                            <label class="block text-[0.7rem] font-bold text-gray-700 mb-1.5 ml-1">Sampai Tanggal <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="end_date" required
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5 mt-2">
                        <p class="text-xs font-bold text-gray-800 mb-4"><i
                                class="fas fa-signature text-blue-500 mr-2"></i>Penandatangan (Opsional)</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[0.7rem] font-bold text-gray-700 mb-1.5 ml-1">Tanggal Penandatangan</label>
                                <input type="date" name="tanggal_ttd"
                                    class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            </div>
                            <div>
                                <label class="block text-[0.7rem] font-bold text-gray-700 mb-1.5 ml-1">Nama
                                    Penandatangan</label>
                                <input type="text" name="penandatangan" placeholder="Contoh: Budi, S.Kom"
                                    class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-[0.7rem] font-bold text-gray-700 mb-1.5 ml-1">Jabatan</label>
                                    <input type="text" name="jabatan" placeholder="Contoh: Kepala Bidang ..."
                                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-[0.7rem] font-bold text-gray-700 mb-1.5 ml-1">Pangkat /
                                        Golongan / NIP</label>
                                    <input type="text" name="pangkat" placeholder="Contoh: Pembina (IV/a)"
                                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end pt-4">
                        <button type="button" @click="showExportModal = false"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors text-sm">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-colors flex items-center gap-2 text-sm">
                            <i class="fas fa-print"></i> Generate PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Ekstrak Teks AI -->
        <div x-show="showAiModal" style="display: none;"
            class="fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div x-show="showAiModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
                @click="showAiModal = false"></div>

            <div x-show="showAiModal" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] w-full max-w-2xl mx-auto overflow-hidden z-10 relative">

                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white">
                    <div>
                        <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1">PENGISIAN CEPAT</p>
                        <h3 class="text-xl font-bold text-[#111827] flex items-center gap-2">
                            <i class="fas fa-magic text-purple-500"></i> Ekstrak Data dari Teks
                        </h3>
                    </div>
                    <button @click="showAiModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-8">
                    <p class="text-xs text-gray-500 mb-4 bg-purple-50 p-3 rounded-lg border border-purple-100">
                        <i class="fas fa-info-circle text-purple-400 mr-1"></i> Paste laporan mentah dari WhatsApp atau catatan Anda di bawah ini. Sistem akan otomatis mengisi form berdasarkan pola yang terdeteksi. Anda tetap bisa mengubah datanya sebelum disimpan.
                    </p>

                    <textarea x-model="rawText" rows="6" placeholder="Contoh: Kegiatan maintenance server bulan Mei dilaksanakan pada 12-05-2026 di ruang server lantai 2 oleh Budi dan Andi. Status selesai dengan prioritas tinggi. Hasil: membersihkan debu dan cek suhu."
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 transition-all text-gray-700 resize-none"></textarea>
                </div>

                <div class="px-8 py-5 border-t border-gray-100 flex justify-end bg-white">
                    <div class="flex gap-3">
                        <button type="button" @click="showAiModal = false"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors text-sm">
                            Batal
                        </button>
                        <button type="button" @click="parseRawText()" :disabled="isAnalyzing || !rawText.trim()"
                            class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-colors flex items-center gap-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isAnalyzing"><i class="fas fa-sync-alt"></i> Ekstrak Sekarang</span>
                            <span x-show="isAnalyzing"><i class="fas fa-spinner fa-spin"></i> Mendeteksi Pola...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>