<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Http\Requests\StoreUrlRequest;
use App\Http\Requests\UpdateUrlRequest;
use App\Services\UrlService;

class UrlController extends Controller
{
    protected $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function index()
    {
        $urls = Url::latest()->paginate(10);
        return view('urls.index', compact('urls'));
    }

    public function store(StoreUrlRequest $request)
    {
        $this->urlService->createUrl($request->validated());
        return redirect()->route('urls.index')->with('success', 'URL created successfully.');
    }

    public function update(UpdateUrlRequest $request, Url $url)
    {
        $this->urlService->updateUrl($url, $request->validated());
        return redirect()->route('urls.index')->with('success', 'URL updated successfully.');
    }

    public function destroy(Url $url)
    {
        $url->delete();
        return redirect()->route('urls.index')->with('success', 'URL deleted successfully.');
    }

    public function redirect($shortCode)
    {
        $originalUrl = $this->urlService->incrementClickAndRedirect($shortCode);
        return redirect()->away($originalUrl);
    }
}
