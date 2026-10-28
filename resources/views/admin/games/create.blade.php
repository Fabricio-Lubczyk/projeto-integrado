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
            <button type="submit" class="primary-button">Cadastrar jogo</button>
        </form>
        <a class="account-back" href="{{ route('admin.index') }}">Voltar para administração</a>
    </section>
@endsection
