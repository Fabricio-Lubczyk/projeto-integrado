@if ($reviews->isEmpty())
    <p class="review-empty">Ainda não há avaliações. Seja a primeira pessoa a avaliar este jogo.</p>
@else
    <div class="review-list">
        @foreach ($reviews as $review)
            <article class="review-card">
                <div class="review-heading">
                    <h3>{{ $review->user->name }}</h3>
                    <span>{{ $review->rating }} de 5</span>
                </div>
                <p class="review-comment">{{ $review->comment }}</p>
                <time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</time>
            </article>
        @endforeach
    </div>
    <div class="catalog-pagination">{{ $reviews->links() }}</div>
@endif
