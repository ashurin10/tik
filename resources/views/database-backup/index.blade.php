<x-app-layout title="Backup Database">
    <div class="p-6 max-w-5xl mx-auto">
        @if(session('error'))
            <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-xl border-l-4 border-red-500 font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Backup Database</h1>
                <p class="text-sm text-gray-500">Unduh salinan database aplikasi untuk arsip dan pemulihan data.</p>
            </div>

            <form action="{{ route('database-backup.store') }}" method="POST">
                @csrf
                <button type="submit"
                    @disabled(!$isSupported)
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold shadow-lg transition
                    {{ $isSupported ? 'bg-blue-600 text-white shadow-blue-200 hover:bg-blue-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed shadow-none' }}">
                    <i class="fas fa-download"></i>
                    Download Backup
                </button>
            </form>
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">Driver Database</p>
                <p class="text-lg font-bold text-gray-800">{{ strtoupper($driver) }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:col-span-2">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">Lokasi Database</p>
                <p class="text-sm font-mono text-gray-700 break-all">{{ $databasePath ?? '-' }}</p>
            </div>
        </div>

        @unless($isSupported)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-5 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle mt-1"></i>
                    <div>
                        <p class="font-bold">Backup otomatis belum tersedia untuk konfigurasi database ini.</p>
                        <p class="text-sm mt-1">Fitur download langsung saat ini mendukung SQLite berbasis file. Untuk MySQL/PostgreSQL, gunakan backup dari server database.</p>
                    </div>
                </div>
            </div>
        @endunless

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Riwayat Backup</p>
                    <h2 class="text-lg font-bold text-gray-800">File backup tersimpan</h2>
                </div>
                <span class="text-xs font-bold text-gray-500 bg-gray-100 rounded-full px-3 py-1">{{ count($backups) }} file</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="p-4 text-sm font-bold text-gray-600">Nama File</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Ukuran</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($backups as $backup)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-sm font-mono text-gray-800">{{ $backup['name'] }}</td>
                                <td class="p-4 text-sm text-gray-600">{{ $backup['size'] }}</td>
                                <td class="p-4 text-sm text-gray-600">{{ $backup['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500">
                                    <div class="mb-2"><i class="fas fa-database text-3xl text-gray-300"></i></div>
                                    Belum ada file backup.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
