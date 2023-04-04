<th class="lt-px-4 lt-py-3 lt-text-sm lt-font-medium lt-text-left lt-text-gray-700 lt-whitespace-nowrap">
    @unless($sortable)
        <span class="lt-capitalize">
            {{ $heading }}
        </span>
    @else
        <button class="lt-flex lt-items-center lt-gap-0.5 lt-group focus:lt-outline-none focus:lt-ring focus:lt-ring-sky-100 lt-p-2 lt--m-2"
                wire:click="sort">
            <span class="lt-capitalize">
                {{ $heading }}
            </span>

            <span>
                @if ($sortField == $field)
                    <span @class([
                        'lt-block',
                        'lt-rotate-0' => $sortDir === 'asc',
                        'lt-rotate-180' => $sortDir === 'desc',
                    ])>
                        <svg class="lt-w-3 lt-h-3"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 15l7-7 7 7"></path>
                        </svg>
                    </span>
                @else
                    <span class="lt-transition lt-opacity-0 group-hover:lt-opacity-100">
                        <svg class="lt-w-3 lt-h-3"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 15l7-7 7 7"></path>
                        </svg>
                    </span>
                @endif
            </span>
        </button>
    @endunless
</th>
