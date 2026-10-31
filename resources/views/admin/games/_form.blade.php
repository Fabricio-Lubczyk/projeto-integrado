@php
    $game = $game ?? null;
    $defaultGenres = session()->hasOldInput() ? [] : ($game?->genres->modelKeys() ?? []);
    $defaultPlatforms = session()->hasOldInput() ? [] : ($game?->platforms->modelKeys() ?? []);
    $selectedGenres = array_map('strval', array_filter((array) old('genres', $defaultGenres), 'is_scalar'));
    $selectedPlatforms = array_map('strval', array_filter((array) old('platforms', $defaultPlatforms), 'is_scalar'));
    $values = [
        'title' => old('title', $game?->title),
        'developer' => old('developer', $game?->developer),
        'released_on' => old('released_on', $game?->released_on?->format('Y-m-d')),
        'description' => old('description', $game?->description),
    ];
    foreach ($values as $field => $value) {
        $values[$field] = is_scalar($value) ? (string) $value : '';
    }
@endphp

<x-form.input name="title" label="Título" :value="$values['title']" maxlength="150" required autofocus />
<x-form.input name="developer" label="Desenvolvedora" :value="$values['developer']" maxlength="120" required />
<x-form.input name="released_on" label="Data de lançamento (opcional)" type="date" :value="$values['released_on']" />
<div class="form-field">
    <label for="description">Descrição</label>
    <textarea class="form-input" id="description" name="description" rows="6" maxlength="5000" required aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" @error('description') aria-describedby="erro-description" @enderror>{{ $values['description'] }}</textarea>
    @error('description')<p id="erro-description" class="field-error">{{ $message }}</p>@enderror
</div>
<fieldset class="taxonomy-fieldset">
    <legend>Gêneros</legend>
    <div class="taxonomy-options">
        @forelse($genres as $option)
            <label class="taxonomy-option">
                <input type="checkbox" name="genres[]" value="{{ $option->id }}" @checked(in_array((string) $option->id, $selectedGenres, true))>
                <span>{{ $option->name }}</span>
            </label>
        @empty
            <p>Nenhuma opção cadastrada.</p>
        @endforelse
    </div>
    @error('genres')<p class="field-error">{{ $message }}</p>@enderror
    @foreach($errors->get('genres.*') as $messages)
        @foreach($messages as $message)<p class="field-error">{{ $message }}</p>@endforeach
    @endforeach
</fieldset>
<fieldset class="taxonomy-fieldset">
    <legend>Plataformas</legend>
    <div class="taxonomy-options">
        @forelse($platforms as $option)
            <label class="taxonomy-option">
                <input type="checkbox" name="platforms[]" value="{{ $option->id }}" @checked(in_array((string) $option->id, $selectedPlatforms, true))>
                <span>{{ $option->name }}</span>
            </label>
        @empty
            <p>Nenhuma opção cadastrada.</p>
        @endforelse
    </div>
    @error('platforms')<p class="field-error">{{ $message }}</p>@enderror
    @foreach($errors->get('platforms.*') as $messages)
        @foreach($messages as $message)<p class="field-error">{{ $message }}</p>@endforeach
    @endforeach
</fieldset>
