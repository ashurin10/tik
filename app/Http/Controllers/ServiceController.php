<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    // Portal View (Dashboard replacement)
    public function index()
    {
        $services = Service::orderBy('order')->get();
        // Group by category manually to preserve order if needed, or just use collection groupBy
        $groupedServices = $services->groupBy('category');

        return view('portal', compact('groupedServices', 'services'));
    }

    public function create()
    {
        if (Auth::user()->peran !== 'admin') {
            abort(403);
        }
        return view('services.create');
    }

    // Admin Methods (Superuser)
    public function store(Request $request)
    {
        // Simple check for superuser (role == 'admin') - can be middleware later
        if (Auth::user()->peran !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'url' => 'required|string', // Allow non-url internal paths if needed
            'icon_class' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Service::create($request->all());

        return redirect()->route('dashboard')->with('status', 'Layanan berhasil ditambahkan!');
    }

    public function update(Request $request, Service $service)
    {
        if (Auth::user()->peran !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'url' => 'required|string',
            'icon_class' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($service->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($data);

        return redirect()->route('dashboard')->with('status', 'Layanan berhasil diperbarui!');
    }

    public function destroy(Service $service)
    {
        if (Auth::user()->peran !== 'admin') {
            abort(403);
        }

        if ($service->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()->route('dashboard')->with('status', 'Layanan dihapus.');
    }
}
