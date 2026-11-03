@extends('layouts.admin')
@section('title', 'Cadastrar conta')
@section('admin-content')
    <h1>Cadastrar conta</h1>
    <p class="intro-description">Crie uma conta e escolha o que ela pode acessar.</p>
    <form class="auth-panel" method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        @if($errors->any())<div class="field-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="form-field"><label for="name">Nome</label><input class="form-input" id="name" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" required></div>
        <div class="form-field"><label for="email">E-mail</label><input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required></div>
        <div class="form-field"><label for="password">Senha inicial</label><input class="form-input" type="password" id="password" name="password" minlength="8" autocomplete="new-password" required></div>
        <div class="form-field"><label for="password_confirmation">Confirmar senha</label><input class="form-input" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
        @include('admin.users._role', ['selectedRole' => old('role', 'player')])
        <button class="primary-button" type="submit">Cadastrar conta</button>
        <a class="account-back" href="{{ route('admin.users.index') }}">Voltar</a>
    </form>
@endsection
