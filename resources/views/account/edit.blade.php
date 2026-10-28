@extends('layouts.app')

@section('title', 'Editar conta')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-editar">
        <h1 id="titulo-editar" class="auth-title">Editar conta<span>.</span></h1>
        <p class="auth-description">Altere seu nome ou o e-mail usado para entrar.</p>
        <form action="{{ route('account.update') }}" method="POST" class="auth-form">
            @csrf
            @method('PATCH')
            <x-form.input name="name" label="Nome" :value="old('name', $user->name)" autocomplete="name" maxlength="100" required autofocus />
            <x-form.input name="email" label="E-mail" type="email" :value="old('email', $user->email)" autocomplete="email" maxlength="255" required />
            <button class="primary-button" type="submit">Salvar alterações</button>
        </form>
        <a class="account-back" href="{{ route('account.show') }}">Voltar para minha conta</a>
    </section>
@endsection
