<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|grand-hotel&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-800">
    <div class="min-h-screen flex" x-data="{ mobileMenuOpen: false }">

        <!-- Sidebar -->
        <aside class="w-72 bg-transparent hidden md:flex flex-col p-4 shrink-0 h-screen sticky top-0">
            <div class="h-full bg-white rounded-3xl shadow-xl flex flex-col overflow-hidden">
                <!-- Brand -->
                <div class="h-20 flex items-center px-8 border-b border-gray-100 shrink-0">
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white mr-3 shadow-lg">
                        <i class="fas fa-cube text-xs"></i>
                    </div>
                    <span class="text-gray-800 font-bold tracking-wide uppercase text-sm">TIK</span>
                </div>

                <!-- Nav -->
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar">
                    @if(isset($sidebarMenus))
                        @foreach($sidebarMenus as $menu)
                                    @php
                                        $url = $menu->url && Route::has($menu->url) ? route($menu->url) : ($menu->url ? url($menu->url) : '#');
                                        $isActive = false;
                                        if ($menu->url && Route::has($menu->url)) {
                                            $isActive = request()->routeIs($menu->url . '*');
                                        }

                                        // Check for active children to auto-expand
                                        $hasActiveChild = false;
                                        if ($menu->children->isNotEmpty()) {
                                            foreach ($menu->children as $child) {
                                                if ($child->url && Route::has($child->url) && request()->routeIs($child->url . '*')) {
                                                    $hasActiveChild = true;
                                                    break;
                                                }
                                            }
                                        }

                                        // If it has children, URL should trigger collapse, unless it's a direct link (which is rare for parents)
                                        // We'll treat parents with children as toggles.
                                        $isToggle = $menu->children->isNotEmpty();
                                        $isParentPlaceholder = !$isToggle && $menu->url && str_starts_with($menu->url, '#');
                                        $href = $isToggle ? '#' : ($isParentPlaceholder ? '#' : $url);
                                    @endphp

                                    <div x-data="{ open: {{ $isActive || $hasActiveChild ? 'true' : 'false' }} }">
                                        <a href="{{ $href }}" @if($isToggle) @click.prevent="open = !open" @endif @if($isParentPlaceholder) onclick="alert('Menu ini sedang dalam tahap pengembangan.'); return false;" @endif class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all group cursor-pointer
                                                                                                                  {{ $isActive || $hasActiveChild
                            ? 'bg-white shadow-md shadow-gray-200 transform -translate-y-0.5'
                            : 'hover:bg-gray-50' 
                                                                                                                  }}">
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shadow-sm transition-all
                                                                                                                        {{ $isActive || $hasActiveChild
                            ? 'bg-gradient-to-tl from-blue-500 to-indigo-500 text-white shadow-md'
                            : 'bg-white text-gray-800 border border-gray-100 group-hover:shadow-md' 
                                                                                                                        }}">
                                                <i class="{{ $menu->icon ?? 'fas fa-circle' }} text-xs"></i>
                                            </div>
                                            <span
                                                class="flex-1 text-sm {{ $isActive || $hasActiveChild ? 'font-bold text-gray-700' : 'font-medium text-gray-500 group-hover:text-gray-900' }}">
                                                {{ $menu->name }}
                                            </span>

                                            @if(in_array($menu->name, ['Mutasi Aset', 'Pemeliharaan']))
                                                <div class="w-2 h-2 shrink-0 rounded-full bg-red-500 mr-1 shadow-sm shadow-red-200"></div>
                                            @endif

                                            @if($isToggle)
                                                <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                                                    :class="{'transform rotate-180': open}"></i>
                                            @endif
                                        </a>

                                        <!-- Submenus -->
                                        @if($menu->children->isNotEmpty())
                                            <div x-show="open" class="ml-12 border-l border-gray-100 pl-4 space-y-1 mt-1 overflow-hidden">
                                                @foreach($menu->children as $child)
                                                    @php
                                                        $isPlaceholder = $child->url && str_starts_with($child->url, '#');
                                                        if ($child->url && Route::has($child->url)) {
                                                            $childUrl = route($child->url);
                                                        } else {
                                                            $childUrl = $isPlaceholder ? '#' : ($child->url ? url($child->url) : '#');
                                                        }
                                                        $isChildActive = $child->url && Route::has($child->url) && request()->routeIs($child->url . '*');
                                                        $showBadge = in_array($child->name, ['Request Approval', 'Jadwal Maintenance']);
                                                    @endphp
                                                    <a href="{{ $childUrl }}"
                                                        @if($isPlaceholder) onclick="alert('Menu ini sedang dalam tahap pengembangan.'); return false;" @endif
                                                        class="block px-2 py-2 text-sm rounded-lg transition-colors {{ $isChildActive ? 'text-blue-600 font-bold bg-blue-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                                                        <div class="flex items-center justify-between">
                                                            <span>{{ $child->name }}</span>
                                                            @if($showBadge)
                                                                <span
                                                                    class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm shadow-red-200">2</span>
                                                            @endif
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                        @endforeach
                    @else
                        <p class="px-4 text-xs text-red-400">Menus not loaded.</p>
                    @endif
                </nav>

                <!-- Profile -->
                <div class="p-4 bg-gray-50 mt-auto shrink-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-blue-600 font-bold border border-gray-100">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <h6 class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</h6>
                            <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->peran ?? 'User' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-gray-400 hover:text-red-500 transition-colors"><i
                                    class="fas fa-power-off"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="mobileMenuOpen" 
             style="display: none;"
             class="fixed inset-0 z-[60] md:hidden" 
             x-cloak>
            <!-- Backdrop -->
            <div @click="mobileMenuOpen = false" 
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
            </div>

            <!-- Sidebar Content -->
            <aside class="fixed inset-y-0 left-0 w-72 bg-white shadow-2xl flex flex-col transform transition-transform"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="-translate-x-full"
                   x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="translate-x-0"
                   x-transition:leave-end="-translate-x-full">
                
                <!-- Brand Mobile -->
                <div class="h-20 flex items-center px-8 border-b border-gray-100 shrink-0 justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white mr-3 shadow-lg">
                            <i class="fas fa-cube text-xs"></i>
                        </div>
                        <span class="text-gray-800 font-bold tracking-wide uppercase text-sm">TIK</span>
                    </div>
                    <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Nav Mobile -->
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar">
                    @if(isset($sidebarMenus))
                        @foreach($sidebarMenus as $menu)
                            @php
                                $url = $menu->url && Route::has($menu->url) ? route($menu->url) : ($menu->url ? url($menu->url) : '#');
                                $isActive = false;
                                if ($menu->url && Route::has($menu->url)) {
                                    $isActive = request()->routeIs($menu->url . '*');
                                }
                                $hasActiveChild = false;
                                if ($menu->children->isNotEmpty()) {
                                    foreach ($menu->children as $child) {
                                        if ($child->url && Route::has($child->url) && request()->routeIs($child->url . '*')) {
                                            $hasActiveChild = true;
                                            break;
                                        }
                                    }
                                }
                                $isToggle = $menu->children->isNotEmpty();
                                $isParentPlaceholder = !$isToggle && $menu->url && str_starts_with($menu->url, '#');
                                $href = $isToggle ? '#' : ($isParentPlaceholder ? '#' : $url);
                            @endphp

                            <div x-data="{ open: {{ $isActive || $hasActiveChild ? 'true' : 'false' }} }">
                                <a href="{{ $href }}" @if($isToggle) @click.prevent="open = !open" @endif class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all group cursor-pointer
                                    {{ $isActive || $hasActiveChild ? 'bg-blue-50 text-blue-600 shadow-sm' : 'hover:bg-gray-50' }}">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shadow-sm transition-all
                                        {{ $isActive || $hasActiveChild ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-800 border border-gray-100' }}">
                                        <i class="{{ $menu->icon ?? 'fas fa-circle' }} text-xs"></i>
                                    </div>
                                    <span class="flex-1 text-sm {{ $isActive || $hasActiveChild ? 'font-bold' : 'font-medium text-gray-500' }}">
                                        {{ $menu->name }}
                                    </span>
                                    @if($isToggle)
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': open}"></i>
                                    @endif
                                </a>

                                @if($menu->children->isNotEmpty())
                                    <div x-show="open" class="ml-12 border-l border-gray-100 pl-4 space-y-1 mt-1">
                                        @foreach($menu->children as $child)
                                            @php
                                                $childUrl = $child->url && Route::has($child->url) ? route($child->url) : ($child->url ? url($child->url) : '#');
                                                $isChildActive = $child->url && Route::has($child->url) && request()->routeIs($child->url . '*');
                                            @endphp
                                            <a href="{{ $childUrl }}" class="block px-2 py-2 text-sm rounded-lg transition-colors {{ $isChildActive ? 'text-blue-600 font-bold bg-blue-50' : 'text-gray-500 hover:text-gray-900' }}">
                                                {{ $child->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </nav>

                <!-- Profile Mobile -->
                <div class="p-4 bg-gray-50 mt-auto shrink-0 border-t border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-blue-600 font-bold border border-gray-100">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <h6 class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</h6>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-power-off"></i></button>
                        </form>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Main Content Wrapper -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">
            <!-- Global Header Top (Search Bar) -->
            <header
                class="h-20 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-end px-6 sticky top-0 z-50 shrink-0">
                <!-- Right Actions -->
                <div class="flex items-center gap-4 relative">
                    <button
                        class="w-10 h-10 rounded-full bg-gray-50 hover:bg-red-50 flex items-center justify-center text-gray-600 hover:text-red-600 relative transition-all border border-gray-100">
                        <i class="fas fa-bell"></i>
                        <span
                            class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenuOpen = true"
                        class="md:hidden w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors border border-gray-100">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-6 relative">
                {{ $slot }}
            </div>
        </main>
    </div>
    @stack('scripts')
</body>

</html>