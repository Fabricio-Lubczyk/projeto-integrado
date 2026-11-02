@extends('layouts.app')

@section('title', $game->title)

@section('content')
    <article class="game-detail" aria-labelledby="titulo-jogo">
        <a class="account-back" href="{{ route('games.index') }}">Voltar ao catálogo</a>
        <header class="pn-detail-header">
            @if($game->cover_url)<img src="{{ $game->cover_url }}" alt="{{ $game->title }}" width="920" height="430" fetchpriority="high" class="pn-detail-cover">@endif
            <div class="pn-detail-heading"><p class="pn-label">JOGO / {{ $game->developer }}</p><h1 id="titulo-jogo">{{ $game->title }}</h1><p class="pn-detail-genres">{{ $game->genres->pluck('name')->join(' · ') }}</p>
                @if($game->steam_app_id)<a class="pn-steam-link" href="https://store.steampowered.com/app/{{ $game->steam_app_id }}/" target="_blank" rel="noopener noreferrer">Ver na Steam ↗</a>@endif
            </div>
        </header>
        <dl class="account-details pn-game-facts">
            <div><dt>Desenvolvedora</dt><dd>{{ $game->developer }}</dd></div>
            <div><dt>Lançamento</dt><dd>{{ $game->released_on?->format('d/m/Y') ?? 'Não informado' }}</dd></div>
            <div><dt>Gêneros</dt><dd>{{ $game->genres->pluck('name')->join(', ') ?: 'Nenhum gênero informado.' }}</dd></div>
            <div><dt>Plataformas</dt><dd>{{ $game->platforms->pluck('name')->join(', ') ?: 'Nenhuma plataforma informada.' }}</dd></div>
        </dl>
        <section aria-labelledby="sobre-jogo">
            <h2 id="sobre-jogo">Sobre o jogo</h2>
            <p class="game-description">{{ $game->description }}</p>
            @if($game->steam_app_id)<p class="pn-source-note">Informações e imagens: Steam. As avaliações identificadas como demonstração são fictícias.</p>@endif
            @if($game->screenshots)<div class="pn-screenshots">@foreach($game->screenshots as $shot)<a href="{{ $shot }}" target="_blank" rel="noopener noreferrer"><img src="{{ $shot }}" alt="Captura {{ $loop->iteration }} de {{ $game->title }}" width="640" height="360" loading="lazy" decoding="async"></a>@endforeach</div>@endif
        </section>

        <section class="reviews-section" aria-labelledby="avaliacoes-titulo">
            <h2 id="avaliacoes-titulo">Avaliações</h2>
            <div class="pn-review-summary">
                <div class="pn-summary-score">
                    <span class="pn-score {{ $reviewAverage >= 3.5 ? 'pn-score-good' : 'pn-score-mixed' }}">{{ $reviewCount ? number_format($reviewAverage, 1, ',', '.') : '—' }}</span>
                    <div>@if($reviewCount)<strong>{{ number_format($reviewAverage, 1, ',', '.') }} de 5</strong><p>{{ $reviewCount }} {{ $reviewCount === 1 ? 'avaliação' : 'avaliações' }}</p>@else<strong>Ainda sem avaliações.</strong>@endif</div>
                </div>
                <div class="pn-rating-bars" aria-label="Distribuição das notas">
                    @foreach(range(5, 1) as $rating)
                        <div><span>{{ $rating }}</span><div class="pn-rating-track"><span style="width: {{ $reviewCount ? round(($ratingDistribution[$rating] ?? 0) / $reviewCount * 100) : 0 }}%"></span></div><span>{{ $ratingDistribution[$rating] ?? 0 }}</span></div>
                    @endforeach
                </div>
            </div>

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
