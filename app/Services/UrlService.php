<?php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Str;

class UrlService
{
    public function createUrl(array $data)
    {
        if (empty($data['short_code'])) {
            $data['short_code'] = Str::random(6);
            // Ensure unique randomly
            while (Url::where('short_code', $data['short_code'])->exists()) {
                $data['short_code'] = Str::random(6);
            }
        }

        return Url::create($data);
    }

    public function updateUrl(Url $url, array $data)
    {
        if (empty($data['short_code'])) {
            $data['short_code'] = Str::random(6);
            while (Url::where('short_code', $data['short_code'])->where('id', '!=', $url->id)->exists()) {
                $data['short_code'] = Str::random(6);
            }
        }
        $url->update($data);
        return $url;
    }

    public function incrementClickAndRedirect(string $shortCode)
    {
        $url = Url::where('short_code', $shortCode)->where('status', true)->firstOrFail();
        $url->increment('klik');
        return $url->original_url;
    }
}
