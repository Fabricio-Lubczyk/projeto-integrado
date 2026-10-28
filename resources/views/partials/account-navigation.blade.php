<nav class="account-navigation" aria-label="Minha conta">
    <a href="{{ route('account.show') }}" @if(request()->routeIs('account.show')) aria-current="page" @endif>Resumo</a>
    <a href="{{ route('account.edit') }}" @if(request()->routeIs('account.edit')) aria-current="page" @endif>Editar dados</a>
    <a href="{{ route('account.password.edit') }}" @if(request()->routeIs('account.password.*')) aria-current="page" @endif>Alterar senha</a>
</nav>
