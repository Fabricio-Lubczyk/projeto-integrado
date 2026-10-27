<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_available(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Crie sua conta');
    }

    public function test_visitor_can_register_and_is_signed_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => '  Jogador Novo  ',
            'email' => '  NOVO@example.com  ',
            'password' => 'senha-segura-2026',
            'password_confirmation' => 'senha-segura-2026',
            'is_admin' => true,
        ]);

        $response->assertRedirect(route('inicio'))->assertSessionHasNoErrors();
        $user = User::where('email', 'novo@example.com')->firstOrFail();
        $this->assertSame('Jogador Novo', $user->name);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('senha-segura-2026', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_email_cannot_be_registered_again_with_different_case(): void
    {
        User::factory()->create(['email' => 'jogador@example.com']);

        $this->post(route('register.store'), [
            'name' => 'Outro Jogador',
            'email' => 'JOGADOR@example.com',
            'password' => 'senha-segura-2026',
            'password_confirmation' => 'senha-segura-2026',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_invalid_registration_does_not_create_a_user(): void
    {
        $this->post(route('register.store'), [
            'name' => '',
            'email' => 'email-invalido',
            'password' => 'curta',
            'password_confirmation' => 'outra',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_signed_in_user_is_redirected_away_from_registration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('register'))
            ->assertRedirect(route('inicio'));
    }
}
