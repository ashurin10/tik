<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventarisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('inventari')->id ?? null;
        return [
            'kode_barang' => "required|string|unique:inventaris,kode_barang,{$id}",
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string',
            'satuan' => 'required|string',
            'keterangan' => 'nullable|string',
        ];
    }
}
