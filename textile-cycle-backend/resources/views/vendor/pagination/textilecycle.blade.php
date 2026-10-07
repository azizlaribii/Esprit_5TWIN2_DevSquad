@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" style="display:flex;flex-direction:column;align-items:center;gap:.75rem">

    <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;justify-content:center">

        {{-- Précédent --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary btn-sm" style="opacity:.4;cursor:not-allowed">
                <span class="material-icons-round" style="font-size:1.1rem">chevron_left</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm">
                <span class="material-icons-round" style="font-size:1.1rem">chevron_left</span>
            </a>
        @endif

        {{-- Numéros de pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="color:var(--text-muted);padding:0 .3rem">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-primary btn-sm" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-secondary btn-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Suivant --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm">
                <span class="material-icons-round" style="font-size:1.1rem">chevron_right</span>
            </a>
        @else
            <span class="btn btn-secondary btn-sm" style="opacity:.4;cursor:not-allowed">
                <span class="material-icons-round" style="font-size:1.1rem">chevron_right</span>
            </span>
        @endif
    </div>

    <p style="font-size:.8rem;color:var(--text-muted)">
        Affichage de {{ $paginator->firstItem() }} à {{ $paginator->lastItem() }} sur {{ $paginator->total() }} résultats
    </p>
</nav>
@endif