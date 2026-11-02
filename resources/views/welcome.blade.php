@extends('layouts.app')
@section('title', 'Jogos e avaliações')
@section('content')
    <header class="pn-page-title"><div><p>PLAYNOTA / JOGOS</p><h1>O que você vai jogar?</h1></div><a href="{{ route('games.index') }}">Explorar todos os jogos <span aria-hidden="true">↗</span></a></header>
    @if($games->isNotEmpty())
        <section class="pn-front-feature" aria-label="Jogos em destaque">
            <div class="pn-carousel" data-carousel role="region" aria-roledescription="carrossel" aria-label="Destaques do catálogo" tabindex="0">
                <div class="pn-carousel-slides">
                    @foreach($games->take(5) as $featured)
                        <a class="pn-feature pn-slide" data-slide href="{{ route('games.show', $featured) }}" @if(!$loop->first) hidden @endif aria-label="{{ $featured->title }} — destaque {{ $loop->iteration }} de {{ min(5, $games->count()) }}">
                            @if($featured->cover_url)<img src="{{ $featured->screenshots[0] ?? $featured->cover_url }}" alt="{{ $featured->title }}" width="1280" height="720" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>@endif
                            <div class="pn-feature-content"><span class="pn-feature-label">EM DESTAQUE</span><h2>{{ $featured->title }}</h2><p>{{ Str::limit($featured->description, 140) }}</p><span class="pn-feature-bottom"><span class="pn-score {{ $featured->reviews_avg_rating >= 3.5 ? 'pn-score-good' : 'pn-score-mixed' }}">{{ $featured->reviews_avg_rating === null ? '—' : number_format($featured->reviews_avg_rating, 1, ',', '.') }}</span><span>{{ $featured->reviews_count }} avaliações</span><span aria-hidden="true">→</span></span></div>
                        </a>
                    @endforeach
                </div>
                @if($games->count() > 1)
                    <div class="pn-carousel-controls" data-carousel-controls hidden>
                        <div class="pn-carousel-dots">@foreach($games->take(5) as $featured)<button type="button" data-slide-to="{{ $loop->index }}" aria-label="Mostrar destaque {{ $loop->iteration }}: {{ $featured->title }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>@endforeach</div>
                        <span data-carousel-status role="status" aria-live="polite">1 / {{ min(5, $games->count()) }}</span>
                        <button type="button" data-prev aria-label="Destaque anterior">←</button>
                        <button type="button" data-next aria-label="Próximo destaque">→</button>
                    </div>
                @endif
            </div>
            <div class="pn-front-picks"><h2>Mais para descobrir</h2>@foreach($games->skip(5)->take(3) as $game)<a href="{{ route('games.show', $game) }}"><img src="{{ $game->cover_url }}" alt="" width="460" height="215" loading="lazy"><div><h3>{{ $game->title }}</h3><p>{{ $game->genres->pluck('name')->take(2)->join(' / ') }}</p></div><span class="pn-score {{ $game->reviews_avg_rating >= 3.5 ? 'pn-score-good' : 'pn-score-mixed' }}">{{ $game->reviews_avg_rating === null ? '—' : number_format($game->reviews_avg_rating, 1, ',', '.') }}</span></a>@endforeach</div>
        </section>
    @endif
    @if($categories->isNotEmpty())
        <nav class="pn-category-nav" aria-label="Explorar por categoria"><span>Explorar por gênero</span><div>@foreach($categories as $category)<a href="#categoria-{{ $category->id }}">{{ $category->name }} <small>{{ $category->games_count }}</small></a>@endforeach</div></nav>
    @endif
    <div class="pn-home-columns">
        <div class="pn-category-sections">
            @forelse($categories as $category)
                <section id="categoria-{{ $category->id }}" class="pn-category-section" aria-labelledby="genero-{{ $category->id }}">
                    <div class="pn-section-head"><div><h2 id="genero-{{ $category->id }}">{{ $category->name }}</h2><p>{{ $category->games_count }} jogos no catálogo</p></div><a href="{{ route('games.index', ['genre' => $category->id]) }}">Ver todos →</a></div>
                    <div class="pn-category-grid">@foreach($category->games as $game)<x-game-tile :game="$game" />@endforeach</div>
                </section>
            @empty
                <section><div class="pn-section-head"><h2>Explore o catálogo</h2><a href="{{ route('games.index') }}">Ver todos →</a></div><div class="pn-tiles">@forelse($games as $game)<x-game-tile :game="$game" />@empty<p class="pn-empty">Nenhum jogo cadastrado por enquanto.</p>@endforelse</div></section>
            @endforelse
        </div>
        <aside class="pn-ranking" aria-labelledby="ranking"><div class="pn-section-head"><h2 id="ranking">Bem avaliados</h2></div><p class="pn-ranking-caption">Notas dos jogadores · escala de 1 a 5</p>@forelse($topGames as $game)<a class="pn-ranking-item" href="{{ route('games.show', $game) }}"><span class="pn-rank-number">{{ $loop->iteration }}</span>@if($game->cover_url)<img src="{{ $game->cover_url }}" alt="" width="80" height="50" loading="lazy">@endif<strong>{{ $game->title }}</strong><span class="pn-score pn-score-good">{{ number_format($game->reviews_avg_rating, 1, ',', '.') }}</span></a>@empty<p class="pn-ranking-empty">Nenhum jogo avaliado por enquanto.</p>@endforelse</aside>
    </div>
    <section class="pn-reviews" aria-labelledby="opinioes"><div class="pn-section-head"><div><h2 id="opinioes">A comunidade está comentando</h2><p>Experiências e opiniões sobre os jogos do catálogo.</p></div></div><div class="pn-review-grid">@forelse($latestReviews as $review)<article><div class="pn-review-top">@if($review->game->cover_url)<img src="{{ $review->game->cover_url }}" alt="" width="100" height="60" loading="lazy">@endif<a href="{{ route('games.show', $review->game) }}">{{ $review->game->title }}</a><span class="pn-score {{ $review->rating >= 4 ? 'pn-score-good' : ($review->rating >= 3 ? 'pn-score-mixed' : 'pn-score-low') }}">{{ $review->rating }}<small>/5</small></span></div><p>{{ Str::limit($review->comment, 320) }}</p><footer><span class="pn-comment-initial" aria-hidden="true">{{ mb_substr($review->user->name, 0, 1) }}</span><div><strong>{{ $review->user->name }}</strong><small>{{ $review->created_at->format('d/m/Y') }}{{ $review->is_demo ? ' · Demonstração' : '' }}</small></div></footer></article>@empty<p class="pn-ranking-empty">Ainda não há opiniões publicadas.</p>@endforelse</div></section>
@endsection
