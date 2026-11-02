<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostgresReviewOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requer um banco PostgreSQL de teste isolado.');
        }
    }

    public function test_functions_procedures_and_update_trigger(): void
    {
        $review = Review::factory()->create(['rating' => 3]);
        $this->assertEquals(1, DB::selectOne('SELECT contar_avaliacoes_jogo(?) AS total', [$review->game_id])->total);
        $this->assertEquals(3, DB::selectOne('SELECT media_avaliacoes_jogo(?) AS average', [$review->game_id])->average);
        $old = $review->updated_at->copy()->subDay();
        DB::table('reviews')->where('id', $review->id)->update(['updated_at' => $old]);
        DB::statement('CALL atualizar_propria_avaliacao(?, ?, ?, ?)', [$review->id, $review->user_id, 5, 'Atualizado']);
        $this->assertSame(5, (int) $review->fresh()->rating);
        $this->assertTrue($review->fresh()->updated_at->greaterThan($old));
        $this->assertEquals(5, DB::selectOne('SELECT media_avaliacoes_jogo(?) AS average', [$review->game_id])->average);
        DB::statement('CALL excluir_propria_avaliacao(?, ?)', [$review->id, $review->user_id]);
        $this->assertEquals(0, DB::selectOne('SELECT contar_avaliacoes_jogo(?) AS total', [$review->game_id])->total);
        $this->assertNull(DB::selectOne('SELECT media_avaliacoes_jogo(?) AS average', [$review->game_id])->average);
    }

    public function test_trigger_rejects_invalid_reviews(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        try {
            DB::transaction(fn () => DB::table('reviews')->insert([
                'user_id' => $user->id, 'game_id' => $game->id, 'rating' => 9,
                'comment' => 'Inválido', 'created_at' => now(), 'updated_at' => now(),
            ]));
            $this->fail('O banco deveria rejeitar a nota inválida.');
        } catch (QueryException $exception) {
            $this->assertSame('23514', $exception->errorInfo[0]);
        }
        $this->assertDatabaseMissing('reviews', ['game_id' => $game->id]);
    }
}
