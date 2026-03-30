<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'Password123!@#',
                'password_confirmation' => 'Password123!@#',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('Password123!@#', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'Password123!@#',
                'password_confirmation' => 'Password123!@#',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }
    public function test_password_cannot_be_reused(): void
    {
        $user = User::factory()->create();

        // First update
        $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'Password123!@#',
                'password_confirmation' => 'Password123!@#',
            ])
            ->assertSessionHasNoErrors();

        // Try to reuse the first password ('password') which should be in history
        $response = $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'Password123!@#',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');

        // Try to reuse the current password ('Password123!@#')
        $response = $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'Password123!@#',
                'password' => 'Password123!@#',
                'password_confirmation' => 'Password123!@#',
            ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }
}
