@extends('layouts.app')

@section('title', 'Excluir jogo')

@section('content')
    <section class="auth-panel game-deletion" aria-labelledby="titulo-exclusao">
        <h1 id="titulo-exclusao" class="auth-title">Excluir jogo<span>?</span></h1>
        <dl class="account-details">
            <div>
                <dt>Título</dt>
                <dd>{{ $game->title }}</dd>
            </div>
            <div>
                <dt>Desenvolvedora</dt>
                <dd>{{ $game->developer }}</dd>
            </div>
        </dl>
        <p class="auth-description" id="aviso-exclusao">Este jogo e todas as suas avaliações serão excluídos. Esta ação não pode ser desfeita.</p>

        <form action="{{ route('admin.games.destroy', $game) }}" method="POST" class="auth-form">
            @csrf
            @method('DELETE')
            <div class="form-field">
                <label class="remember-option" for="confirm">
                    <input type="checkbox" id="confirm" name="confirm" value="1" required aria-describedby="aviso-exclusao{{ $errors->has('confirm') ? ' erro-confirm' : '' }}" aria-invalid="{{ $errors->has('confirm') ? 'true' : 'false' }}">
                    <span>Confirmo a exclusão deste jogo e de suas avaliações.</span>
                </label>
                @error('confirm')<p class="field-error" id="erro-confirm">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="primary-button danger-button">Excluir jogo</button>
        </form>
        <a class="account-back" href="{{ route('admin.games.index') }}">Cancelar e voltar para a lista</a>
    </section>
@endsection
