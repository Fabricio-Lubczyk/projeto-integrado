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

        <section class="reviews-section" aria-labelledby="avaliacoes-titulo">
            <h2 id="avaliacoes-titulo">Avaliações</h2>
            @if ($reviewCount)
                <p>{{ number_format($reviewAverage, 1, ',', '.') }} de 5 · {{ $reviewCount }} {{ $reviewCount === 1 ? 'avaliação' : 'avaliações' }}</p>
            @else
                <p>Ainda sem avaliações.</p>
            @endif

            @auth
                @if ($hasReviewed)
                    <p class="review-notice" role="status">Você já enviou uma avaliação para este jogo.</p>
                @else
                    @include('games.reviews.form')
                @endif
            @else
                @include('games.reviews.invitation')
            @endauth

            @include('games.reviews.list')
        </section>
    </article>
@endsection
