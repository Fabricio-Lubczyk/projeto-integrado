@extends('layouts.app')

@section('title', 'Editar conta')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-editar">
        <h1 id="titulo-editar" class="auth-title">Editar conta<span>.</span></h1>
        <p class="auth-description">Confira seu nome e e-mail.</p>
        <dl class="account-details">
            <div><dt>Nome</dt><dd>{{ $user->name }}</dd></div>
            <div><dt>E-mail</dt><dd>{{ $user->email }}</dd></div>
        </dl>
        <a class="account-back" href="{{ route('account.show') }}">Voltar para minha conta</a>
    </section>
@endsection
