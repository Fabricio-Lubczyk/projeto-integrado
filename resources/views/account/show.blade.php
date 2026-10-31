@extends('layouts.app')

@section('title', 'Minha conta')

@section('content')
    <section class="auth-panel" aria-labelledby="titulo-conta">
        @include('partials.account-navigation')
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

        <section aria-labelledby="minhas-avaliacoes">
            <h2 id="minhas-avaliacoes">Minhas avaliações</h2>
            @forelse ($reviews as $review)
                <p><a href="{{ route('games.show', $review->game) }}">{{ $review->game->title }}</a> — {{ $review->rating }} de 5</p>
            @empty
                <p>Você ainda não avaliou nenhum jogo.</p>
            @endforelse
            {{ $reviews->links() }}
        </section>

        <a class="account-back" href="{{ route('inicio') }}">Voltar ao início</a>
    </section>
@endsection
