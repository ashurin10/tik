<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard Laporan</h1>
                <p class="text-gray-500 text-sm">Ringkasan data kegiatan TIK dan Persandian</p>
            </div>
            <a href="{{ route('laporan-mingguan.index') }}"
                class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl shadow-sm hover:shadow-md transition-all text-sm">
                <i class="fas fa-list"></i> Lihat Data Laporan
            </a>
        </div>

        <!-- Stats Cards (Row 1) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Kegiatan -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50 relative overflow-hidden group hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-blue-500 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-200/50">
                            <i class="fas fa-clipboard-list text-white"></i>
                        </div>
                        <span class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-wider">Total</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $totalKegiatan }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">Total Kegiatan</p>
                </div>
            </div>

            <!-- Selesai -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50 relative overflow-hidden group hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-green-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-green-500 to-emerald-500 flex items-center justify-center shadow-lg shadow-green-200/50">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                        <span class="text-[0.65rem] font-bold text-green-500 uppercase tracking-wider">Selesai</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $statusSelesai }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">Kegiatan Selesai</p>
                </div>
            </div>

            <!-- Berjalan -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50 relative overflow-hidden group hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-orange-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-orange-400 to-amber-500 flex items-center justify-center shadow-lg shadow-orange-200/50">
                            <i class="fas fa-spinner text-white"></i>
                        </div>
                        <span class="text-[0.65rem] font-bold text-orange-500 uppercase tracking-wider">Berjalan</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $statusBerjalan }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">Sedang Berjalan</p>
                </div>
            </div>

            <!-- Tertunda -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50 relative overflow-hidden group hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-red-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-red-400 to-rose-500 flex items-center justify-center shadow-lg shadow-red-200/50">
                            <i class="fas fa-pause-circle text-white"></i>
                        </div>
                        <span class="text-[0.65rem] font-bold text-red-500 uppercase tracking-wider">Tertunda</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $statusTertunda }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">Kegiatan Tertunda</p>
                </div>
            </div>
        </div>

        <!-- Row 2: Completion Rate + Priority Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Completion Rate -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-chart-pie text-blue-500 mr-2"></i>Tingkat Penyelesaian</h4>
                <div class="flex items-center justify-center py-4">
                    <div class="relative w-40 h-40">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none" stroke="#f3f4f6" stroke-width="3" />
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none" stroke="url(#gradient)" stroke-width="3"
                                stroke-dasharray="{{ $completionRate }}, 100"
                                stroke-linecap="round" />
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#3b82f6" />
                                    <stop offset="100%" style="stop-color:#6366f1" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-bold text-gray-800">{{ $completionRate }}%</span>
                            <span class="text-[0.65rem] text-gray-400 font-bold">SELESAI</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center gap-6 mt-2 text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                        <span class="text-gray-600 font-medium">Selesai: {{ $statusSelesai }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-gray-200"></div>
                        <span class="text-gray-600 font-medium">Lainnya: {{ $totalKegiatan - $statusSelesai }}</span>
                    </div>
                </div>
            </div>

            <!-- Priority Breakdown -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i>Distribusi Prioritas</h4>
                <div class="space-y-5 mt-6">
                    @php $maxPrioritas = max($prioritasTinggi, $prioritasSedang, $prioritasRendah, 1); @endphp
                    <!-- Tinggi -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-red-600 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Tinggi
                            </span>
                            <span class="text-sm font-bold text-gray-800">{{ $prioritasTinggi }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-red-500 to-rose-400 h-3 rounded-full transition-all duration-700" style="width: {{ ($prioritasTinggi / $maxPrioritas) * 100 }}%"></div>
                        </div>
                    </div>
                    <!-- Sedang -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-orange-600 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span> Sedang
                            </span>
                            <span class="text-sm font-bold text-gray-800">{{ $prioritasSedang }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-500 to-amber-400 h-3 rounded-full transition-all duration-700" style="width: {{ ($prioritasSedang / $maxPrioritas) * 100 }}%"></div>
                        </div>
                    </div>
                    <!-- Rendah -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-green-600 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Rendah
                            </span>
                            <span class="text-sm font-bold text-gray-800">{{ $prioritasRendah }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-400 h-3 rounded-full transition-all duration-700" style="width: {{ ($prioritasRendah / $maxPrioritas) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-tasks text-indigo-500 mr-2"></i>Distribusi Status</h4>
                <div class="space-y-4 mt-6">
                    @php $maxStatus = max($statusSelesai, $statusBerjalan, $statusTertunda, 1); @endphp
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-check text-green-500 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between mb-1.5">
                                <span class="text-sm font-bold text-gray-700">Selesai</span>
                                <span class="text-sm font-bold text-green-600">{{ $statusSelesai }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($statusSelesai / $maxStatus) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-sync text-orange-500 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between mb-1.5">
                                <span class="text-sm font-bold text-gray-700">Berjalan</span>
                                <span class="text-sm font-bold text-orange-600">{{ $statusBerjalan }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ ($statusBerjalan / $maxStatus) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-clock text-red-500 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between mb-1.5">
                                <span class="text-sm font-bold text-gray-700">Tertunda</span>
                                <span class="text-sm font-bold text-red-600">{{ $statusTertunda }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($statusTertunda / $maxStatus) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Monthly Trend Chart + Weekly Trend -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Monthly Trend -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-chart-bar text-blue-500 mr-2"></i>Tren Kegiatan Bulanan</h4>
                <div class="flex items-end gap-3 h-48 mt-4">
                    @php $maxMonthly = max(array_column($monthlyTrend, 'count')) ?: 1; @endphp
                    @foreach($monthlyTrend as $month)
                        <div class="flex flex-col items-center flex-1 gap-2">
                            <span class="text-xs font-bold text-gray-600">{{ $month['count'] }}</span>
                            <div class="w-full bg-gradient-to-t from-blue-500 to-indigo-400 rounded-t-xl shadow-md shadow-blue-100 transition-all hover:from-blue-600 hover:to-indigo-500"
                                 style="height: {{ max(($month['count'] / $maxMonthly) * 100, 4) }}%; min-height: 6px;">
                            </div>
                            <span class="text-[0.6rem] text-gray-400 font-bold text-center leading-tight">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Weekly Trend -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-calendar-week text-purple-500 mr-2"></i>Tren Kegiatan Mingguan</h4>
                <div class="flex items-end gap-4 h-48 mt-4">
                    @php $maxWeekly = max(array_column($weeklyTrend, 'count')) ?: 1; @endphp
                    @foreach($weeklyTrend as $week)
                        <div class="flex flex-col items-center flex-1 gap-2">
                            <span class="text-xs font-bold text-gray-600">{{ $week['count'] }}</span>
                            <div class="w-full bg-gradient-to-t from-purple-500 to-violet-400 rounded-t-xl shadow-md shadow-purple-100 transition-all hover:from-purple-600 hover:to-violet-500"
                                 style="height: {{ max(($week['count'] / $maxWeekly) * 100, 4) }}%; min-height: 6px;">
                            </div>
                            <span class="text-[0.6rem] text-gray-400 font-bold text-center leading-tight">{{ $week['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Row 4: Top PIC + Top Lokasi + Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Top PIC -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-user-shield text-teal-500 mr-2"></i>Top 5 PIC Teraktif</h4>
                <div class="space-y-3">
                    @php $rank = 1; @endphp
                    @forelse($topPics as $picName => $picCount)
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $rank === 1 ? 'bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-100' : 'bg-gray-50/50' }} transition-colors hover:bg-gray-50">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0
                                {{ $rank === 1 ? 'bg-gradient-to-tl from-amber-400 to-yellow-400 text-white shadow-md shadow-amber-200' : ($rank === 2 ? 'bg-gray-300 text-white' : ($rank === 3 ? 'bg-orange-300 text-white' : 'bg-gray-100 text-gray-500')) }}">
                                {{ $rank }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $picName }}</p>
                            </div>
                            <span class="text-sm font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg">{{ $picCount }}</span>
                        </div>
                        @php $rank++; @endphp
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">Belum ada data PIC.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Lokasi -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-map-marker-alt text-rose-500 mr-2"></i>Top 5 Lokasi</h4>
                <div class="space-y-3">
                    @php $locRank = 1; @endphp
                    @forelse($lokasiCounts as $lokasi => $locCount)
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $locRank === 1 ? 'bg-gradient-to-r from-rose-50 to-pink-50 border border-rose-100' : 'bg-gray-50/50' }} transition-colors hover:bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold shrink-0">
                                <i class="fas fa-map-pin"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $lokasi }}</p>
                            </div>
                            <span class="text-sm font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg">{{ $locCount }}</span>
                        </div>
                        @php $locRank++; @endphp
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">Belum ada data lokasi.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-2xl shadow-lg shadow-gray-100/50 p-6 border border-gray-50">
                <h4 class="text-sm font-bold text-gray-800 mb-5"><i class="fas fa-history text-gray-500 mr-2"></i>Kegiatan Terakhir</h4>
                <div class="space-y-3">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50/50 transition-colors hover:bg-gray-50">
                            <div class="w-2 h-2 rounded-full mt-2 shrink-0
                                {{ $activity->status === 'Selesai' ? 'bg-green-500' : ($activity->status === 'Berjalan' ? 'bg-orange-500' : 'bg-red-500') }}">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $activity->nama_kegiatan }}</p>
                                <p class="text-[0.65rem] text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($activity->tanggal)->locale('id')->isoFormat('D MMM Y') }} &bull;
                                    {{ $activity->lokasi }}
                                </p>
                            </div>
                            <span class="text-[0.6rem] font-bold px-2 py-1 rounded-md shrink-0
                                {{ $activity->status === 'Selesai' ? 'text-green-600 bg-green-50' : ($activity->status === 'Berjalan' ? 'text-orange-600 bg-orange-50' : 'text-red-600 bg-red-50') }}">
                                {{ $activity->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">Belum ada kegiatan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
