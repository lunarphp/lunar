{{-- this is copy of filament's paginator to achieve more standardized look --}}
{{-- changes: added Livewire's scrollTo --}}
{{-- changes: remove "showing 1 to of x records" --}}
@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth' })
JS
    : '';

$isRtl = __('filament-panels::layout.direction') === 'rtl';
@endphp

<nav
    aria-label="{{ __('filament::components/pagination.label') }}"
    role="navigation"
    class="lunar-panel-timeline-pagination flex justify-end items-center gap-x-3"
>
    @if ($paginator->hasPages())
        <ol
            class="lunar-panel-timeline-pagination-items flex justify-self-end rounded-lg bg-white shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
        >
            @if (! $paginator->onFirstPage())
                <x-lunarpanel::activity-log.timeline-paginator.item
                    :aria-label="__('filament::components/pagination.actions.previous.label')"
                    :icon="$isRtl ? 'heroicon-m-chevron-right' : 'heroicon-m-chevron-left'"
                    icon-alias="pagination.previous-button"
                    rel="prev"
                    :wire:click="'previousPage(\'' . $paginator->getPageName() . '\')'"
                    :wire:key="$this->getId() . '.pagination.previous'"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                />
            @endif

            @foreach ($paginator->render()->offsetGet('elements') as $element)
                @if (is_string($element))
                    <x-lunarpanel::activity-log.timeline-paginator.item disabled :label="$element" />
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <x-lunarpanel::activity-log.timeline-paginator.item
                            :active="$page === $paginator->currentPage()"
                            :aria-label="trans_choice('filament::components/pagination.actions.go_to_page.label', $page, ['page' => $page])"
                            :label="$page"
                            :wire:click="'gotoPage(' . $page . ', \'' . $paginator->getPageName() . '\')'"
                            :wire:key="$this->getId() . '.pagination.' . $paginator->getPageName() . '.' . $page"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        />
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <x-lunarpanel::activity-log.timeline-paginator.item
                    :aria-label="__('filament::components/pagination.actions.next.label')"
                    :icon="$isRtl ? 'heroicon-m-chevron-left' : 'heroicon-m-chevron-right'"
                    icon-alias="pagination.next-button"
                    rel="next"
                    :wire:click="'nextPage(\'' . $paginator->getPageName() . '\')'"
                    :wire:key="$this->getId() . '.pagination.next'"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                />
            @endif
        </ol>
    @endif
</nav>
