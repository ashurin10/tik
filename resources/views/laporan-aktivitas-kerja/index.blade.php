<x-app-layout title="Laporan Aktivitas Kerja">
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Aktivitas Kerja (LAK)</h1>
            <p class="text-gray-500 text-sm">Pilih Pegawai dan Bulan untuk melihat atau mengelola LAK.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl border-l-4 border-green-500 font-bold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-xl border-l-4 border-red-500 font-bold flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 max-w-2xl">
            <form action="{{ route('laporan-aktivitas-kerja.show') }}" method="GET" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Pegawai (PIC)</label>
                    <select name="pic" required class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pics as $pic)
                            <option value="{{ $pic }}">{{ $pic }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Bulan & Tahun</label>
                    <input type="month" name="bulan" required value="{{ date('Y-m') }}" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Tampilkan Laporan Aktivitas Kerja
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
