@if (isset($paginator) && $paginator->lastPage() > 1)
    @php
        $interval = isset($interval) ? abs(intval($interval)) : 3;
        $from = $paginator->currentPage() - $interval;
        if ($from < 1) {
            $from = 1;
        }
        $to = $paginator->currentPage() + $interval;
        if ($to > $paginator->lastPage()) {
            $to = $paginator->lastPage();
        }
    @endphp
    <div class="">
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                @if ($paginator->currentPage() > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($paginator->currentPage() - 1) }}"
                            aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>
                @endif

                <!-- links -->
                @for ($i = $from; $i <= $to; $i++)
                    <?php
                    $isCurrentPage = $paginator->currentPage() == $i;
                    ?>
                    <li class="page-item {{ $isCurrentPage ? 'active' : '' }}">
                        <a href="{{ !$isCurrentPage ? $paginator->url($i) : '#' }}" class="page-link">
                            {{ $i }}
                        </a>
                    </li>
                @endfor


                @if ($paginator->currentPage() < $paginator->lastPage())
                    <li class="page-item ">
                        <a class="page-link" href="{{ $paginator->url($paginator->currentPage() + 1) }}"
                            aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>

@endif

<label>Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }}
    registros</label>
