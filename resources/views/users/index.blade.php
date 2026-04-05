<x-app-layout title="Manajemen Pengguna">
    <div x-data="{ showCreateModal: false, showEditModal: false }" class="p-6" x-cloak>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen User</h1>
                <p class="text-gray-500 text-sm">Kelola pengguna dan hak akses aplikasi.</p>
            </div>
            <button type="button" @click="showCreateModal = true"
                class="bg-gradient-to-tl from-blue-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transition-all transform hover:-translate-y-0.5 inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah User
            </button>
        </div>

        @if(session('status'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-bold uppercase tracking-wider">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <ul class="list-disc list-inside text-sm text-red-700 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-50">
                <h6 class="font-bold text-gray-800">Daftar User</h6>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase font-bold bg-gray-50/50">
                            <th class="px-6 py-4">Nama</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Peran (Role)</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tl from-blue-500 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-200">
                                            <span class="font-bold">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <span class="font-bold text-gray-800">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-bold rounded-md border
                                        {{ $user->peran === 'admin'
                                            ? 'bg-blue-50 text-blue-600 border-blue-100'
                                            : 'bg-green-50 text-green-600 border-green-100' }}">
                                        {{ ucfirst($user->peran) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->terkunci_sampai && now()->lessThan($user->terkunci_sampai))
                                        <span class="px-3 py-1 text-xs font-bold rounded-md bg-orange-50 text-orange-600 border border-orange-200">
                                            <i class="fas fa-lock mr-1"></i> Terkunci
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-bold rounded-md border
                                            {{ $user->aktif
                                                ? 'bg-green-50 text-green-600 border-green-100'
                                                : 'bg-red-50 text-red-600 border-red-100' }}">
                                            {{ $user->aktif ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <button type="button"
                                            @click="
                                                $event.preventDefault();
                                                const btn = $el.closest('button');
                                                document.getElementById('editForm').action = '/users/' + btn.dataset.userId;
                                                document.getElementById('edit_name').value = btn.dataset.userName || '';
                                                document.getElementById('edit_email').value = btn.dataset.userEmail || '';
                                                document.getElementById('edit_peran').value = btn.dataset.userPeran || 'user';
                                                document.getElementById('edit_aktif').value = btn.dataset.userAktif || '1';
                                                
                                                const lockContainer = document.getElementById('lock_container');
                                                if(btn.dataset.userLocked === '1') {
                                                    lockContainer.style.display = 'block';
                                                    document.getElementById('unlock_account').checked = false;
                                                } else {
                                                    lockContainer.style.display = 'none';
                                                }
                                                showEditModal = true;
                                            "
                                            data-user-id="{{ $user->hashid }}"
                                            data-user-name="{{ $user->name }}"
                                            data-user-email="{{ $user->email }}"
                                            data-user-peran="{{ $user->peran }}"
                                            data-user-aktif="{{ $user->aktif ? 1 : 0 }}"
                                            data-user-locked="{{ $user->terkunci_sampai && now()->lessThan($user->terkunci_sampai) ? 1 : 0 }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 rounded-lg text-xs font-bold transition-colors">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="p-4 border-t border-gray-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    <!-- Create Modal -->
    <div x-show="showCreateModal" style="display: none;" 
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" 
        x-transition x-cloak
        @click.self="showCreateModal = false">
        <div
            class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100/50 w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white shadow-sm z-10">
                <div>
                    <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1">PENGISIAN DATA BARU</p>
                    <h3 class="text-xl font-bold text-[#111827]">Tambah User Baru</h3>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-red-500 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="p-8 space-y-5 overflow-y-auto">
                @csrf

                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Alfian"
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                </div>

                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required placeholder="email@example.com"
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required placeholder="********"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                    </div>
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required placeholder="********"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Peran (Role) <span class="text-red-500">*</span></label>
                        <select name="peran"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Status <span class="text-red-500">*</span></label>
                        <select name="aktif"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <button type="button" @click="showCreateModal = false"
                        class="text-gray-500 font-bold text-sm uppercase tracking-wider hover:text-gray-700 transition">
                        BATAL
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-colors flex items-center gap-2 text-sm">
                        <i class="fas fa-arrow-right"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" style="display: none;" 
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" 
        x-transition x-cloak
        @click.self="showEditModal = false">
        <div
            class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100/50 w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white shadow-sm z-10">
                <div>
                    <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1">EDIT DATA</p>
                    <h3 class="text-xl font-bold text-[#111827]">Edit User</h3>
                </div>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-red-500 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="p-8 space-y-5 overflow-y-auto">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                </div>

                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="edit_email" required
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Peran (Role) <span class="text-red-500">*</span></label>
                        <select name="peran" id="edit_peran"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Status <span class="text-red-500">*</span></label>
                        <select name="aktif" id="edit_aktif"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div id="lock_container" style="display: none;" class="p-4 bg-orange-50 border border-orange-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <div class="flex h-6 items-center">
                            <input id="unlock_account" name="unlock_account" value="1" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600">
                        </div>
                        <div class="text-sm leading-6">
                            <label for="unlock_account" class="font-bold text-orange-900">Buka Kunci Akun</label>
                            <p class="text-orange-700 text-xs mt-1">Akun ini sedang terkunci karena terlalu banyak percobaan login yang gagal. Centang kotak ini untuk membuka kunci sekarang.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100/50">
                    <p class="text-[0.65rem] font-bold text-blue-400 tracking-[0.1em] uppercase mb-3">Ganti Password (Opsional)</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1 font-bold">Password Baru</label>
                            <input type="password" name="password" placeholder="********"
                                class="w-full bg-white border-blue-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1 font-bold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" placeholder="********"
                                class="w-full bg-white border-blue-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>
                    <p class="mt-2 text-[0.65rem] text-gray-400 italic font-medium">* Kosongkan password jika tidak ingin mengganti.</p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <button type="button" @click="showEditModal = false"
                        class="text-gray-500 font-bold text-sm uppercase tracking-wider hover:text-gray-700 transition">
                        BATAL
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-colors flex items-center gap-2 text-sm">
                        <i class="fas fa-arrow-right"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

</x-app-layout>
