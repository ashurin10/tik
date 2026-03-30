<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_aset' => 'required|string|max:255',
            'tahun_pengadaan' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'kategori' => 'required|string',
            'jenis' => 'required|string',
            'kondisi' => 'required|in:Baik,Cukup,Rusak',
            'status' => 'required|in:Aktif,Dipinjam,Maintenance,Pensiun',
            'unit_pengguna' => 'required|string',
            'penanggung_jawab' => 'required|string',
            'pemilik_aset' => 'nullable|string|max:255',
            'spesifikasi' => 'nullable|array',
        ];
    }
}
