<form class="review-form" method="POST" action="{{ route('reviews.store', $game) }}">
    @csrf
    @if ($errors->has('review'))
        <p class="review-error" role="alert">{{ $errors->first('review') }}</p>
    @endif
    <div class="form-field">
        <label for="rating">Sua nota</label>
        <select id="rating" name="rating" required aria-describedby="rating-error">
            <option value="">Selecione de 1 a 5</option>
            @foreach (range(1, 5) as $rating)
                <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} de 5</option>
            @endforeach
        </select>
        @error('rating')<span id="rating-error" class="review-error">{{ $message }}</span>@enderror
    </div>
    <div class="form-field">
        <label for="comment">Comentário</label>
        <textarea id="comment" name="comment" rows="5" maxlength="2000" required aria-describedby="comment-error">{{ old('comment') }}</textarea>
        @error('comment')<span id="comment-error" class="review-error">{{ $message }}</span>@enderror
    </div>
    <button class="primary-button" type="submit">Enviar avaliação</button>
</form>
