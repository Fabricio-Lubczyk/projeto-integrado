@extends('layouts.app')

@section('title', 'Editar jogo')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-jogo">
        <h1 id="titulo-jogo" class="auth-title">Editar jogo<span>.</span></h1>
        <p class="auth-description">Atualize os dados que aparecem no catálogo.</p>
        <form action="{{ route('admin.games.update', $game) }}" method="POST" class="auth-form">
            @csrf
            @method('PUT')
            @include('admin.games._form', ['game' => $game])
            <button type="submit" class="primary-button">Salvar alterações</button>
        </form>
        <a class="account-back" href="{{ route('admin.games.index') }}">Voltar para a lista de jogos</a>
    </section>
@endsection
