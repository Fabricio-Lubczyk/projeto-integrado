@extends('layouts.app')

@section('title', 'Minha conta')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-conta">
        <p class="eyebrow"><span aria-hidden="true"></span> Seu espaço</p>
        <h1 id="titulo-conta" class="auth-title">Minha conta<span>.</span></h1>
        <p class="auth-description">Estes são os dados que você usou para entrar na comunidade.</p>

        <dl class="account-details">
            <div>
                <dt>Nome</dt>
                <dd>{{ $user->name }}</dd>
            </div>
            <div>
                <dt>E-mail</dt>
                <dd>{{ $user->email }}</dd>
            </div>
            <div>
                <dt>Na comunidade desde</dt>
                <dd>{{ $user->created_at?->format('d/m/Y') }}</dd>
            </div>
        </dl>

        <a class="account-back" href="{{ route('account.edit') }}">Editar dados</a>

        <a class="account-back" href="{{ route('inicio') }}">Voltar ao início</a>
    </section>
@endsection
