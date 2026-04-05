<x-app-layout title="Manajemen Menu">
    <div x-data="{ showCreateModal: false, showEditModal: false }" class="p-6" x-cloak>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Menu Management</h1>
                <p class="text-gray-500 text-sm">Create and manage dynamic sidebar menus.</p>
            </div>
            <button type="button" @click="showCreateModal = true"
                class="bg-gradient-to-tl from-blue-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transition-all transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Add New Menu
            </button>
        </div>

        @if (session('status'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden">
            <div class="p-6 border-b border-gray-50">
                <h6 class="font-bold text-gray-800">Menu Structure</h6>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase font-bold bg-gray-50/50">
                            <th class="px-6 py-4">Name / Icon</th>
                            <th class="px-6 py-4">URL / Route</th>
                            <th class="px-6 py-4">Access Roles</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($menus as $menu)
                            <!-- Parent Menu -->
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tl from-blue-500 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-200">
                                            <i class="{{ $menu->icon ?? 'fas fa-bars' }} text-sm"></i>
                                        </div>
                                        <span class="font-bold text-gray-800">{{ $menu->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                    {{ $menu->url }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-1 flex-wrap">
                                        @foreach($menu->roles as $role)
                                            <span class="px-2 py-1 text-xs font-bold bg-blue-50 text-blue-600 rounded-md border border-blue-100">
                                                {{ ucfirst($role->role) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                            @click="
                                                $event.preventDefault();
                                                const btn = $el.closest('button');
                                                document.getElementById('editForm').action = '/menus/' + btn.dataset.menuId;
                                                document.getElementById('edit_name').value = btn.dataset.menuName || '';
                                                document.getElementById('edit_url').value = btn.dataset.menuUrl || '';
                                                document.getElementById('edit_icon').value = btn.dataset.menuIcon || '';
                                                document.getElementById('edit_parent_id').value = btn.dataset.menuParentId || '';
                                                document.getElementById('edit_role_admin').checked = btn.dataset.menuRoleAdmin === '1';
                                                document.getElementById('edit_role_user').checked = btn.dataset.menuRoleUser === '1';
                                                showEditModal = true;
                                            "
                                            data-menu-id="{{ $menu->hashid }}"
                                            data-menu-name="{{ $menu->name }}"
                                            data-menu-url="{{ $menu->url }}"
                                            data-menu-icon="{{ $menu->icon }}"
                                            data-menu-parent-id="{{ $menu->parent_id }}"
                                            data-menu-role-admin="{{ $menu->roles->contains('role', 'admin') ? 1 : 0 }}"
                                            data-menu-role-user="{{ $menu->roles->contains('role', 'user') ? 1 : 0 }}"
                                            class="text-gray-500 hover:text-blue-600 transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('menus.destroy', $menu) }}" method="POST"
                                            onsubmit="return confirm('Delete this menu?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-500 hover:text-red-500 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Submenus -->
                            @foreach ($menu->children as $child)
                                <tr class="hover:bg-gray-50/50 transition-colors bg-gray-50/30">
                                    <td class="px-6 py-4 pl-12">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-gray-400 border border-gray-200 shadow-sm">
                                                <i class="{{ $child->icon ?? 'fas fa-circle' }} text-xs"></i>
                                            </div>
                                            <span class="font-medium text-gray-600">{{ $child->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                        {{ $child->url }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-1 flex-wrap">
                                            @foreach($child->roles as $role)
                                                <span class="px-2 py-1 text-xs font-bold bg-gray-100 text-gray-600 rounded-md">
                                                    {{ ucfirst($role->role) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                @click="
                                                    $event.preventDefault();
                                                    const btn = $el.closest('button');
                                                    document.getElementById('editForm').action = '/menus/' + btn.dataset.menuId;
                                                    document.getElementById('edit_name').value = btn.dataset.menuName || '';
                                                    document.getElementById('edit_url').value = btn.dataset.menuUrl || '';
                                                    document.getElementById('edit_icon').value = btn.dataset.menuIcon || '';
                                                    document.getElementById('edit_parent_id').value = btn.dataset.menuParentId || '';
                                                    document.getElementById('edit_role_admin').checked = btn.dataset.menuRoleAdmin === '1';
                                                    document.getElementById('edit_role_user').checked = btn.dataset.menuRoleUser === '1';
                                                    showEditModal = true;
                                                "
                                                data-menu-id="{{ $child->hashid }}"
                                                data-menu-name="{{ $child->name }}"
                                                data-menu-url="{{ $child->url }}"
                                                data-menu-icon="{{ $child->icon }}"
                                                data-menu-parent-id="{{ $child->parent_id }}"
                                                data-menu-role-admin="{{ $child->roles->contains('role', 'admin') ? 1 : 0 }}"
                                                data-menu-role-user="{{ $child->roles->contains('role', 'user') ? 1 : 0 }}"
                                                class="text-gray-400 hover:text-blue-600 transition-colors">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <form action="{{ route('menus.destroy', $child) }}" method="POST"
                                                onsubmit="return confirm('Delete this submenu?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="showCreateModal" style="display: none;" 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" 
            x-transition x-cloak>
            <div @click.away="showCreateModal = false"
                class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100/50 w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white shadow-sm z-10">
                    <div>
                        <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1">PENGISIAN DATA BARU</p>
                        <h3 class="text-xl font-bold text-[#111827]">Create New Menu</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('menus.store') }}" method="POST" class="p-8 space-y-5 overflow-y-auto">
                    @csrf

                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Menu Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Dashboard"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">URL / Route</label>
                            <input type="text" name="url" placeholder="e.g., users.index"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                        <div>
                            <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Icon (FontAwesome)</label>
                            <input type="text" name="icon" placeholder="fas fa-home"
                                class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Parent Menu (Optional)</label>
                        <select name="parent_id"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                            <option value="">None (Top Level)</option>
                            @foreach($allMenus as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Access Roles</label>
                        <div class="flex gap-6 mt-2 bg-[#f4f5f7] rounded-xl px-4 py-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="roles[]" value="admin"
                                    class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 font-medium">Admin</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="roles[]" value="user"
                                    class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 font-medium">User</span>
                            </label>
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
        x-transition x-cloak>
        <div @click.away="showEditModal = false"
            class="bg-white rounded-[1.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100/50 w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white shadow-sm z-10">
                <div>
                    <p class="text-[0.65rem] font-bold text-gray-400 tracking-[0.2em] uppercase mb-1">EDIT DATA</p>
                    <h3 class="text-xl font-bold text-[#111827]">Edit Menu</h3>
                </div>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-red-500 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="p-8 space-y-5 overflow-y-auto">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Menu Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">URL / Route</label>
                        <input type="text" name="url" id="edit_url"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                    </div>
                    <div>
                        <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Icon</label>
                        <input type="text" name="icon" id="edit_icon"
                            class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Parent Menu</label>
                    <select name="parent_id" id="edit_parent_id"
                        class="w-full bg-[#f4f5f7] border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        <option value="">None (Top Level)</option>
                        @foreach($allMenus as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[0.7rem] font-medium text-gray-500 mb-1.5 ml-1">Access Roles</label>
                    <div class="flex gap-6 mt-2 bg-[#f4f5f7] rounded-xl px-4 py-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="roles[]" value="admin" id="edit_role_admin"
                                class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600 font-medium">Admin</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="roles[]" value="user" id="edit_role_user"
                                class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600 font-medium">User</span>
                        </label>
                    </div>
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
