@if($paginator->hasPages())
    <nav class="pn-pagination" aria-label="Paginação">
        <span>Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }} · {{ $paginator->total() }} resultados</span>
        <div>
            @if($paginator->onFirstPage())
                <span aria-disabled="true">← Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Anterior</a>
            @endif
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima →</a>
            @else
                <span aria-disabled="true">Próxima →</span>
            @endif
        </div>
    </nav>
@endif
