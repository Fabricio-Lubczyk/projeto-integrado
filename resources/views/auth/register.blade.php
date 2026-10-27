@extends('layouts.app')

@section('title', 'Criar conta')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-cadastro">
        <p class="eyebrow"><span aria-hidden="true"></span> Faça parte da comunidade</p>
        <h1 id="titulo-cadastro" class="auth-title">Crie sua conta<span>.</span></h1>
        <p class="auth-description">Um lugar para guardar e compartilhar suas opiniões sobre jogos.</p>

        <form action="{{ route('register.store') }}" method="POST" class="auth-form">
            @csrf
            <x-form.input name="name" label="Nome" :value="old('name')" autocomplete="name" maxlength="100" required autofocus />
            <x-form.input name="email" label="E-mail" type="email" :value="old('email')" autocomplete="email" maxlength="255" required />
            <x-form.input name="password" label="Senha" type="password" autocomplete="new-password" minlength="8" required />
            <p class="field-hint">Use pelo menos 8 caracteres.</p>
            <x-form.input name="password_confirmation" label="Confirmar senha" type="password" autocomplete="new-password" required />
            <button type="submit" class="primary-button">Criar conta</button>
        </form>

        @if (Route::has('login'))
            <p class="auth-alternative">Já tem uma conta? <a href="{{ route('login') }}">Entrar</a></p>
        @endif
    </section>
@endsection
