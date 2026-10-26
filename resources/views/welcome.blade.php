@extends('layouts.app')

@section('title', 'Início')

@section('content')
    <section class="page-intro" aria-labelledby="titulo-inicio">
        <p class="eyebrow"><span aria-hidden="true"></span> Avaliações de jogos</p>
        <h1 id="titulo-inicio">Cada jogo,<br>uma nova <span>experiência.</span></h1>
        <p class="intro-description">Um espaço para descobrir jogos, compartilhar opiniões e encontrar a sua próxima aventura.</p>
    </section>

    <section class="discovery-panel" aria-labelledby="titulo-descobertas">
        <div class="discovery-symbol" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="8" y="12" width="32" height="25" rx="6" />
                <path d="M15 23h10m-5-5v10m12-7h.01m4 5h.01M18 8h12" stroke-linecap="round" />
            </svg>
        </div>
        <p class="panel-eyebrow">Boas histórias merecem ser compartilhadas</p>
        <h2 id="titulo-descobertas">O próximo capítulo começa aqui.</h2>
        <p class="panel-description">Em breve, os jogos e as avaliações da comunidade vão ocupar este espaço.</p>
        <span class="availability-label"><span aria-hidden="true"></span> Novidades em breve</span>
    </section>
@endsection
