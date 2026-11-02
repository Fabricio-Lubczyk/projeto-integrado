@extends('layouts.admin')

@section('title', 'Painel')

@section('admin-content')
    <header class="admin-page-heading">
        <div><h1>Painel</h1><p>Gerencie os jogos publicados no site.</p></div>
        <a class="primary-button" href="{{ route('admin.games.create') }}"><span aria-hidden="true">+</span> Cadastrar jogo</a>
    </header>
    <div class="admin-overview">
        <div><span>Jogos cadastrados</span><strong>{{ $gameCount }}</strong></div>
        <div><span>Avaliações recebidas</span><strong>{{ $reviewCount }}</strong></div>
    </div>
    <section class="admin-register" aria-labelledby="cadastros-recentes">
        <header><h2 id="cadastros-recentes">Últimos jogos cadastrados</h2><a href="{{ route('admin.games.index') }}">Gerenciar jogos →</a></header>
        @if($games->isNotEmpty())
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead><tr><th scope="col">Jogo</th><th scope="col">Desenvolvedora</th><th scope="col">Cadastro</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
                    <tbody>
                        @foreach($games as $game)
                            <tr><td><a href="{{ route('games.show', $game) }}">{{ $game->title }}</a></td><td>{{ $game->developer }}</td><td>{{ $game->created_at->format('d/m/Y') }}</td><td><a href="{{ route('admin.games.edit', $game) }}">Editar<span class="sr-only"> {{ $game->title }}</span></a></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-empty"><h3>Nenhum jogo cadastrado</h3><p>Os jogos adicionados ao catálogo vão aparecer aqui.</p><a href="{{ route('admin.games.create') }}">Cadastrar o primeiro jogo</a></div>
        @endif
    </section>
@endsection
