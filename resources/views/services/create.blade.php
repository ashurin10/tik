<x-app-layout>
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1">PENGISIAN DATA BARU</p>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Layanan Portal</h1>
            </div>
            <a href="{{ route('portal') }}" class="text-gray-500 hover:text-gray-700 font-medium">
                &larr; Kembali
            </a>
        </div>

        <div class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100/50 overflow-hidden">
            <form method="POST" action="{{ route('services.store') }}" enctype="multipart/form-data" class="p-8 space-y-5">
                @csrf

                <!-- Image Upload -->
                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Upload Gambar (Mascot/Character)</label>
                    <input id="image" name="image" type="file" accept="image/*" class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700
                        file:mr-4 file:py-1 file:px-3
                        file:rounded-lg file:border-0
                        file:text-xs file:font-bold
                        file:bg-blue-50 file:text-blue-600
                        hover:file:bg-blue-100" />
                    <x-input-error class="mt-2" :messages="$errors->get('image')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Judul Layanan <span class="text-red-500">*</span></label>
                        <input type="text" id="title" name="title" required autofocus placeholder="Contoh: Sistem Akademik"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Kategori <span class="text-red-500">*</span></label>
                        <select id="category" name="category"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            <option value="" disabled selected>Pilih Kategori</option>
                            <option value="APLIKASI DAN WEB">APLIKASI DAN WEB</option>
                            <option value="JARINGAN DAN SERVER">JARINGAN DAN SERVER</option>
                            <option value="KEAMANAN INFORMASI">KEAMANAN INFORMASI</option>
                            <option value="STATISTIK SEKTORAL">STATISTIK SEKTORAL</option>
                            <option value="KOMUNIKASI PUBLIK">KOMUNIKASI PUBLIK</option>
                            <option value="PERSANDIAN">PERSANDIAN</option>
                            <option value="PERIZINAN TIK">PERIZINAN TIK</option>
                            <option value="LAINNYA">LAINNYA</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('category')" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- URL -->
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">URL / Link <span class="text-red-500">*</span></label>
                        <input type="text" id="url" name="url" required placeholder="https://..."
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        <x-input-error class="mt-2" :messages="$errors->get('url')" />
                    </div>

                    <!-- Icon Class -->
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Icon Class (FontAwesome)</label>
                        <input type="text" id="icon_class" name="icon_class" placeholder="fas fa-globe"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        <x-input-error class="mt-2" :messages="$errors->get('icon_class')" />
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Deskripsi Singkat</label>
                    <textarea id="description" name="description" rows="3" placeholder="Tuliskan deskripsi singkat layanan"
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700 resize-none"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <a href="{{ route('portal') }}"
                        class="text-gray-500 font-bold text-sm uppercase tracking-wider hover:text-gray-700 transition">
                        BATAL
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-colors flex items-center gap-2 text-sm">
                        <i class="fas fa-arrow-right"></i> Simpan Layanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>