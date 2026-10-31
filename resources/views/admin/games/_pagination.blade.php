@if($paginator->hasPages())
    <nav class="admin-game-pagination" aria-label="Paginação dos jogos">
        @if($paginator->onFirstPage())
            <span class="admin-pagination-disabled" aria-disabled="true">Anterior</span>
        @else
            <a class="account-back" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
        @endif

        <p>Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</p>

        @if($paginator->hasMorePages())
            <a class="account-back" href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima</a>
        @else
            <span class="admin-pagination-disabled" aria-disabled="true">Próxima</span>
        @endif
    </nav>
@endif
