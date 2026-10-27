<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_open_administration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.index'))
            ->assertForbidden()
            ->assertSee('Sua conta não tem permissão');
    }

    public function test_administrator_can_open_administration(): void
    {
        $user = User::factory()->administrator()->create();

        $this->actingAs($user)->get(route('admin.index'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Administrador');
    }

    public function test_regular_user_does_not_see_administration_link(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('inicio'))
            ->assertDontSee('href="'.route('admin.index').'"', false);
    }

    public function test_administrator_sees_administration_link(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('inicio'))
            ->assertSee('href="'.route('admin.index').'"', false);
    }
}
