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
                $role = $user->peran; // Assuming 'peran' column stores 'admin', 'user'

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
            }

            $view->with('sidebarMenus', $menus);
        });
    }
}
