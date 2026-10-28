<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_change_password(): void
    {
        $this->get(route('account.password.edit'))->assertRedirect(route('login'));
        $this->put(route('account.password.update'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_change_password_and_login_again(): void
    {
        $user = User::factory()->create();
        $token = $user->remember_token;
        $this->actingAs($user)->get(route('account.password.edit'))->assertOk();
        $this->put(route('account.password.update'), [
            'current_password' => 'password', 'password' => 'nova-senha-123', 'password_confirmation' => 'nova-senha-123',
        ])->assertRedirect(route('account.show'))->assertSessionHas('status');
        $this->assertTrue(Hash::check('nova-senha-123', $user->fresh()->password));
        $this->assertNotSame($token, $user->fresh()->remember_token);
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'nova-senha-123'])->assertRedirect(route('inicio'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_current_password_does_not_change_hash(): void
    {
        $user = User::factory()->create();
        $hash = $user->password;
        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'errada', 'password' => 'nova-senha-123', 'password_confirmation' => 'nova-senha-123',
        ])->assertSessionHasErrors('current_password');
        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_short_unconfirmed_and_unchanged_passwords_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        foreach ([['curta', 'curta'], ['nova-senha-123', 'outra'], ['password', 'password']] as [$password, $confirmation]) {
            $this->put(route('account.password.update'), [
                'current_password' => 'password', 'password' => $password, 'password_confirmation' => $confirmation,
            ])->assertSessionHasErrors('password');
        }
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(session()->hasOldInput('current_password'));
    }
}
