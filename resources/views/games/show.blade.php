@extends('layouts.app')

@section('title', $game->title)

@section('content')
    <article class="game-detail" aria-labelledby="titulo-jogo">
        <a class="account-back" href="{{ route('games.index') }}">Voltar ao catálogo</a>
        <h1 id="titulo-jogo">{{ $game->title }}</h1>
        <dl class="account-details">
            <div><dt>Desenvolvedora</dt><dd>{{ $game->developer }}</dd></div>
            <div><dt>Lançamento</dt><dd>{{ $game->released_on?->format('d/m/Y') ?? 'Não informado' }}</dd></div>
            <div><dt>Gêneros</dt><dd>{{ $game->genres->pluck('name')->join(', ') ?: 'Nenhum gênero informado.' }}</dd></div>
            <div><dt>Plataformas</dt><dd>{{ $game->platforms->pluck('name')->join(', ') ?: 'Nenhuma plataforma informada.' }}</dd></div>
        </dl>
        <section aria-labelledby="sobre-jogo">
            <h2 id="sobre-jogo">Sobre o jogo</h2>
            <p class="game-description">{{ $game->description }}</p>
        </section>
    </article>
@endsection
