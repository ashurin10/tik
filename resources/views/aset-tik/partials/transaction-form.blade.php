<form action="{{ route('aset-tik.transaksi.store', $pageKey) }}" method="POST" class="p-6 grid md:grid-cols-2 xl:grid-cols-4 gap-4">
    @csrf
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nomor Transaksi</label>
        <input type="text" name="nomor" value="{{ old('nomor') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tanggal</label>
        <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Aset</label>
        <select name="aset_tik_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih aset</option>
            @foreach($asets as $aset)
                <option value="{{ $aset->id }}" @selected((string) old('aset_tik_id') === (string) $aset->id)>{{ $aset->kode }} - {{ $aset->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis</label>
        <select name="jenis" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih jenis</option>
            @foreach($transactionConfig['jenis'] as $jenis)
                <option value="{{ $jenis }}" @selected(old('jenis') === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Vendor</label>
        <select name="vendor_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih vendor</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}" @selected((string) old('vendor_id') === (string) $vendor->id)>{{ $vendor->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Lokasi Asal</label>
        <select name="lokasi_asal_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih lokasi</option>
            @foreach($lokasis as $lokasi)
                <option value="{{ $lokasi->id }}" @selected((string) old('lokasi_asal_id') === (string) $lokasi->id)>{{ $lokasi->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Lokasi Tujuan</label>
        <select name="lokasi_tujuan_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih lokasi</option>
            @foreach($lokasis as $lokasi)
                <option value="{{ $lokasi->id }}" @selected((string) old('lokasi_tujuan_id') === (string) $lokasi->id)>{{ $lokasi->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">PJ Baru</label>
        <select name="penanggung_jawab_baru_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Pilih penanggung jawab</option>
            @foreach($penanggungJawabs as $person)
                <option value="{{ $person->id }}" @selected((string) old('penanggung_jawab_baru_id') === (string) $person->id)>{{ $person->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Biaya</label>
        <input type="number" name="biaya" value="{{ old('biaya') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kondisi Akhir</label>
        <select name="kondisi_akhir" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Tidak berubah</option>
            @foreach(['Baik', 'Rusak ringan', 'Rusak berat'] as $option)
                <option value="{{ $option }}" @selected(old('kondisi_akhir') === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Dokumen Pendukung</label>
        <input type="text" name="dokumen" value="{{ old('dokumen') }}" placeholder="Nomor surat / tautan dokumen" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div class="md:col-span-2 xl:col-span-4">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Keterangan</label>
        <textarea name="keterangan" rows="2" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan') }}</textarea>
    </div>
    <div class="md:col-span-2 xl:col-span-4 flex justify-end">
        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
            <i class="fas fa-save"></i>
            Simpan Transaksi
        </button>
    </div>
</form>
