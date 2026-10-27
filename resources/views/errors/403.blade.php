@extends('layouts.app')

@section('title', 'Acesso restrito')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-acesso-restrito">
        <p class="eyebrow"><span aria-hidden="true"></span> Acesso restrito</p>
        <h1 id="titulo-acesso-restrito" class="auth-title">Essa área é restrita<span>.</span></h1>
        <p class="auth-description">Sua conta não tem permissão para acessar esta página.</p>
        <a class="account-back" href="{{ route('inicio') }}">Voltar ao início</a>
    </section>
@endsection
