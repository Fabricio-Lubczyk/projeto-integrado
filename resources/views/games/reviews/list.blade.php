@if ($reviews->isEmpty())
    <p class="review-empty">Ainda não há avaliações. Seja a primeira pessoa a avaliar este jogo.</p>
@else
    <div class="review-list">
        @foreach ($reviews as $review)
            <article class="review-card">
                <div class="review-heading">
                    <div class="pn-review-author"><span class="pn-author-avatar" aria-hidden="true">{{ mb_substr($review->user->name, 0, 1) }}</span><div><h3>{{ $review->user->name }}</h3>@if($review->is_demo)<small>Perfil de demonstração</small>@else<small>Jogador da comunidade</small>@endif</div></div>
                    <span class="pn-user-score {{ $review->rating >= 4 ? 'pn-score-good' : ($review->rating >= 3 ? 'pn-score-mixed' : 'pn-score-low') }}">{{ $review->rating }}<small>/5</small><span class="sr-only">{{ $review->rating }} de 5</span></span>
                </div>
                @auth
                    @if (auth()->id() === $review->user_id)
                        <a href="{{ route('reviews.edit', $review) }}">Editar avaliação</a>
                        <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Excluir esta avaliação?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Excluir avaliação</button>
                        </form>
                    @endif
                @endauth
                <p class="review-comment">{{ $review->comment }}</p>
                <time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</time>
            </article>
        @endforeach
    </div>
    <div class="catalog-pagination">{{ $reviews->links('partials.pagination') }}</div>
@endif
