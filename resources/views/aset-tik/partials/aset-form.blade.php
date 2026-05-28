<form action="{{ $action }}" method="POST" class="p-6 grid md:grid-cols-2 xl:grid-cols-4 gap-4">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kode Aset</label>
        <input type="text" name="kode" value="{{ old('kode', $aset->kode ?? '') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kode Tracking</label>
        <input type="text" name="tracking_code" value="{{ old('tracking_code', $aset->tracking_code ?? '') }}" placeholder="Otomatis jika kosong" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Aset</label>
        <input type="text" name="nama" value="{{ old('nama', $aset->nama ?? '') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kategori</label>
        <select name="kategori_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih kategori</option>
            @foreach($kategoris as $kategori)
                <option value="{{ $kategori->id }}" @selected((string) old('kategori_id', $aset->kategori_id ?? '') === (string) $kategori->id)>{{ $kategori->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tahun</label>
        <input type="number" name="tahun_perolehan" value="{{ old('tahun_perolehan', $aset->tahun_perolehan ?? '') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Merk</label>
        <input type="text" name="merk" value="{{ old('merk', $aset->merk ?? '') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tipe/Model</label>
        <input type="text" name="model" value="{{ old('model', $aset->model ?? '') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Serial Number</label>
        <input type="text" name="serial_number" value="{{ old('serial_number', $aset->serial_number ?? '') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nilai Aset</label>
        <input type="number" name="nilai" value="{{ old('nilai', $aset->nilai ?? '') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kondisi</label>
        <select name="kondisi" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            @foreach(['Baik', 'Rusak ringan', 'Rusak berat'] as $option)
                <option value="{{ $option }}" @selected(old('kondisi', $aset->kondisi ?? 'Baik') === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Status</label>
        <select name="status" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            @foreach(['Aktif', 'Maintenance', 'Rusak', 'Dihapus'] as $option)
                <option value="{{ $option }}" @selected(old('status', $aset->status ?? 'Aktif') === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Lokasi</label>
        <select name="lokasi_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih lokasi</option>
            @foreach($lokasis as $lokasi)
                <option value="{{ $lokasi->id }}" @selected((string) old('lokasi_id', $aset->lokasi_id ?? '') === (string) $lokasi->id)>{{ $lokasi->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Penanggung Jawab</label>
        <select name="penanggung_jawab_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih penanggung jawab</option>
            @foreach($penanggungJawabs as $person)
                <option value="{{ $person->id }}" @selected((string) old('penanggung_jawab_id', $aset->penanggung_jawab_id ?? '') === (string) $person->id)>{{ $person->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Spesifikasi</label>
        <textarea name="spesifikasi" rows="2" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('spesifikasi', $aset->spesifikasi ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Keterangan</label>
        <textarea name="keterangan" rows="2" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan', $aset->keterangan ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2 xl:col-span-4 flex justify-end">
        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
            <i class="fas fa-save"></i>
            Simpan Aset
        </button>
    </div>
</form>
