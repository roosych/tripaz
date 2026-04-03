@if ($paginator->hasPages())
<nav aria-label="Pagination" class="tripaz-pagination">
    <div class="d-flex align-items-center justify-content-center">

        {{-- Page buttons --}}
        <ul class="pagination pagination-sm mb-0 gap-1">

            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link tripaz-page-link rounded-2">
                        <i class="ph ph-caret-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link tripaz-page-link rounded-2"
                       href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="ph ph-caret-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link tripaz-page-link rounded-2">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link tripaz-page-link tripaz-page-active rounded-2">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link tripaz-page-link rounded-2" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link tripaz-page-link rounded-2"
                       href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <i class="ph ph-caret-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link tripaz-page-link rounded-2">
                        <i class="ph ph-caret-right"></i>
                    </span>
                </li>
            @endif

        </ul>
    </div>
</nav>
@endif
