<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLaporanMingguanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'hasil_deskripsi' => 'nullable|string',
            'prioritas' => 'required|string|max:50',
            'pic' => 'required|array|min:1',
            'pic.*' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'keterangan_tindak_lanjut' => 'nullable|string',
        ];
    }
}
