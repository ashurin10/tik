<x-app-layout title="Dashboard Aset">
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Aset TIK</h1>
                <p class="text-gray-500">Ringkasan status inventaris dan kondisi aset.</p>
            </div>

            <div class="flex items-center gap-3">
                <button
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Scan QR Keluar
                </button>
                <button
                    class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-teal-200 transition-all flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Scan QR Masuk
                </button>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Aset -->
            <div
                class="bg-white p-6 rounded-3xl shadow-lg shadow-blue-100/50 flex flex-col justify-between h-32 relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-gray-500 text-sm font-bold">Total Aset</span>
                    <div class="text-3xl font-bold text-gray-800 mt-1">{{ $totalAset }}</div>
                </div>
                <div class="absolute right-[-10px] bottom-[-10px] text-blue-50 text-8xl">
                    <i class="fas fa-cube"></i>
                </div>
            </div>

            <!-- Aset Keluar Hari Ini -->
            <div
                class="bg-white p-6 rounded-3xl shadow-lg shadow-purple-100/50 flex flex-col justify-between h-32 relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-gray-500 text-sm font-bold">Aset Keluar Hari Ini</span>
                    <div class="text-3xl font-bold text-purple-600 mt-1">{{ $asetKeluarHariIni }}</div>
                </div>
                <div class="absolute right-[-10px] bottom-[-10px] text-purple-50 text-8xl">
                    <i class="fas fa-truck-loading"></i>
                </div>
            </div>

            <!-- Jatuh Tempo Pengembalian -->
            <div
                class="bg-white p-6 rounded-3xl shadow-lg shadow-orange-100/50 flex flex-col justify-between h-32 relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-gray-500 text-sm font-bold">Jatuh Tempo Pengembalian</span>
                    <div class="text-3xl font-bold text-orange-600 mt-1">{{ $asetJatuhTempo }}</div>
                </div>
                <div class="absolute right-[-10px] bottom-[-10px] text-orange-50 text-8xl">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <!-- Kondisi Rusak / Dipinjam -->
            <div
                class="bg-white p-6 rounded-3xl shadow-lg shadow-red-100/50 flex flex-col justify-between h-32 relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-gray-500 text-sm font-bold">Kondisi Rusak</span>
                    <div class="text-3xl font-bold text-red-600 mt-1">{{ $asetRusak }}</div>
                </div>
                <div class="absolute right-[-10px] bottom-[-10px] text-red-50 text-8xl">
                    <i class="fas fa-heart-broken"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Assets -->
            <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-lg shadow-gray-200/50">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-gray-800">Aset Terbaru</h3>
                    <a href="{{ route('aset.master.data.index') }}"
                        class="text-sm text-blue-600 font-bold hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-4">
                    @forelse($asetTerbaru as $aset)
                        <div
                            class="flex items-center gap-4 p-4 rounded-2xl hover:bg-gray-50 transition border border-gray-100">
                            <div
                                class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <h4 class="font-bold text-gray-800">{{ $aset->nama_aset }}</h4>
                                    <span
                                        class="text-xs font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded">{{ $aset->kode_aset }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-500 mt-1">
                                    <span>{{ $aset->kategori }} &bull; {{ $aset->merk }}</span>
                                    <span>{{ $aset->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 py-8">Belum ada data aset.</div>
                    @endforelse
                </div>
            </div>

            <!-- Categories -->
            <div class="bg-white p-6 rounded-3xl shadow-lg shadow-gray-200/50">
                <h3 class="font-bold text-gray-800 mb-6">Kategori Aset</h3>
                <div class="space-y-4">
                    @foreach($kategoriDist as $cat)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">{{ $cat->kategori }}</span>
                            <span class="font-bold text-gray-800 bg-gray-100 px-3 py-1 rounded-lg">{{ $cat->total }}</span>
                        </div>
                    @endforeach
                    @if($kategoriDist->isEmpty())
                        <div class="text-center text-gray-400 py-8">Tidak ada data.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>