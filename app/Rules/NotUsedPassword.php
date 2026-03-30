<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\PasswordHistory;

class NotUsedPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();

        // Check current password
        if (Hash::check($value, $user->password)) {
            $fail('Password tidak boleh sama dengan password saat ini.');
            return;
        }

        // Check history
        $histories = \DB::table('password_histories')
            ->where('user_id', $user->id)
            ->get();

        foreach ($histories as $history) {
            if (Hash::check($value, $history->password)) {
                $fail('Password pernah digunakan sebelumnya. Gunakan password baru.');
                return;
            }
        }
    }
}
