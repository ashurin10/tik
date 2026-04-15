<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMasterAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_aset' => 'nullable|string|max:255',
            'nama_aset' => 'required|string|max:255',
            'tahun_pengadaan' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'kategori' => 'required|string',
            'jenis' => 'required|string',
            'kondisi' => 'required|in:Baik,Cukup,Rusak',
            'status' => 'required|in:Aktif,Terpakai,Maintenance,Pensiun',
            'unit_pengguna' => 'required|string',
            'penanggung_jawab' => 'required|string',
            'merk' => 'nullable|string|max:255',
            'model_tipe' => 'nullable|string|max:255',
            'nomor_seri' => 'nullable|string|max:255',
            'pemilik_aset' => 'nullable|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'spesifikasi' => 'nullable|array',
        ];
    }
}
