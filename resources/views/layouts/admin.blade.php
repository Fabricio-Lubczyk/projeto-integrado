<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administração') | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-area">
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.index') }}"><span aria-hidden="true">PN</span>{{ config('app.name') }}</a>
            <p class="admin-sidebar-label">Administração</p>
            <nav aria-label="Administração">
                <a href="{{ route('admin.index') }}" @if(request()->routeIs('admin.index')) aria-current="page" @endif><span aria-hidden="true">▦</span> Painel</a>
                <a href="{{ route('admin.games.index') }}" @if(request()->routeIs('admin.games.index', 'admin.games.edit', 'admin.games.delete')) aria-current="page" @endif><span aria-hidden="true">☷</span> Gerenciar jogos</a>
                <a href="{{ route('admin.games.create') }}" @if(request()->routeIs('admin.games.create')) aria-current="page" @endif><span aria-hidden="true">+</span> Cadastrar jogo</a>
            </nav>
            <div class="admin-sidebar-bottom">
                <a href="{{ route('games.index') }}">Abrir catálogo <span aria-hidden="true">↗</span></a>
                <a href="{{ route('account.show') }}">Ver minha conta</a>
                <div class="admin-user"><span class="admin-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>Administrador</small></div></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sair da conta</button></form>
            </div>
        </aside>
        <div class="admin-body">
            <header class="admin-topbar"><span>Administração <span aria-hidden="true">/</span> <strong>@yield('title', 'Painel')</strong></span><a href="{{ route('inicio') }}">Voltar ao site ↗</a></header>
            <main class="admin-main" id="conteudo" tabindex="-1">
                @include('partials.flash')
                @yield('admin-content')
            </main>
        </div>
    </div>
</body>
</html>
