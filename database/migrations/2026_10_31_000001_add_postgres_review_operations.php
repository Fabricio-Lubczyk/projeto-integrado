<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION contar_avaliacoes_jogo(p_game_id bigint) RETURNS bigint
            LANGUAGE sql STABLE AS $$
                SELECT count(*) FROM reviews WHERE game_id = p_game_id
            $$
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION media_avaliacoes_jogo(p_game_id bigint) RETURNS numeric
            LANGUAGE sql STABLE AS $$
                SELECT avg(rating)::numeric FROM reviews WHERE game_id = p_game_id
            $$
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE atualizar_propria_avaliacao(p_review_id bigint, p_user_id bigint, p_rating integer, p_comment text)
            LANGUAGE plpgsql AS $$
            BEGIN
                UPDATE reviews SET rating = p_rating, comment = p_comment
                WHERE id = p_review_id AND user_id = p_user_id;
                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Avaliacao nao encontrada para este usuario';
                END IF;
            END;
            $$
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE excluir_propria_avaliacao(p_review_id bigint, p_user_id bigint)
            LANGUAGE plpgsql AS $$
            BEGIN
                DELETE FROM reviews WHERE id = p_review_id AND user_id = p_user_id;
                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Avaliacao nao encontrada para este usuario';
                END IF;
            END;
            $$
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION validar_avaliacao() RETURNS trigger
            LANGUAGE plpgsql AS $$
            BEGIN
                IF NEW.rating NOT BETWEEN 1 AND 5 OR length(trim(NEW.comment)) = 0 OR length(NEW.comment) > 2000 THEN
                    RAISE EXCEPTION 'Dados da avaliacao invalidos' USING ERRCODE = '23514';
                END IF;
                RETURN NEW;
            END;
            $$
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER validar_dados_avaliacao BEFORE INSERT OR UPDATE ON reviews
            FOR EACH ROW EXECUTE FUNCTION validar_avaliacao()
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION atualizar_data_avaliacao() RETURNS trigger
            LANGUAGE plpgsql AS $$
            BEGIN
                NEW.updated_at := CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER atualizar_data_avaliacao_trigger BEFORE UPDATE ON reviews
            FOR EACH ROW EXECUTE FUNCTION atualizar_data_avaliacao()
        SQL);
        DB::statement('CREATE INDEX avaliacoes_jogo_data_idx ON reviews (game_id, created_at DESC)');
        DB::statement('CREATE INDEX avaliacoes_usuario_data_idx ON reviews (user_id, created_at DESC)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS atualizar_data_avaliacao_trigger ON reviews');
        DB::unprepared('DROP TRIGGER IF EXISTS validar_dados_avaliacao ON reviews');
        DB::unprepared('DROP FUNCTION IF EXISTS atualizar_data_avaliacao()');
        DB::unprepared('DROP FUNCTION IF EXISTS validar_avaliacao()');
        DB::unprepared('DROP PROCEDURE IF EXISTS excluir_propria_avaliacao(bigint, bigint)');
        DB::unprepared('DROP PROCEDURE IF EXISTS atualizar_propria_avaliacao(bigint, bigint, integer, text)');
        DB::unprepared('DROP FUNCTION IF EXISTS media_avaliacoes_jogo(bigint)');
        DB::unprepared('DROP FUNCTION IF EXISTS contar_avaliacoes_jogo(bigint)');
        DB::statement('DROP INDEX IF EXISTS avaliacoes_jogo_data_idx');
        DB::statement('DROP INDEX IF EXISTS avaliacoes_usuario_data_idx');
    }
};
