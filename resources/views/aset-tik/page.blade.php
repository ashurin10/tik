<x-app-layout :title="$page['title']">
    <div class="p-4 md:p-6 max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold mb-3">
                    <i class="fas fa-laptop-code"></i>
                    Manajemen Aset TIK
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $page['title'] }}</h1>
                <p class="text-sm text-gray-500 mt-2 max-w-3xl">{{ $page['description'] }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center justify-center text-blue-600">
                <i class="{{ $page['icon'] }} text-xl"></i>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        @if($pageKey === 'dashboard')
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                @foreach($summaryCards as $card)
                    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase text-gray-400">{{ $card['label'] }}</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $card['value'] }}</p>
                            </div>
                            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl shadow-sm p-6 min-h-72">
                    <h2 class="font-bold text-gray-900 mb-1">Grafik Aset Bulanan</h2>
                    <p class="text-sm text-gray-500 mb-6">Area ini disiapkan untuk grafik aset masuk dan aset keluar bulanan.</p>
                    <div class="h-44 rounded-xl bg-gray-50 border border-dashed border-gray-200 flex items-center justify-center text-gray-400 text-sm">
                        Data aset belum tersedia
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 min-h-72">
                    <h2 class="font-bold text-gray-900 mb-1">Kategori Aset</h2>
                    <p class="text-sm text-gray-500 mb-6">Ringkasan aset per kategori akan tampil di sini.</p>
                    <div class="space-y-3">
                        @foreach(['Server', 'Laptop', 'PC', 'Printer'] as $category)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ $category }}</span>
                                <span class="font-bold text-gray-900">0</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @elseif($masterConfig)
            @php
                $labels = [
                    'kode' => 'Kode',
                    'nama' => 'Nama',
                    'deskripsi' => 'Deskripsi',
                    'gedung' => 'Gedung',
                    'ruangan' => 'Ruangan',
                    'keterangan' => 'Keterangan',
                    'unit_kerja' => 'Unit Kerja',
                    'alamat' => 'Alamat',
                    'kontak' => 'Kontak',
                    'pic' => 'PIC',
                ];
                $type = $pageKey;
            @endphp

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Tambah {{ $page['title'] }}</h2>
                </div>
                <form action="{{ route('aset-tik.master.store', $type) }}" method="POST" class="p-6 grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @csrf
                    @foreach($masterConfig['fields'] as $field)
                        <div class="{{ in_array($field, ['deskripsi', 'keterangan', 'alamat'], true) ? 'md:col-span-2 xl:col-span-3' : '' }}">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">{{ $labels[$field] ?? $field }}</label>
                            @if(in_array($field, ['deskripsi', 'keterangan', 'alamat'], true))
                                <textarea name="{{ $field }}" rows="2" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old($field) }}</textarea>
                            @else
                                <input type="text" name="{{ $field }}" value="{{ old($field) }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @endif
                        </div>
                    @endforeach
                    <div class="md:col-span-2 xl:col-span-3 flex justify-end">
                        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                            <i class="fas fa-save"></i>
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Daftar {{ $page['title'] }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                @foreach($masterConfig['fields'] as $field)
                                    <th class="px-4 py-3">{{ $labels[$field] ?? $field }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($masterItems as $item)
                                <tr>
                                    <form action="{{ route('aset-tik.master.update', [$type, $item]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        @foreach($masterConfig['fields'] as $field)
                                            <td class="px-4 py-3 min-w-44">
                                                @if(in_array($field, ['deskripsi', 'keterangan', 'alamat'], true))
                                                    <textarea name="{{ $field }}" rows="1" class="w-full rounded-lg border-gray-200 text-sm">{{ $item->{$field} }}</textarea>
                                                @else
                                                    <input type="text" name="{{ $field }}" value="{{ $item->{$field} }}" class="w-full rounded-lg border-gray-200 text-sm">
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <button class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" title="Simpan perubahan">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                    </form>
                                                <form action="{{ route('aset-tik.master.destroy', [$type, $item]) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($masterConfig['fields']) + 1 }}" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">{{ $masterItems->links() }}</div>
            </div>
        @elseif($pageKey === 'data-aset')
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Tambah Data Aset TIK</h2>
                </div>
                @include('aset-tik.partials.aset-form', ['action' => route('aset-tik.data-aset.store'), 'method' => 'POST'])
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Daftar Data Aset TIK</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Kode</th>
                                <th class="px-4 py-3">Tracking</th>
                                <th class="px-4 py-3">Nama Aset</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3">Kondisi</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($asets as $aset)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-sm text-gray-700">{{ $aset->kode }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-blue-700 bg-blue-50 rounded-lg px-2 py-1">{{ $aset->tracking_code }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-gray-900">{{ $aset->nama }}</p>
                                        <p class="text-xs text-gray-500">{{ trim(($aset->merk ?? '') . ' ' . ($aset->model ?? '')) ?: '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $aset->kategori?->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $aset->lokasi?->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $aset->kondisi }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">{{ $aset->status }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('aset-tik.data-aset.label', $aset) }}" target="_blank"
                                                class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 inline-flex items-center justify-center"
                                                title="Cetak label">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                            <form action="{{ route('aset-tik.data-aset.destroy', $aset) }}" method="POST" onsubmit="return confirm('Hapus aset ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada aset.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">{{ $asets->links() }}</div>
            </div>
        @elseif($transactionConfig)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Catat {{ $page['title'] }}</h2>
                </div>
                @include('aset-tik.partials.transaction-form')
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Daftar {{ $page['title'] }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Nomor</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Aset</th>
                                <th class="px-4 py-3">Jenis</th>
                                <th class="px-4 py-3">Perubahan</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-sm text-gray-700">{{ $transaction->nomor }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->tanggal?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-gray-900">{{ $transaction->aset?->nama ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $transaction->aset?->kode ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->jenis ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <div>Lokasi: {{ $transaction->lokasiAsal?->nama ?? '-' }} ke {{ $transaction->lokasiTujuan?->nama ?? '-' }}</div>
                                        <div>Kondisi: {{ $transaction->kondisi_akhir ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end">
                                            <form action="{{ route('aset-tik.transaksi.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini? Status aset tidak otomatis dikembalikan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">{{ $transactions->links() }}</div>
            </div>
        @elseif($pageKey === 'riwayat')
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Riwayat Aktivitas Aset</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Aset</th>
                                <th class="px-4 py-3">Aktivitas</th>
                                <th class="px-4 py-3">Nomor Transaksi</th>
                                <th class="px-4 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($riwayats as $riwayat)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $riwayat->tanggal?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-gray-900">{{ $riwayat->aset?->nama ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $riwayat->aset?->kode ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $riwayat->aktivitas }}</td>
                                    <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $riwayat->transaksi?->nomor ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $riwayat->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada riwayat aset.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">{{ $riwayats->links() }}</div>
            </div>
        @elseif($pageKey === 'qr-tracking')
            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="font-bold text-gray-900">Scan / Cari Aset</h2>
                        <p class="text-sm text-gray-500 mt-1">Masukkan kode tracking, kode aset, atau serial number.</p>
                    </div>
                    <form method="GET" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kode Tracking</label>
                            <input type="text" name="q" value="{{ request('q') }}" autofocus class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: TIK-AST-001">
                        </div>
                        <button class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                            <i class="fas fa-search"></i>
                            Cari Aset
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="font-bold text-gray-900">Hasil Tracking</h2>
                    </div>
                    @if(request()->filled('q') && !$trackingResult)
                        <div class="p-8 text-center text-gray-500">
                            <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-search"></i>
                            </div>
                            Data aset tidak ditemukan.
                        </div>
                    @elseif($trackingResult)
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6">
                                <div>
                                    <p class="font-mono text-sm text-blue-700 bg-blue-50 inline-flex rounded-lg px-2 py-1 mb-3">{{ $trackingResult->tracking_code }}</p>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $trackingResult->nama }}</h3>
                                    <p class="text-sm text-gray-500">{{ $trackingResult->kode }} {{ $trackingResult->serial_number ? ' - SN: ' . $trackingResult->serial_number : '' }}</p>
                                </div>
                                <div class="w-32 h-32 rounded-xl border border-gray-200 bg-gray-50 flex flex-col items-center justify-center text-center p-3">
                                    <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                                    <span class="font-mono text-[10px] text-gray-600 break-all">{{ $trackingResult->tracking_code }}</span>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <p class="text-xs font-bold text-gray-400 uppercase">Kategori</p>
                                    <p class="font-semibold text-gray-900 mt-1">{{ $trackingResult->kategori?->nama ?? '-' }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <p class="text-xs font-bold text-gray-400 uppercase">Lokasi</p>
                                    <p class="font-semibold text-gray-900 mt-1">{{ $trackingResult->lokasi?->nama ?? '-' }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <p class="text-xs font-bold text-gray-400 uppercase">Kondisi</p>
                                    <p class="font-semibold text-gray-900 mt-1">{{ $trackingResult->kondisi }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <p class="text-xs font-bold text-gray-400 uppercase">Status</p>
                                    <p class="font-semibold text-gray-900 mt-1">{{ $trackingResult->status }}</p>
                                </div>
                            </div>

                            <h4 class="font-bold text-gray-900 mb-3">Riwayat Terakhir</h4>
                            <div class="space-y-2">
                                @forelse($trackingResult->riwayat->sortByDesc('tanggal')->take(5) as $history)
                                    <div class="rounded-xl border border-gray-100 p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="font-semibold text-gray-800">{{ $history->aktivitas }}</p>
                                            <span class="text-xs text-gray-500">{{ $history->tanggal?->format('d/m/Y') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">{{ $history->keterangan ?? $history->transaksi?->nomor ?? '-' }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">Belum ada riwayat untuk aset ini.</p>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center text-gray-500">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            Masukkan kode tracking untuk melihat detail aset.
                        </div>
                    @endif
                </div>
            </div>
        @elseif($reportType)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mb-6">
                <form method="GET" class="p-6 grid md:grid-cols-2 xl:grid-cols-5 gap-4">
                    @if(in_array($reportType, ['laporan-aset-masuk', 'laporan-aset-keluar'], true))
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bulan</label>
                            <select name="bulan" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua bulan</option>
                                @for($month = 1; $month <= 12; $month++)
                                    <option value="{{ $month }}" @selected((string) request('bulan') === (string) $month)>{{ $month }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tahun</label>
                            <input type="number" name="tahun" value="{{ request('tahun') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Lokasi</label>
                            <select name="lokasi_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua lokasi</option>
                                @foreach($lokasis as $lokasi)
                                    <option value="{{ $lokasi->id }}" @selected((string) request('lokasi_id') === (string) $lokasi->id)>{{ $lokasi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Status</label>
                            <select name="status" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua status</option>
                                @foreach(['Aktif', 'Maintenance', 'Rusak', 'Dihapus'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kategori</label>
                        <select name="kategori_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" @selected((string) request('kategori_id') === (string) $kategori->id)>{{ $kategori->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                            <i class="fas fa-filter"></i>
                            Filter
                        </button>
                        <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-bold hover:bg-gray-200">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900">Data {{ $page['title'] }}</h2>
                    <span class="text-xs font-bold text-gray-500 bg-gray-100 rounded-full px-3 py-1">{{ $reportRows->total() }} data</span>
                </div>
                <div class="overflow-x-auto">
                    @if(in_array($reportType, ['laporan-aset-masuk', 'laporan-aset-keluar'], true))
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Nomor</th>
                                    <th class="px-4 py-3">Aset</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3">Jenis</th>
                                    <th class="px-4 py-3">Vendor/Tujuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reportRows as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->tanggal?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 font-mono text-sm text-gray-700">{{ $row->nomor }}</td>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-900">{{ $row->aset?->nama ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $row->aset?->kode ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->aset?->kategori?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->jenis ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->vendor?->nama ?? $row->lokasiTujuan?->nama ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data laporan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Kode</th>
                                    <th class="px-4 py-3">Aset</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3">Lokasi</th>
                                    <th class="px-4 py-3">Penanggung Jawab</th>
                                    <th class="px-4 py-3">Kondisi</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reportRows as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-sm text-gray-700">{{ $row->kode }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $row->nama }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->kategori?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->lokasi?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->penanggungJawab?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->kondisi }}</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">{{ $row->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data laporan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100">{{ $reportRows->links() }}</div>
            </div>
        @else
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Ruang Kerja {{ $page['title'] }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Kerangka menu sudah aktif. Form, tabel, filter, dan proses data dapat ditambahkan pada halaman ini.</p>
                </div>
                <div class="p-8">
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 min-h-64 flex flex-col items-center justify-center text-center px-4">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center justify-center text-blue-600 mb-4">
                            <i class="{{ $page['icon'] }} text-xl"></i>
                        </div>
                        <p class="font-bold text-gray-900">{{ $page['title'] }}</p>
                        <p class="text-sm text-gray-500 mt-2 max-w-md">{{ $page['description'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
