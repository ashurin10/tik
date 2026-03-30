<x-app-layout>
    <div class="p-6">
        <div class="flex items-center gap-4 mb-6">
            <div
                class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shadow-sm">
                <i class="fas {{ $icon ?? 'fa-tools' }}"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $title ?? 'Halaman dalam Pengembangan' }}</h1>
                <p class="text-gray-500">Modul ini sudah terdaftar sebagai bagian dari sistem, namun masih dalam proses
                    pengembangan antarmukanya.</p>
            </div>
        </div>

        <div
            class="bg-white p-12 rounded-3xl shadow-xl shadow-gray-200/50 flex flex-col items-center justify-center text-center">
            <div class="w-32 h-32 mb-6 text-gray-200">
                <i class="fas fa-hammer text-[100px]"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">Segera Hadir</h2>
            <p class="text-gray-500 max-w-lg mx-auto">
                Fitur <strong>{{ $title ?? 'ini' }}</strong> sedang dalam pengerjaan dan akan segera tersedia. Seluruh
                data dan model telah terhubung dengan Database ISO 27001. Silakan kembali lagi nanti!
            </p>
            <a href="{{ route('aset.dashboard') }}"
                class="mt-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-200 transition-all">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</x-app-layout>