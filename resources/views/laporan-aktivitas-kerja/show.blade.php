<x-app-layout>
    <div x-data="{ 
        showModal: false, 
        showCetakModal: false,
        showSyncModal: false,
        showCopyModal: false,
        showResetModal: false,
        editId: null,
        deleteId: null,
        showDeleteModal: false,
        form: {
            tanggal: '',
            jam_mulai: '',
            jam_selesai: '',
            uraian_kegiatan: '',
            keterangan: 'tj'
        },
        openAddModal() {
            this.editId = null;
            this.form.tanggal = '';
            this.form.jam_mulai = '07:30';
            this.form.jam_selesai = '08:00';
            this.form.uraian_kegiatan = 'Apel Pagi';
            this.form.keterangan = 'tj';
            this.showModal = true;
        },
        openEditModal(lak) {
            this.editId = lak.id;
            this.form.tanggal = lak.tanggal_raw;
            this.form.jam_mulai = lak.jam_mulai_raw;
            this.form.jam_selesai = lak.jam_selesai_raw;
            this.form.uraian_kegiatan = lak.uraian_kegiatan;
            this.form.keterangan = lak.keterangan;
            this.showModal = true;
        },
        openDeleteModal(id) {
            this.deleteId = id;
            this.showDeleteModal = true;
        },
        async deleteLak() {
            if (!this.deleteId) return;
            try {
                const response = await fetch(`/laporan-aktivitas-kerja/${this.deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error(error);
                alert('Gagal menghapus data.');
            }
        }
    }" class="p-6">

        @if(session('success'))
            <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl border-l-4 border-green-500 font-bold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <a href="{{ route('laporan-aktivitas-kerja.index') }}" class="text-blue-500 text-sm hover:underline mb-1 inline-block"><i class="fas fa-arrow-left"></i> Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen LAK - {{ $pic }}</h1>
                <p class="text-gray-500 text-sm">Bulan: {{ \Carbon\Carbon::parse($bulanStr)->locale('id')->isoFormat('MMMM YYYY') }}</p>
            </div>

            <div class="flex items-center gap-3">
                <button @click="showResetModal = true" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-xl font-bold hover:bg-red-600 hover:text-white transition flex items-center gap-2 shadow-sm border border-red-200 focus:ring-2 focus:ring-red-500" title="Hapus seluruh kegiatan di bulan ini">
                    <i class="fas fa-trash-alt"></i> Reset
                </button>
                
                <div class="flex bg-indigo-50 rounded-xl border border-indigo-100 overflow-hidden shadow-sm">
                    <button @click="showSyncModal = true" class="bg-indigo-600 text-white px-4 py-2.5 font-bold hover:bg-indigo-700 transition flex items-center gap-2 text-sm" title="Tarik Semua Laporan Mingguan Pilihan">
                        <i class="fas fa-sync-alt"></i> Sync Penuh
                    </button>
                    <button @click="showCopyModal = true" class="text-indigo-700 px-4 py-2.5 font-bold hover:bg-indigo-100 transition flex items-center gap-2 text-sm border-l border-indigo-200" title="Salin Kegiatan Satu per Satu">
                        <i class="fas fa-list"></i> Salin (Satuan)
                    </button>
                </div>
                
                <button @click="showCetakModal = true"
                    class="bg-white text-gray-700 px-5 py-2.5 rounded-xl font-bold border border-gray-200 hover:bg-gray-50 transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-print text-blue-600"></i> Cetak PDF
                </button>
                
                <button @click="openAddModal()"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-plus"></i> Tambah Manual
                </button>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-4 font-bold text-gray-600 text-sm">Tanggal</th>
                        <th class="p-4 font-bold text-gray-600 text-sm">Jam</th>
                        <th class="p-4 font-bold text-gray-600 text-sm">Uraian Kegiatan</th>
                        <th class="p-4 font-bold text-gray-600 text-sm text-center">Menit</th>
                        <th class="p-4 font-bold text-gray-600 text-sm">Ket</th>
                        <th class="p-4 font-bold text-gray-600 text-sm text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php 
                        $currentDate = ''; 
                        $dailyTotal = 0;
                        $totalMonth = 0;
                    @endphp
                    @forelse($laks as $index => $row)
                        @php
                            $start = \Carbon\Carbon::parse($row->tanggal . ' ' . $row->jam_mulai);
                            $end = \Carbon\Carbon::parse($row->tanggal . ' ' . $row->jam_selesai);
                            $minutes = $start->diffInMinutes($end);
                            
                            $isJumat = \Carbon\Carbon::parse($row->tanggal)->dayOfWeekIso == 5;
                            $breakStart = \Carbon\Carbon::parse($row->tanggal . ($isJumat ? ' 11:30:00' : ' 12:00:00'));
                            $breakEnd = \Carbon\Carbon::parse($row->tanggal . ' 13:00:00');
                            
                            $overlapStart = $start->copy()->max($breakStart);
                            $overlapEnd = $end->copy()->min($breakEnd);
                            if ($overlapStart < $overlapEnd) {
                                $minutes -= $overlapStart->diffInMinutes($overlapEnd);
                            }
                            
                            $isNewDate = $currentDate != $row->tanggal;
                            
                            if($isNewDate && $index > 0) {
                                // Print subtotal for previous day
                                echo '<tr class="bg-gray-50/50 font-bold border-t-2 border-gray-200"><td colspan="3" class="p-3 text-right text-xs text-gray-600">Total Aktivitas Kerja Harian (menit)</td><td class="p-3 text-center text-sm text-gray-800">'.$dailyTotal.'</td><td colspan="2"></td></tr>';
                                $dailyTotal = 0;
                            }
                            $currentDate = $row->tanggal;
                            $dailyTotal += $minutes;
                            $totalMonth += $minutes;
                            
                            $lakData = [
                                'id' => $row->id,
                                'tanggal_raw' => $row->tanggal,
                                'jam_mulai_raw' => substr($row->jam_mulai, 0, 5),
                                'jam_selesai_raw' => substr($row->jam_selesai, 0, 5),
                                'uraian_kegiatan' => $row->uraian_kegiatan,
                                'keterangan' => $row->keterangan,
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="p-4 text-sm text-gray-800">{{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</td>
                            <td class="p-4 text-sm font-semibold text-gray-700 whitespace-nowrap">{{ substr($row->jam_mulai, 0, 5) }} - {{ substr($row->jam_selesai, 0, 5) }}</td>
                            <td class="p-4 text-sm text-gray-800">
                                {{ $row->uraian_kegiatan }}
                                @if($row->laporan_mingguan_id)
                                    <span class="inline-flex items-center gap-1 text-[0.6rem] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded-md ml-2" title="Disinkronkan dari Laporan Mingguan">
                                        <i class="fas fa-link text-[0.55rem]"></i> Sync
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[0.6rem] font-bold text-green-700 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded-md ml-2" title="Entri Manual">
                                        <i class="fas fa-hand-paper text-[0.55rem]"></i> Manual
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-sm text-center font-bold text-blue-600">{{ $minutes }}</td>
                            <td class="p-4 text-sm text-gray-600">{{ $row->keterangan }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click='openEditModal(@json($lakData))'
                                        class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="openDeleteModal({{ $row->id }})"
                                        class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">Belum ada data aktivitas kerja bulan ini.</td>
                        </tr>
                    @endforelse
                    
                    @if(count($laks) > 0)
                        <tr class="bg-gray-50/50 font-bold border-t-2 border-gray-200"><td colspan="3" class="p-3 text-right text-xs text-gray-600">Total Aktivitas Kerja Harian (menit)</td><td class="p-3 text-center text-sm text-gray-800">{{ $dailyTotal }}</td><td colspan="2"></td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="showModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden z-10 relative">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-gray-900" x-text="editId ? 'Edit Aktivitas' : 'Tambah Aktivitas Manual'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                </div>
                <form :action="editId ? `/laporan-aktivitas-kerja/${editId}` : '{{ route('laporan-aktivitas-kerja.store') }}'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="pic" value="{{ $pic }}">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" x-model="form.tanggal" required class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai" x-model="form.jam_mulai" required class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai" x-model="form.jam_selesai" required class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Uraian Kegiatan</label>
                        <input type="text" name="uraian_kegiatan" x-model="form.uraian_kegiatan" required class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" x-model="form.keterangan" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl text-sm">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-xl text-sm shadow-md">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div x-show="showDeleteModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-auto overflow-hidden z-10 relative">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4"><i class="fas fa-trash-alt text-2xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Aktivitas?</h3>
                    <p class="text-sm text-gray-500 mb-6">Aktivitas ini akan dihapus dari LAK secara permanen.</p>
                    <div class="flex gap-2">
                        <button @click="showDeleteModal = false" type="button" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-xl">Batal</button>
                        <button @click="deleteLak()" type="button" class="flex-1 px-4 py-2.5 bg-red-500 text-white font-bold rounded-xl">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cetak Modal -->
        <div x-show="showCetakModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="showCetakModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCetakModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden z-10 relative">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-gray-900">Pengaturan Cetak LAK</h3>
                    <button @click="showCetakModal = false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                </div>
                <form action="{{ route('laporan-aktivitas-kerja.cetak') }}" method="GET" target="_blank" class="p-6">
                    <input type="hidden" name="pic" value="{{ $pic }}">
                    <input type="hidden" name="bulan" value="{{ $bulanStr }}">
                    
                    <div class="overflow-y-auto max-h-[60vh] pr-2 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- DATA PEGAWAI -->
                            <div class="space-y-4">
                                <h4 class="font-bold text-gray-800 border-b border-gray-100 pb-2 text-sm uppercase tracking-wider text-blue-600">Pembuat Laporan</h4>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Nama</label>
                                    <input type="text" name="nama_pembuat" required value="{{ $pic }}" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">NIP</label>
                                    <input type="text" name="nip" required value="19991010 202504 1 009" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Pangkat / Gol. Ruang</label>
                                    <input type="text" name="pangkat" required value="Penata Muda III/a" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Jabatan</label>
                                    <input type="text" name="jabatan" required value="Manggala Informatika Ahli Pertama" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Unit Kerja</label>
                                    <input type="text" name="unit" required value="Bidang Teknologi Informasi dan Komunikasi Diskominfo" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Jml Hari Kerja</label>
                                    <input type="number" name="jml_hari" required value="15" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <!-- DATA ATASAN -->
                            <div class="space-y-4">
                                <h4 class="font-bold text-gray-800 border-b border-gray-100 pb-2 text-sm uppercase tracking-wider text-green-600">Mengetahui (Atasan)</h4>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Nama Atasan</label>
                                    <input type="text" name="nama_atasan" required value="NURDIYANA, S.Pd., M.A.P." placeholder="Nama beserta gelar" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">NIP Atasan</label>
                                    <input type="text" name="nip_atasan" required value="19780112 200501 1 012" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Pangkat / Gol. Ruang Atasan</label>
                                    <input type="text" name="pangkat_atasan" value="Pembina Tk. I IV/b" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700">Jabatan Atasan</label>
                                    <input type="text" name="jabatan_atasan" required value="Kepala Bidang Teknologi Informasi dan Komunikasi" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500">
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <div class="pt-2 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showCetakModal = false" class="px-4 py-2 mt-4 bg-gray-100 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" @click="setTimeout(() => showCetakModal = false, 100)" class="px-5 py-2 mt-4 bg-blue-600 text-white font-bold rounded-xl text-sm shadow-md hover:bg-blue-700 transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Cetak PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sync Modal -->
        <div x-show="showSyncModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="showSyncModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showSyncModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto overflow-hidden z-10 relative">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-gray-900">Sync Laporan Mingguan</h3>
                    <button @click="showSyncModal = false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                </div>
                <form action="{{ route('laporan-aktivitas-kerja.sync') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="pic" value="{{ $pic }}">
                    <input type="hidden" name="bulan" value="{{ $bulanStr }}">
                    
                    <div class="bg-indigo-50 text-indigo-700 p-3 rounded-xl border border-indigo-100 text-sm mb-4">
                        Tarik data aktivitas dari daftar <b>Laporan Mingguan</b> ke laporan <b>{{ $pic }}</b>.
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Sumber Data (Nama Pegawai / PIC)</label>
                        <select name="source_pic" required class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 text-gray-700 font-medium">
                            @foreach($pics as $p)
                                <option value="{{ $p }}" {{ $p == $pic ? 'selected' : '' }}>
                                    {{ $p }} {{ $p == $pic ? '(PIC Saat ini)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="pt-4 flex gap-2">
                        <button type="button" @click="showSyncModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-xl text-sm">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-sm shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-download"></i> Tarik Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Copy Activity Modal -->
        <div x-show="showCopyModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="showCopyModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCopyModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-auto overflow-hidden z-10 relative flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-gray-900">Salin Kegiatan (Satu per Satu)</h3>
                    <button @click="showCopyModal = false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                </div>
                
                <div class="p-6 overflow-y-auto" x-data="{ selectedPic: '', search: '', selectedIds: [] }">
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Filter PIC (Sumber)</label>
                            <select x-model="selectedPic" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 text-gray-700">
                                <option value="">-- Semua PIC Laporan Mingguan --</option>
                                @foreach($pics as $p)
                                    @if($p != $pic)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cari Kegiatan</label>
                            <input type="text" x-model="search" placeholder="Ketik kata kunci..." class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 text-gray-700">
                        </div>
                    </div>

                    <form action="{{ route('laporan-aktivitas-kerja.copy-multiple') }}" method="POST">
                        @csrf
                        <input type="hidden" name="pic" value="{{ $pic }}">
                        <input type="hidden" name="bulan" value="{{ $bulanStr }}">
                        
                        <div class="border border-gray-100 rounded-xl overflow-hidden max-h-[50vh] overflow-y-auto relative mb-4">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 text-gray-600 font-bold sticky top-0 shadow-sm z-10">
                                    <tr>
                                        <th class="p-3 border-b border-gray-100 text-center w-10">Pilih</th>
                                        <th class="p-3 border-b border-gray-100">Tgl</th>
                                        <th class="p-3 border-b border-gray-100">PIC</th>
                                        <th class="p-3 border-b border-gray-100">Kegiatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 relative bg-white">
                                    @forelse($otherLaporanMingguan as $other)
                                        <tr x-show="(selectedPic === '' || selectedPic === '{{ $other->pic }}') && ('{{ strtolower(htmlspecialchars($other->nama_kegiatan, ENT_QUOTES)) }}'.includes(search.toLowerCase()))" class="hover:bg-gray-50 transition border-b border-gray-50">
                                            <td class="p-3 text-center">
                                                <input type="checkbox" name="other_ids[]" value="{{ $other->id }}" x-model="selectedIds" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                            </td>
                                            <td class="p-3 whitespace-nowrap text-gray-700">{{ \Carbon\Carbon::parse($other->tanggal)->format('d/m') }}</td>
                                            <td class="p-3 text-gray-800 font-semibold" title="{{ $other->pic }}">{{ Str::limit($other->pic, 15) }}</td>
                                            <td class="p-3 text-gray-600">{{ $other->nama_kegiatan }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-6 text-center text-gray-500 font-medium bg-white">Tidak ada Laporan Mingguan dari PIC lain di bulan ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="px-6 py-4 bg-gray-50 -mx-6 -mb-6 border-t border-gray-100 flex justify-between items-center rounded-b-2xl">
                            <span class="text-xs font-bold text-gray-600 bg-white px-3 py-1.5 border border-gray-200 rounded-lg shadow-sm">
                                <span x-text="selectedIds.length" class="text-indigo-600"></span> kegiatan terpilih
                            </span>
                            <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-5 rounded-xl flex items-center gap-2 shadow-md hover:bg-indigo-700 transition" :disabled="selectedIds.length === 0" :class="selectedIds.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fas fa-copy"></i> Salin Terpilih
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reset Modal -->
        <div x-show="showResetModal" style="display: none;" class="fixed inset-0 z-[300] flex items-center justify-center p-4">
            <div x-show="showResetModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showResetModal = false"></div>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-auto overflow-hidden z-10 relative">
                <form action="{{ route('laporan-aktivitas-kerja.reset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pic" value="{{ $pic }}">
                    <input type="hidden" name="bulan" value="{{ $bulanStr }}">
                    
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4 border-4 border-red-50"><i class="fas fa-exclamation-triangle text-3xl"></i></div>
                        <h3 class="text-xl font-black text-gray-900 mb-2">Reset Bulan Ini?</h3>
                        <p class="text-sm text-gray-600 mb-6 font-medium leading-relaxed">
                            Apakah Anda yakin ingin menghapus <strong class="text-red-500">SELURUH</strong> kegiatan Laporan Aktivitas Kerja untuk <b>{{ $pic }}</b> pada bulan <b>{{ $bulanStr }}</b>?<br><br>
                            <span class="text-red-500 text-xs font-bold">Tindakan ini tidak bisa dibatalkan!</span>
                        </p>
                        <div class="flex gap-3">
                            <button @click="showResetModal = false" type="button" class="flex-1 px-4 py-3 bg-gray-100 text-gray-800 font-bold rounded-xl hover:bg-gray-200 transition">Batal</button>
                            <button type="submit" class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition shadow-md flex items-center justify-center gap-2">
                                <i class="fas fa-trash-restore"></i> Ya, Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
