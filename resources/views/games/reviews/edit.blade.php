@extends('layouts.app')

@section('title', 'Editar avaliação')

@section('content')
    <section class="auth-panel">
        <h1 class="auth-title">Editar avaliação<span>.</span></h1>
        <p>{{ $review->game->title }}</p>
        <form class="review-form" method="POST" action="{{ route('reviews.update', $review) }}">
            @csrf
            @method('PUT')
            <div class="form-field">
                <label for="rating">Sua nota</label>
                <select id="rating" name="rating" required>
                    @foreach (range(1, 5) as $rating)
                        <option value="{{ $rating }}" @selected(old('rating', $review->rating) == $rating)>{{ $rating }} de 5</option>
                    @endforeach
                </select>
                @error('rating')<span class="review-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-field">
                <label for="comment">Comentário</label>
                <textarea id="comment" name="comment" rows="5" maxlength="2000" required>{{ old('comment', $review->comment) }}</textarea>
                @error('comment')<span class="review-error">{{ $message }}</span>@enderror
            </div>
            <button class="primary-button" type="submit">Salvar avaliação</button>
        </form>
        <a class="account-back" href="{{ route('games.show', $review->game_id) }}">Voltar ao jogo</a>
    </section>
@endsection
