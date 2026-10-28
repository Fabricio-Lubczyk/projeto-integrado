<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_edit_or_save_account(): void
    {
        $this->get(route('account.edit'))->assertRedirect(route('login'));
        $this->patch(route('account.update'), [])->assertRedirect(route('login'));
    }

    public function test_user_updates_own_data_without_changing_permissions(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->get(route('account.edit'))->assertOk()->assertSee($user->email);
        $this->patch(route('account.update'), [
            'name' => '  Fabrício  ', 'email' => ' NOVO@EXAMPLE.COM ', 'is_admin' => true, 'id' => $other->id,
        ])->assertRedirect(route('account.show'))->assertSessionHas('status');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Fabrício', 'email' => 'novo@example.com', 'is_admin' => false]);
        $this->assertSame($other->email, $other->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_current_email_can_be_kept(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('account.update'), ['name' => 'Outro nome', 'email' => $user->email])->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_duplicate_and_invalid_data_are_rejected(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->patch(route('account.update'), ['name' => '', 'email' => strtoupper($other->email)])
            ->assertSessionHasErrors(['name', 'email']);
        $this->assertSame($user->email, $user->fresh()->email);
        $this->patch(route('account.update'), ['name' => str_repeat('a', 101), 'email' => 'invalido'])
            ->assertSessionHasErrors(['name', 'email']);
    }
}
