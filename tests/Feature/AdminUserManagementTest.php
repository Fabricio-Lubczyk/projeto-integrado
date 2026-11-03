<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_general_admin_can_access_account_management(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        foreach ([User::factory()->create(), $this->catalogManager()] as $user) {
            $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
            $this->get(route('admin.users.create'))->assertForbidden();
            $this->post(route('admin.users.store'), [])->assertForbidden();
            $this->get(route('admin.users.edit', $user))->assertForbidden();
            $this->patch(route('admin.users.update', $user), ['role' => 'admin'])->assertForbidden();
        }
    }

    public function test_admin_can_search_filter_and_open_role_form(): void
    {
        $admin = User::factory()->administrator()->create();
        $catalog = $this->catalogManager();
        $player = User::factory()->create(['name' => 'Conta para procurar']);
        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'Conta para procurar', 'role' => 'player']))
            ->assertOk()->assertSee($player->email)->assertDontSee($catalog->email);
        $this->get(route('admin.users.index', ['role' => 'catalog']))->assertOk()->assertSee($catalog->email)->assertDontSee($player->email);
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.users.edit', $catalog))->assertOk()->assertSee($catalog->email);
    }

    public function test_admin_can_create_each_role_with_hashed_password(): void
    {
        $this->actingAs(User::factory()->administrator()->create());
        foreach (['player', 'catalog', 'admin'] as $role) {
            $email = $role.'@example.test';
            $this->post(route('admin.users.store'), ['name' => 'Nova conta', 'email' => strtoupper($email), 'password' => 'SenhaTeste123!', 'password_confirmation' => 'SenhaTeste123!', 'role' => $role])
                ->assertRedirect(route('admin.users.index'));
            $user = User::where('email', $email)->firstOrFail();
            $this->assertSame($role, $user->adminRole());
            $this->assertTrue(Hash::check('SenhaTeste123!', $user->password));
        }
    }

    public function test_account_creation_validates_role_email_and_password(): void
    {
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)->post(route('admin.users.store'), ['name' => '', 'email' => $admin->email, 'password' => 'short', 'password_confirmation' => 'other', 'role' => 'owner'])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
        $this->assertSame(1, User::count());
    }

    public function test_admin_can_promote_change_and_revoke_roles(): void
    {
        $admin = User::factory()->administrator()->create();
        $target = User::factory()->create();
        $this->actingAs($admin);
        foreach (['admin', 'catalog', 'player'] as $role) {
            $this->patch(route('admin.users.update', $target), ['role' => $role])->assertRedirect(route('admin.users.index'));
            $this->assertSame($role, $target->fresh()->adminRole());
        }
        $this->actingAs($target->fresh())->get(route('admin.games.index'))->assertForbidden();
    }

    public function test_admin_cannot_remove_own_access_even_when_another_admin_exists(): void
    {
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)->patch(route('admin.users.update', $admin), ['role' => 'player'])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->is_admin);
        User::factory()->administrator()->create();
        $this->patch(route('admin.users.update', $admin), ['role' => 'catalog'])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_invalid_role_does_not_change_permissions(): void
    {
        $admin = User::factory()->administrator()->create();
        $target = User::factory()->create();
        $this->actingAs($admin)->patch(route('admin.users.update', $target), ['role' => 'owner'])->assertSessionHasErrors('role');
        $this->assertSame('player', $target->fresh()->adminRole());
    }

    public function test_catalog_manager_can_manage_games_on_web_and_api_but_not_users(): void
    {
        $catalog = $this->catalogManager();
        $this->actingAs($catalog)->get(route('admin.index'))->assertOk()->assertDontSee('href="'.route('admin.users.index').'"', false);
        $this->get(route('admin.games.index'))->assertOk();
        $this->get(route('admin.games.create'))->assertOk();
        $data = ['title' => 'Jogo da equipe', 'developer' => 'Estudio', 'description' => 'Descricao'];
        $this->post(route('admin.games.store'), $data)->assertRedirect();
        $game = Game::where('title', $data['title'])->firstOrFail();
        $this->get(route('admin.games.edit', $game))->assertOk();
        $this->put(route('admin.games.update', $game), [...$data, 'title' => 'Editado'])->assertRedirect();
        $id = $this->postJson(route('api.games.store'), [...$data, 'title' => 'Jogo da API'])->assertCreated()->json('data.id');
        $this->putJson(route('api.games.update', $id), [...$data, 'title' => 'API editada'])->assertOk();
        $this->deleteJson(route('api.games.destroy', $id))->assertNoContent();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_public_registration_and_profile_cannot_set_permissions(): void
    {
        $this->post(route('register.store'), ['name' => 'Jogador', 'email' => 'jogador@example.test', 'password' => 'SenhaTeste123!', 'password_confirmation' => 'SenhaTeste123!', 'is_admin' => true, 'is_catalog_manager' => true, 'role' => 'admin'])->assertRedirect();
        $user = User::where('email', 'jogador@example.test')->firstOrFail();
        $this->assertSame('player', $user->adminRole());
        $this->actingAs($user)->patch(route('account.update'), ['name' => 'Jogador', 'email' => $user->email, 'is_admin' => true, 'is_catalog_manager' => true])->assertRedirect();
        $this->assertSame('player', $user->fresh()->adminRole());
    }

    private function catalogManager(): User
    {
        return User::factory()->create(['is_catalog_manager' => true]);
    }
}
