<header class="site-header">
    <div class="page-container header-content">
        <a class="site-brand" href="{{ route('inicio') }}">
            <span class="brand-symbol" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 8h12l2.2 8.1a2.1 2.1 0 0 1-3.5 2.1L14 16h-4l-2.7 2.2a2.1 2.1 0 0 1-3.5-2.1L6 8Z" stroke-linejoin="round" />
                    <path d="M7 12h4m-2-2v4m7-3h.01m2 3h.01" stroke-linecap="round" />
                </svg>
            </span>
            <span>{{ config('app.name') }}</span>
        </a>

        <nav class="main-navigation" aria-label="Navegação principal">
            <a class="navigation-link{{ request()->routeIs('inicio') ? ' is-active' : '' }}" href="{{ route('inicio') }}" @if(request()->routeIs('inicio')) aria-current="page" @endif>Início</a>
            <a class="navigation-link" href="{{ route('inicio') }}#sobre-projeto">Sobre o projeto</a>
        </nav>

        <div class="header-actions">
            @auth
                <span class="account-name">{{ auth()->user()->name }}</span>
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
