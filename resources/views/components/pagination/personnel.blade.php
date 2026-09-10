@if ($paginator->hasPages()) <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">


    <p class="text-sm text-gray-600">
        Showing
        <span class="font-semibold text-gray-900">
            {{ $paginator->firstItem() }}
        </span>
        to
        <span class="font-semibold text-gray-900">
            {{ $paginator->lastItem() }}
        </span>
        of
        <span class="font-semibold text-gray-900">
            {{ $paginator->total() }}
        </span>
        results
    </p>

    <nav class="flex items-center gap-1" aria-label="Pagination">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                <span class="hidden sm:inline">Previous</span>
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-indigo-600"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                <span class="hidden sm:inline">Previous</span>
            </a>
        @endif

        <div class="flex items-center gap-1">

            @foreach ($elements as $element)

                @if (is_string($element))
                    <span class="px-2 text-sm font-medium text-gray-400">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)

                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-indigo-600 px-3 text-sm font-semibold text-white shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a
                                href="{{ $url }}"
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-medium text-gray-600 transition hover:bg-indigo-50 hover:text-indigo-600"
                            >
                                {{ $page }}
                            </a>
                        @endif

                    @endforeach
                @endif

            @endforeach

        </div>

        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-indigo-600"
            >
                <span class="hidden sm:inline">Next</span>

                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                <span class="hidden sm:inline">Next</span>

                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path class="hidden sm:inline" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        @endif

    </nav>
</div>


@endif
