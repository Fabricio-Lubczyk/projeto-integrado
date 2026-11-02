<?php

use App\Services\SteamGameService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('games:importar-steam {appid*}', function (SteamGameService $steam) {
    foreach ($this->argument('appid') as $id) {
        if (! ctype_digit((string) $id) || (int) $id < 1) {
            $this->error('Informe somente identificadores numericos da Steam.');

            return 1;
        }
        $game = $steam->import((int) $id);
        $this->info('Importado: '.$game->title);
    }
})->purpose('Importar ou atualizar jogos reais e capas pela API publica da Steam');
