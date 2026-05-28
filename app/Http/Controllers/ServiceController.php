<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('order')->orderBy('title')
            ->get()
            ->filter(fn(Service $service) => $this->canViewService($service))
            ->values();
        $groupedServices = $services->groupBy(fn(Service $service) => $service->category ?: 'Lainnya');
        $availableRoutes = $this->availablePortalRoutes();
        $canManagePortal = $this->canManagePortal();

        return view('portal', compact('availableRoutes', 'canManagePortal', 'groupedServices', 'services'));
    }

    public function create()
    {
        abort_unless($this->canManagePortal(), 403);

        return view('services.create');
    }

    public function store(Request $request)
    {
        abort_unless($this->canManagePortal(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'url' => 'required|string|max:255',
            'icon_class' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0|max:9999',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $data['order'] = $data['order'] ?? ((int) Service::max('order') + 1);

        Service::create($data);

        return redirect()->route('dashboard')->with('status', 'Sistem portal berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        abort_unless($this->canManagePortal(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'url' => 'required|string|max:255',
            'icon_class' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0|max:9999',
            'image' => 'nullable|image|max:2048',
            'remove_image' => ['nullable', Rule::in(['1'])],
        ]);

        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        } elseif ($request->boolean('remove_image') && $service->image) {
            Storage::disk('public')->delete($service->image);
            $data['image'] = null;
        }

        unset($data['remove_image']);
        $service->update($data);

        return redirect()->route('dashboard')->with('status', 'Sistem portal berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        abort_unless($this->canManagePortal(), 403);

        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()->route('dashboard')->with('status', 'Sistem portal dihapus.');
    }

    private function canManagePortal(): bool
    {
        return in_array(Auth::user()?->peran, ['admin', 'superadmin'], true);
    }

    private function canViewService(Service $service): bool
    {
        if (!$this->isAdminService($service)) {
            return true;
        }

        return $this->canManagePortal();
    }

    private function isAdminService(Service $service): bool
    {
        return in_array($service->url, [
            'users.index',
            'menus.index',
            'database-backup.index',
        ], true);
    }

    private function availablePortalRoutes(): array
    {
        $labels = [
            'laporan-mingguan.index' => 'Laporan Mingguan',
            'urls.index' => 'URL Shortener',
            'aset-tik.dashboard' => 'Manajemen Aset TIK',
            'users.index' => 'Panel Admin',
        ];

        return collect($labels)
            ->filter(fn($_label, string $route) => Route::has($route))
            ->all();
    }
}
