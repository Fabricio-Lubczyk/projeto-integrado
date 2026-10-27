<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Entre na sua conta');
    }

    public function test_user_can_login_with_normalized_email(): void
    {
        $user = User::factory()->create(['email' => 'jogador@example.com']);

        $this->post(route('login.store'), [
            'email' => ' JOGADOR@example.com ',
            'password' => 'password',
        ])->assertRedirect(route('inicio'))->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_does_not_authenticate(): void
    {
        User::factory()->create(['email' => 'jogador@example.com']);

        $this->post(route('login.store'), [
            'email' => 'jogador@example.com',
            'password' => 'senha-errada',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_empty_credentials_are_rejected(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_login_is_limited_after_five_failures(): void
    {
        $email = 'limite@example.com';
        User::factory()->create(['email' => $email]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => $email, 'password' => 'errada']);
        }

        $this->post(route('login.store'), ['email' => $email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Muitas tentativas.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_successful_login_clears_previous_failures(): void
    {
        $email = 'tentativas@example.com';
        User::factory()->create(['email' => $email]);
        $this->post(route('login.store'), ['email' => $email, 'password' => 'errada']);
        $this->post(route('login.store'), ['email' => $email, 'password' => 'password']);

        $this->assertSame(0, RateLimiter::attempts(Str::transliterate($email.'|127.0.0.1')));
        $this->assertAuthenticated();
    }

    public function test_remember_option_sets_authentication_cookie(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertCookie(auth()->guard()->getRecallerName());

        $this->assertAuthenticatedAs($user);
    }

    public function test_signed_in_user_cannot_open_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get(route('login'))->assertRedirect(route('inicio'));
    }
}
