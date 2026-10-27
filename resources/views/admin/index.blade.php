@extends('layouts.app')

@section('title', 'Administração')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-admin">
        <p class="eyebrow"><span aria-hidden="true"></span> Acesso administrativo</p>
        <h1 id="titulo-admin" class="auth-title">Administração<span>.</span></h1>
        <p class="auth-description">Área reservada para gerenciar a comunidade e o catálogo de jogos.</p>

        <dl class="account-details">
            <div>
                <dt>Conectado como</dt>
                <dd>{{ auth()->user()->name }}</dd>
            </div>
            <div>
                <dt>Perfil de acesso</dt>
                <dd>Administrador</dd>
            </div>
        </dl>

        <a class="account-back" href="{{ route('account.show') }}">Ver minha conta</a>
    </section>
@endsection
