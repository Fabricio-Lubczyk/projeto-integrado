@extends('layouts.app')

@section('title', 'Alterar senha')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-senha">
        @include('partials.account-navigation')
        <h1 id="titulo-senha" class="auth-title">Alterar senha<span>.</span></h1>
        <p class="auth-description">Confirme sua senha atual antes de escolher outra.</p>
        <form action="{{ route('account.password.update') }}" method="POST" class="auth-form">
            @csrf
            @method('PUT')
            <x-form.input name="current_password" label="Senha atual" type="password" autocomplete="current-password" required autofocus />
            <x-form.input name="password" label="Nova senha" type="password" autocomplete="new-password" minlength="8" required />
            <p class="field-hint">Use pelo menos 8 caracteres e uma senha diferente da atual.</p>
            <x-form.input name="password_confirmation" label="Confirmar nova senha" type="password" autocomplete="new-password" required />
            <button class="primary-button" type="submit">Alterar senha</button>
        </form>
        <a class="account-back" href="{{ route('account.show') }}">Voltar para minha conta</a>
    </section>
@endsection
