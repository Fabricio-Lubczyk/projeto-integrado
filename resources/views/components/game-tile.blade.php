@props(['game'])
<article class="pn-game">
    <a class="pn-cover" href="{{ route('games.show', $game) }}" aria-label="Ver {{ $game->title }}">
        @if ($game->cover_url)
            <img class="pn-cover-image" src="{{ $game->cover_url }}" alt="{{ $game->title }}" loading="lazy" decoding="async" width="460" height="215">
        @else
            <span class="pn-cover-word">{{ $game->title }}</span>
        @endif
        @unless($game->cover_url)<span class="pn-cover-footer">{{ $game->developer }} <span aria-hidden="true">↗</span></span>@endunless
    </a>
    <div class="pn-game-info">
        <span class="pn-score {{ $game->reviews_avg_rating === null ? 'pn-score-empty' : ($game->reviews_avg_rating >= 3.5 ? 'pn-score-good' : ($game->reviews_avg_rating >= 2.5 ? 'pn-score-mixed' : 'pn-score-low')) }}" aria-label="{{ $game->reviews_avg_rating === null ? 'Sem avaliações' : 'Nota '.number_format($game->reviews_avg_rating, 1, ',', '.').' de 5' }}">{{ $game->reviews_avg_rating === null ? '—' : number_format($game->reviews_avg_rating, 1, ',', '.') }}</span>
        <div><h2><a href="{{ route('games.show', $game) }}">{{ $game->title }}</a></h2><p>{{ $game->released_on?->format('d/m/Y') ?? 'Lançamento não informado' }}</p></div>
    </div>
    <ul class="pn-game-genres" aria-label="Gêneros">@foreach($game->genres as $item)<li>{{ $item->name }}</li>@endforeach</ul>
    <p class="pn-game-meta">{{ $game->reviews_count }} {{ $game->reviews_count === 1 ? 'avaliação' : 'avaliações' }}</p>
</article>
