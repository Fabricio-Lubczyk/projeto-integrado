<div class="form-field">
    <label for="role">Cargo</label>
    <select class="form-input" id="role" name="role" required aria-describedby="role-help">
        <option value="player" @selected($selectedRole === 'player')>Jogador</option>
        <option value="catalog" @selected($selectedRole === 'catalog')>Responsável pelo catálogo</option>
        <option value="admin" @selected($selectedRole === 'admin')>Administrador geral</option>
    </select>
    <p id="role-help">O administrador geral também gerencia contas e cargos. O responsável pelo catálogo cuida apenas dos jogos.</p>
</div>
