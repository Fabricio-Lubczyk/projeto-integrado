@extends('layouts.app')

@section('title', 'Gerenciar jogos')

@section('content')
    <section aria-labelledby="titulo-jogos-admin">
        <h1 id="titulo-jogos-admin">Gerenciar jogos<span>.</span></h1>
        <p class="intro-description">Consulte os jogos cadastrados no catálogo.</p>
        <a class="account-back" href="{{ route('admin.games.create') }}">Cadastrar jogo</a>


        <p class="catalog-count">{{ $games->total() }} {{ $games->total() === 1 ? 'jogo encontrado' : 'jogos encontrados' }}</p>
        <div class="games-grid">
            @forelse($games as $game)
                <article class="game-card">
                    <h2>{{ $game->title }}</h2>
                    <p>{{ $game->developer }}</p>
                    <a class="account-back" href="{{ route('games.show', $game) }}">Ver no catálogo</a>
                </article>
            @empty
                <div class="catalog-empty">
                    <p>Nenhum jogo cadastrado por enquanto.</p>
                    <a class="account-back" href="{{ route('admin.games.create') }}">Cadastrar o primeiro jogo</a>
                </div>
            @endforelse
        </div>
        <div class="catalog-pagination">{{ $games->links() }}</div>
        <a class="account-back" href="{{ route('admin.index') }}">Voltar para administração</a>
    </section>
@endsection
