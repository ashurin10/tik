<x-portal-layout title="Portal Sistem">
    @php
        $resolvePortalUrl = function ($service) {
            if ($service->url && Route::has($service->url)) {
                return route($service->url);
            }

            if ($service->url && str_starts_with($service->url, 'http')) {
                return $service->url;
            }

            return $service->url ? url($service->url) : '#';
        };
    @endphp

    <div x-data="{ showCreate: false, editing: null }" class="w-full min-h-screen bg-gray-50">
        <header class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl font-bold text-gray-900 truncate">Portal Sistem TIK</h1>
                        <p class="text-sm text-gray-500 truncate">Pilih sistem yang ingin dibuka</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if($canManagePortal)
                        <button type="button" @click="showCreate = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition">
                            <i class="fas fa-plus"></i>
                            Tambah Sistem
                        </button>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-10 h-10 rounded-lg border border-gray-200 text-gray-500 hover:text-red-600 hover:bg-red-50 transition">
                            <i class="fas fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('status'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($services->isNotEmpty() || $canManagePortal)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($services as $service)
                            <article class="relative group bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition overflow-hidden">
                                @if($canManagePortal)
                                    <div class="absolute right-3 top-3 z-10 flex items-center gap-1 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition">
                                        <button type="button"
                                            @click="editing = {
                                                id: '{{ $service->id }}',
                                                title: @js($service->title),
                                                category: @js($service->category),
                                                url: @js($service->url),
                                                icon_class: @js($service->icon_class),
                                                description: @js($service->description),
                                                order: '{{ $service->order }}',
                                                has_image: {{ $service->image ? 'true' : 'false' }}
                                            }"
                                            class="w-8 h-8 rounded-md bg-white border border-gray-200 text-gray-500 hover:text-blue-600 shadow-sm transition">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Hapus aplikasi ini dari portal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-md bg-white border border-gray-200 text-gray-500 hover:text-red-600 shadow-sm transition">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                <a href="{{ $resolvePortalUrl($service) }}"
                                    class="min-h-[9.5rem] px-6 py-7 flex flex-col items-center justify-center text-center"
                                    @if($service->url && str_starts_with($service->url, 'http')) target="_blank" rel="noopener noreferrer" @endif>
                                    <div class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 mb-5 overflow-hidden">
                                        @if($service->image)
                                            <img src="{{ Storage::url($service->image) }}" alt="{{ $service->title }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="{{ $service->icon_class ?: 'fas fa-window-maximize' }} text-xl"></i>
                                        @endif
                                    </div>

                                    <h2 class="text-base font-semibold text-gray-900 leading-tight">{{ $service->title }}</h2>
                                    <p class="mt-2 text-sm text-gray-500">Buka aplikasi</p>
                                </a>
                            </article>
                        @endforeach

                        @if($canManagePortal)
                            <button type="button" @click="showCreate = true"
                                class="min-h-[9.5rem] px-6 py-7 rounded-lg border border-dashed border-gray-300 bg-white text-center hover:border-blue-300 hover:bg-blue-50/30 transition">
                                <span class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mx-auto mb-5">
                                    <i class="fas fa-plus text-xl"></i>
                                </span>
                                <span class="block text-base font-semibold text-gray-900">Lainnya</span>
                                <span class="block mt-2 text-sm text-gray-500">Tambah aplikasi</span>
                            </button>
                        @endif
                    </div>
                </section>
            @else
                <div class="bg-white border border-gray-100 rounded-lg p-10 text-center shadow-sm">
                    <div class="w-14 h-14 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-th-large text-xl"></i>
                    </div>
                    <h2 class="font-bold text-gray-900">Portal belum memiliki aplikasi</h2>
                </div>
            @endif
        </main>

        @if($canManagePortal)
            <div x-show="showCreate" style="display:none" class="fixed inset-0 z-50 bg-gray-900/60 flex items-center justify-center p-4">
                <div @click.away="showCreate = false" class="w-full max-w-2xl bg-white rounded-lg shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-bold text-gray-900">Tambah Sistem Portal</h2>
                        <button type="button" @click="showCreate = false" class="text-gray-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                    </div>
                    @include('services.partials.form', ['action' => route('services.store'), 'method' => 'POST', 'availableRoutes' => $availableRoutes])
                </div>
            </div>

            <div x-show="editing" style="display:none" class="fixed inset-0 z-50 bg-gray-900/60 flex items-center justify-center p-4">
                <div @click.away="editing = null" class="w-full max-w-2xl bg-white rounded-lg shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-bold text-gray-900">Edit Sistem Portal</h2>
                        <button type="button" @click="editing = null" class="text-gray-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                    </div>
                    @include('services.partials.form', ['action' => url('/portal/services'), 'method' => 'PUT', 'availableRoutes' => $availableRoutes, 'isEdit' => true])
                </div>
            </div>
        @endif
    </div>
</x-portal-layout>
