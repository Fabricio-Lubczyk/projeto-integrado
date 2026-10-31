@extends('layouts.app')

@section('title', 'Gerenciar jogos')

@section('content')
    <section aria-labelledby="titulo-jogos-admin">
        <h1 id="titulo-jogos-admin">Gerenciar jogos<span>.</span></h1>
        <p class="intro-description">Consulte e edite os jogos cadastrados no catálogo.</p>
        <a class="account-back" href="{{ route('admin.games.create') }}">Cadastrar jogo</a>

        <form action="{{ route('admin.games.index') }}" method="GET" class="catalog-search" role="search">
            <div class="form-field">
                <label for="q">Buscar pelo título</label>
                <input class="form-input" type="search" id="q" name="q" value="{{ $search }}" maxlength="100" @error('q') aria-invalid="true" aria-describedby="erro-q" @enderror>
                @error('q')<p class="field-error" id="erro-q">{{ $message }}</p>@enderror
            </div>
            <button class="primary-button" type="submit">Buscar</button>
            @if($search !== '')<a class="account-back" href="{{ route('admin.games.index') }}">Limpar busca</a>@endif
        </form>

        <p class="catalog-count">{{ $games->total() }} {{ $games->total() === 1 ? 'jogo encontrado' : 'jogos encontrados' }}</p>
        <div class="games-grid">
            @forelse($games as $game)
                <article class="game-card">
                    <h2>{{ $game->title }}</h2>
                    <p>{{ $game->developer }}</p>
                    <div class="admin-game-actions">
                        <a class="account-back" href="{{ route('games.show', $game) }}">Ver no catálogo</a>
                        <a class="account-back" href="{{ route('admin.games.edit', $game) }}">Editar<span class="sr-only"> {{ $game->title }}</span></a>
                    </div>
                </article>
            @empty
                <div class="catalog-empty">
                    @if($search !== '')
                        <p>Nenhum jogo encontrado para esta busca.</p>
                        <a class="account-back" href="{{ route('admin.games.index') }}">Limpar busca</a>
                    @else
                        <p>Nenhum jogo cadastrado por enquanto.</p>
                        <a class="account-back" href="{{ route('admin.games.create') }}">Cadastrar o primeiro jogo</a>
                    @endif
                </div>
            @endforelse
        </div>
        <div class="catalog-pagination">{{ $games->links('admin.games._pagination') }}</div>
        <a class="account-back" href="{{ route('admin.index') }}">Voltar para administração</a>
    </section>
@endsection
