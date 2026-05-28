<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        // Share Menus with App Layout
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $menus = collect();

            if ($user) {
                $role = $user->peran;

                $activeMenuPrefix = $this->activeMenuPrefix(request()->route()?->getName());

                $menus = \App\Models\Menu::whereNull('parent_id')
                    ->whereHas('roles', function ($q) use ($role) {
                        $q->where('role', $role);
                    })
                    ->with([
                        'children' => function ($q) use ($role) {
                            $q->whereHas('roles', function ($q2) use ($role) {
                                $q2->where('role', $role);
                            })->orderBy('order');
                        }
                    ])
                    ->orderBy('order')
                    ->get();

                if ($activeMenuPrefix) {
                    $menus = $menus
                        ->map(function ($menu) use ($activeMenuPrefix) {
                            $menu->setRelation(
                                'children',
                                $menu->children
                                    ->filter(fn($child) => $this->menuMatchesPrefix($child, $activeMenuPrefix))
                                    ->values()
                            );

                            return $menu;
                        })
                        ->filter(fn($menu) => $this->menuMatchesPrefix($menu, $activeMenuPrefix) || $menu->children->isNotEmpty())
                        ->values();
                }
            }

            $view->with('sidebarMenus', $menus);
        });
    }

    private function activeMenuPrefix(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        foreach (['users.', 'menus.', 'database-backup.'] as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return 'admin-portal';
            }
        }

        foreach (['laporan-mingguan.', 'urls.', 'aset-tik.'] as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return $prefix;
            }
        }

        return null;
    }

    private function menuMatchesPrefix($menu, string $prefix): bool
    {
        if ($prefix === 'admin-portal') {
            return is_string($menu->url) && collect(['users.', 'menus.', 'database-backup.'])
                ->contains(fn(string $adminPrefix) => str_starts_with($menu->url, $adminPrefix));
        }

        return is_string($menu->url) && str_starts_with($menu->url, $prefix);
    }
}
