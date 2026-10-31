@extends('layouts.app')

@section('title', 'Cadastrar jogo')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-jogo">
        <h1 id="titulo-jogo" class="auth-title">Cadastrar jogo<span>.</span></h1>
        <p class="auth-description">Adicione os dados que vão aparecer no catálogo.</p>
        <form action="{{ route('admin.games.store') }}" method="POST" class="auth-form">
            @csrf
            @include('admin.games._form')
            <button type="submit" class="primary-button">Cadastrar jogo</button>
        </form>
        <a class="account-back" href="{{ route('admin.index') }}">Voltar para administração</a>
    </section>
@endsection
