<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

trait HasHashid
{
    /**
     * Get the encoded AES Base64 string.
     * We keep the term 'hashid' for compatibility with frontend/AlpineJS bindings.
     *
     * @return string
     */
    public function getHashidAttribute()
    {
        return Crypt::encryptString((string) $this->getKey());
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
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
            try {
                $decoded = Crypt::decryptString($value);
            } catch (DecryptException $e) {
                return null;
            }

            return $this->where($this->getKeyName(), $decoded)->first();
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
