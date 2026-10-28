@extends('layouts.app')

@section('title', 'Cadastrar jogo')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-jogo">
        <h1 id="titulo-jogo" class="auth-title">Cadastrar jogo<span>.</span></h1>
        <p class="auth-description">Adicione os dados que vão aparecer no catálogo.</p>
        <form action="{{ route('admin.games.store') }}" method="POST" class="auth-form">
            @csrf
            <x-form.input name="title" label="Título" :value="old('title')" maxlength="150" required autofocus />
            <x-form.input name="developer" label="Desenvolvedora" :value="old('developer')" maxlength="120" required />
            <x-form.input name="released_on" label="Data de lançamento (opcional)" type="date" :value="old('released_on')" />
            <div class="form-field">
                <label for="description">Descrição</label>
                <textarea class="form-input" id="description" name="description" rows="6" maxlength="5000" required aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" @error('description') aria-describedby="erro-description" @enderror>{{ old('description') }}</textarea>
                @error('description')<p id="erro-description" class="field-error">{{ $message }}</p>@enderror
            </div>
            <fieldset class="taxonomy-fieldset">
                <legend>Gêneros</legend>
                <div class="taxonomy-options">
                    @forelse($genres as $option)
                        <label class="taxonomy-option">
                            <input type="checkbox" name="genres[]" value="{{ $option->id }}" @checked(in_array((string) $option->id, array_map('strval', (array) old('genres', [])), true))>
                            <span>{{ $option->name }}</span>
                        </label>
                    @empty
                        <p>Nenhuma opção cadastrada.</p>
                    @endforelse
                </div>
                @foreach($errors->get('genres') + $errors->get('genres.*') as $messages)
                    @foreach($messages as $message)<p class="field-error">{{ $message }}</p>@endforeach
                @endforeach
            </fieldset>
            <fieldset class="taxonomy-fieldset">
                <legend>Plataformas</legend>
                <div class="taxonomy-options">
                    @forelse($platforms as $option)
                        <label class="taxonomy-option">
                            <input type="checkbox" name="platforms[]" value="{{ $option->id }}" @checked(in_array((string) $option->id, array_map('strval', (array) old('platforms', [])), true))>
                            <span>{{ $option->name }}</span>
                        </label>
                    @empty
                        <p>Nenhuma opção cadastrada.</p>
                    @endforelse
                </div>
                @foreach($errors->get('platforms') + $errors->get('platforms.*') as $messages)
                    @foreach($messages as $message)<p class="field-error">{{ $message }}</p>@endforeach
                @endforeach
            </fieldset>
            <button type="submit" class="primary-button">Cadastrar jogo</button>
        </form>
        <a class="account-back" href="{{ route('admin.index') }}">Voltar para administração</a>
    </section>
@endsection
