<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

/**
 * Trait untuk meng-encode ID model menggunakan Hashids (deterministik).
 *
 * Perbedaan dengan implementasi sebelumnya:
 * - Menggunakan Hashids alih-alih Crypt::encryptString (hasil tetap sama untuk ID yang sama)
 * - URL stabil, bisa di-bookmark dan di-share
 * - Lebih ringan secara komputasi
 */
trait HasHashid
{
    /**
     * Get the encoded Hashid string.
     *
     * @return string
     */
    public function getHashidAttribute(): string
    {
        return Hashids::connection('main')->encode($this->getKey());
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'hashid';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (empty($field) || $field === $this->getRouteKeyName()) {
            $decoded = Hashids::connection('main')->decode($value);

            if (empty($decoded)) {
                return null;
            }

            return $this->where($this->getKeyName(), $decoded[0])->first();
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
