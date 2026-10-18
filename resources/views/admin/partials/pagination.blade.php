@if ($paginator->hasPages())
    <nav class="admin-pagination" aria-label="Navigasi halaman">
        <p class="admin-pagination-info">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        <ul class="admin-pagination-list">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="admin-pagination-link is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">&lsaquo;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="admin-pagination-link" rel="prev" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="admin-pagination-link is-disabled" aria-disabled="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="admin-pagination-link is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="admin-pagination-link" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="admin-pagination-link" rel="next" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="admin-pagination-link is-disabled" aria-disabled="true" aria-label="Halaman berikutnya">&rsaquo;</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
