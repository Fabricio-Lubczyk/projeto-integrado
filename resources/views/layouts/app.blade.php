<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Um espaço para descobrir jogos e compartilhar suas experiências.">
        <title>@yield('title', 'Início') | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body>
        <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
        <div class="site-shell">
            @include('partials.header')
            <main id="conteudo" class="site-main" tabindex="-1">
                <div class="page-container">
                    @yield('content')
                </div>
            </main>
            @include('partials.footer')
        </div>
        @stack('scripts')
    </body>
</html>
