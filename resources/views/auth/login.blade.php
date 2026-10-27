@extends('layouts.app')

@section('title', 'Entrar')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-login">
        <p class="eyebrow"><span aria-hidden="true"></span> Bom ter você por aqui</p>
        <h1 id="titulo-login" class="auth-title">Entre na sua conta<span>.</span></h1>
        <p class="auth-description">Continue de onde parou e acompanhe suas próximas descobertas.</p>

        <form action="{{ route('login.store') }}" method="POST" class="auth-form">
            @csrf
            <x-form.input name="email" label="E-mail" type="email" :value="old('email')" autocomplete="email" maxlength="255" required autofocus />
            <x-form.input name="password" label="Senha" type="password" autocomplete="current-password" required />
            <label class="remember-option">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Manter conectado</span>
            </label>
            <button type="submit" class="primary-button">Entrar</button>
        </form>

        <p class="auth-alternative">Ainda não tem conta? <a href="{{ route('register') }}">Criar conta</a></p>
    </section>
@endsection
