<header class="site-header">
    <div class="page-container header-content">
        <a class="site-brand" href="{{ route('inicio') }}">
            <span class="pn-mark" aria-hidden="true">p<span>n</span></span>
            <span class="pn-wordmark">Play<span>Nota</span></span>
        </a>

        <nav class="main-navigation" aria-label="Navegação principal">
            <a class="navigation-link{{ request()->routeIs('inicio') ? ' is-active' : '' }}" href="{{ route('inicio') }}" @if(request()->routeIs('inicio')) aria-current="page" @endif>Início</a>
            <a class="navigation-link{{ request()->routeIs('games.*') ? ' is-active' : '' }}" href="{{ route('games.index') }}" @if(request()->routeIs('games.*')) aria-current="page" @endif>Jogos</a>
            <a class="navigation-link" href="{{ route('inicio') }}#sobre-projeto">Sobre</a>
        </nav>

        <form class="header-search" action="{{ route('games.index') }}" method="GET" role="search">
            <label class="sr-only" for="header-q">Buscar jogos</label>
            <input id="header-q" name="q" placeholder="Buscar jogos" maxlength="100" type="search">
            <button type="submit" aria-label="Buscar jogos">⌕</button>
        </form>
        <div class="header-actions">
            @auth
                @if (auth()->user()->canManageGames())
                    <a class="account-link" href="{{ route('admin.index') }}">Administração</a>
                @endif
                <a class="account-link account-name" href="{{ route('account.show') }}" @if(request()->routeIs('account.*')) aria-current="page" @endif>Minha conta</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-button">Sair</button>
                </form>
            @else
                @if (Route::has('login'))
                    <a class="account-link" href="{{ route('login') }}">Entrar</a>
                @endif
                @if (Route::has('register'))
                    <a class="account-link account-link-emphasis" href="{{ route('register') }}">Criar conta</a>
                @endif
            @endauth
        </div>
    </div>
</header>
