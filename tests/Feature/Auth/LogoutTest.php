<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_ends_authentication_and_clears_previous_session(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['private-data' => 'remove-on-logout', '_token' => 'old-token'])
            ->post(route('logout'))
            ->assertRedirect(route('inicio'))
            ->assertSessionHas('status', 'Você saiu da sua conta.')
            ->assertSessionMissing('private-data');

        $this->assertGuest();
        $this->assertNotSame('old-token', session()->token());
    }

    public function test_guest_is_redirected_to_login_when_trying_to_logout(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_logout_does_not_accept_get_requests(): void
    {
        $this->actingAs(User::factory()->create())->get(route('logout'))->assertStatus(405);
        $this->assertAuthenticated();
    }
}
