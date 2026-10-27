<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_login_before_viewing_account(): void
    {
        $this->get(route('account.show'))->assertRedirect(route('login'));
    }

    public function test_account_shows_only_the_signed_in_users_data(): void
    {
        $user = User::factory()->create(['name' => 'Jogador da Conta', 'email' => 'minha-conta@example.com']);
        User::factory()->create(['name' => 'Outra Pessoa', 'email' => 'outra-pessoa@example.com']);

        $this->actingAs($user)->get(route('account.show'))
            ->assertOk()
            ->assertSee('Jogador da Conta')
            ->assertSee('minha-conta@example.com')
            ->assertDontSee('outra-pessoa@example.com');
    }

    public function test_login_returns_user_to_the_protected_page(): void
    {
        $user = User::factory()->create();
        $this->get(route('account.show'))->assertRedirect(route('login'));

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('account.show'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_account_is_no_longer_accessible_after_logout(): void
    {
        $this->actingAs(User::factory()->create())->post(route('logout'));
        $this->get(route('account.show'))->assertRedirect(route('login'));
    }
}
