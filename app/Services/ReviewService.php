<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function summary(Game $game): array
    {
        if (DB::getDriverName() === 'pgsql') {
            $summary = DB::selectOne('SELECT contar_avaliacoes_jogo(?) AS total, media_avaliacoes_jogo(?) AS average', [$game->id, $game->id]);

            return [(int) $summary->total, $summary->average];
        }

        return [$game->reviews()->count(), $game->reviews()->avg('rating')];
    }

    public function update(Review $review, int $userId, array $data): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CALL atualizar_propria_avaliacao(?, ?, ?, ?)', [$review->id, $userId, (int) $data['rating'], $data['comment']]);

            return;
        }

        $review->update($data);
    }

    public function delete(Review $review, int $userId): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CALL excluir_propria_avaliacao(?, ?)', [$review->id, $userId]);

            return;
        }

        $review->delete();
    }
}
