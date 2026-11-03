@extends('layouts.admin')
@section('title', 'Alterar cargo')
@section('admin-content')
    <h1>Alterar cargo</h1>
    <p class="intro-description">{{ $user->name }} · {{ $user->email }}</p>
    <form class="auth-panel" method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf @method('PATCH')
        @if($errors->any())<p class="field-error" role="alert">{{ $errors->first() }}</p>@endif
        @include('admin.users._role', ['selectedRole' => old('role', $user->adminRole())])
        @if($user->is(auth()->user()))<p>Você não pode retirar seu próprio acesso de administrador geral.</p>@endif
        <button class="primary-button" type="submit">Salvar cargo</button>
        <a class="account-back" href="{{ route('admin.users.index') }}">Voltar</a>
    </form>
@endsection
