@php
    $isEdit = $isEdit ?? false;
@endphp

<form method="POST"
    action="{{ $action }}"
    @if($isEdit) x-bind:action="'{{ $action }}/' + editing.id" @endif
    enctype="multipart/form-data"
    class="p-6 space-y-5">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5">Nama Sistem</label>
            <input type="text" name="title" required
                @if($isEdit) x-bind:value="editing.title" @endif
                class="w-full bg-gray-50 border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="Contoh: Laporan Mingguan">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5">Kategori</label>
            <input type="text" name="category"
                @if($isEdit) x-bind:value="editing.category" @endif
                class="w-full bg-gray-50 border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="Contoh: Administrasi">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[1fr_120px] gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5">Route / URL Tujuan</label>
            <input list="{{ $isEdit ? 'portal-route-options-edit' : 'portal-route-options-create' }}" type="text" name="url" required
                @if($isEdit) x-bind:value="editing.url" @endif
                class="w-full bg-gray-50 border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="laporan-mingguan.index atau https://...">
            <datalist id="{{ $isEdit ? 'portal-route-options-edit' : 'portal-route-options-create' }}">
                @foreach($availableRoutes as $route => $label)
                    <option value="{{ $route }}">{{ $label }}</option>
                @endforeach
            </datalist>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5">Urutan</label>
            <input type="number" min="0" max="9999" name="order"
                @if($isEdit) x-bind:value="editing.order" @endif
                class="w-full bg-gray-50 border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="0">
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-gray-500 mb-1.5">Deskripsi</label>
        <textarea name="description" rows="3"
            @if($isEdit) x-text="editing.description" @endif
            class="w-full bg-gray-50 border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 resize-none"
            placeholder="Deskripsi singkat sistem"></textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5">Icon FontAwesome</label>
            <input type="text" name="icon_class"
                @if($isEdit) x-bind:value="editing.icon_class" @endif
                class="w-full bg-gray-50 border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="fas fa-file-alt">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5">Gambar</label>
            <input type="file" name="image" accept="image/*"
                class="w-full bg-gray-50 border-gray-200 rounded-lg px-3 py-2 text-sm file:mr-3 file:px-3 file:py-1 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-600 file:font-bold">
            @if($isEdit)
                <label class="mt-2 inline-flex items-center gap-2 text-xs font-medium text-gray-500">
                    <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-blue-600">
                    Hapus gambar saat ini
                </label>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button"
            @if($isEdit) @click="editing = null" @else @click="showCreate = false" @endif
            class="px-4 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-800 transition">
            Batal
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition">
            Simpan
        </button>
    </div>
</form>
