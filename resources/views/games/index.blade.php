@extends('layouts.app')

@section('title', 'Jogos')

@section('content')
    <section aria-labelledby="titulo-catalogo">
        <h1 id="titulo-catalogo">Catálogo de jogos<span>.</span></h1>
        <p class="intro-description">Encontre um jogo pelo título e conheça seus detalhes.</p>
        <form action="{{ route('games.index') }}" method="GET" class="catalog-search" role="search">
            <div class="form-field">
                <label for="q">Buscar pelo título</label>
                <input class="form-input" type="search" id="q" name="q" value="{{ $search }}" maxlength="100" @error('q') aria-invalid="true" aria-describedby="erro-q" @enderror>
                @error('q')<p class="field-error" id="erro-q">{{ $message }}</p>@enderror
            </div>
            <button class="primary-button" type="submit">Buscar</button>
            @if($search !== '')<a class="account-back" href="{{ route('games.index') }}">Limpar busca</a>@endif
        </form>
        <p class="catalog-count">{{ $games->total() }} {{ $games->total() === 1 ? 'jogo encontrado' : 'jogos encontrados' }}</p>
        <div class="games-grid">
            @forelse($games as $game)
                <article class="game-card">
                    <h2><a href="{{ route('games.show', $game) }}">{{ $game->title }}</a></h2>
                    <p>{{ $game->developer }}</p>
                    <p>{{ $game->released_on?->format('d/m/Y') ?? 'Lançamento não informado' }}</p>
                </article>
            @empty
                <p class="catalog-empty">{{ $search !== '' ? 'Nenhum jogo encontrado para esta busca.' : 'Nenhum jogo cadastrado por enquanto.' }}</p>
            @endforelse
        </div>
        <div class="catalog-pagination">{{ $games->links() }}</div>
    </section>
@endsection
