<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('url')->id ?? null;
        return [
            'judul' => 'required|string|max:255',
            'original_url' => 'required|url',
            'short_code' => "nullable|string|alpha_dash|unique:urls,short_code,{$id}",
            'status' => 'boolean',
        ];
    }
}
