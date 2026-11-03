@extends('layouts.admin')
@section('title', 'Usuários e cargos')
@section('admin-content')
    <h1>Usuários e cargos</h1>
    <p class="intro-description">Gerencie o acesso às contas e ao catálogo.</p>
    <a class="primary-button" href="{{ route('admin.users.create') }}">Cadastrar conta</a>
    <div class="admin-role-guide">
        <p><strong>Administrador geral:</strong> gerencia usuários, cargos e jogos.</p>
        <p><strong>Responsável pelo catálogo:</strong> cadastra, edita e exclui jogos. Não altera contas ou cargos.</p>
        <p><strong>Jogador:</strong> consulta jogos e gerencia as próprias avaliações.</p>
    </div>
    <form method="GET" class="catalog-search" action="{{ route('admin.users.index') }}">
        <div class="form-field"><label for="q">Nome ou e-mail</label><input class="form-input" id="q" name="q" value="{{ $search }}" maxlength="100"></div>
        <div class="form-field"><label for="role">Cargo</label><select class="form-input" id="role" name="role"><option value="">Todos</option><option value="admin" @selected($role === 'admin')>Administrador geral</option><option value="catalog" @selected($role === 'catalog')>Responsável pelo catálogo</option><option value="player" @selected($role === 'player')>Jogador</option></select></div>
        <button class="primary-button">Buscar</button>
        <a class="account-back" href="{{ route('admin.users.index') }}">Limpar</a>
    </form>
    @if($errors->any())<p class="field-error">{{ $errors->first() }}</p>@endif
    <p class="catalog-count">{{ $users->total() }} contas encontradas</p>
    <div class="admin-users-table"><table class="admin-table"><thead><tr><th>Nome</th><th>E-mail</th><th>Cargo</th><th>Ação</th></tr></thead><tbody>
        @forelse($users as $user)
            <tr><td>{{ $user->name }} @if($user->is(auth()->user()))<small>(você)</small>@endif</td><td>{{ $user->email }}</td><td>{{ $user->roleLabel() }}</td><td><a class="account-back" href="{{ route('admin.users.edit', $user) }}">Alterar cargo<span class="sr-only"> de {{ $user->name }}</span></a></td></tr>
        @empty<tr><td colspan="4">Nenhuma conta encontrada.</td></tr>@endforelse
    </tbody></table></div>
    <div class="catalog-pagination">{{ $users->links('admin.games._pagination') }}</div>
@endsection
