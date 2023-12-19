<div x-data="{
    savingSearch: false,
    selectedRows: [],
    selectedAll: false,
    init() {
        Livewire.on('savedSearch', () => this.savingSearch = false)

        $watch('selectedRows', (vals) => {
          $wire.emit('table.selectedRows', vals)
        })

        $watch('selectedAll', (isChecked) => {
          this.selectedRows = isChecked ? {{ json_encode($this->rows->pluck('id')->toArray()) }} : []
        })

        window.livewire.on('bulkAction.reset', () => {
          this.selectedRows = []
        })

        window.livewire.on('bulkAction.complete', () => {
          this.selectedRows = []
        })
    }
}">
    <div x-cloak
         x-show="savingSearch">
        <x-l-tables::support.modal>
            <div class="lt-p-4">
                <div class="lt-flex lt-items-end lt-gap-4">
                    <div class="lt-flex-1">
                        <label for="SaveSearchName"
                               class="lt-block lt-text-xs lt-font-medium lt-text-gray-700 lt-capitalize">
                            Name
                        </label>

                        <input type="text"
                               id="SaveSearchName"
                               placeholder="Name"
                               wire:model="savedSearchName"
                               class="lt-w-full lt-mt-1 lt-text-sm lt-text-gray-700 lt-border-gray-200 lt-rounded-md focus:lt-outline-none focus:lt-ring focus:lt-ring-sky-100 focus:lt-border-sky-300 lt-form-input">
                    </div>

                    <x-l-tables::button theme="primary"
                                        wire:click="saveSearch">
                        Save Search
                    </x-l-tables::button>
                </div>

                @if ($errors->first('savedSearchName'))
                    <small class="lt-block lt-mt-1 lt-text-red-600">
                        {{ $errors->first('savedSearchName') }}
                    </small>
                @endif
            </div>
        </x-l-tables::support.modal>
    </div>

    <div class="lt-overflow-hidden lt-border lt-border-gray-200 lt-rounded-lg">
        <div x-data="{
            showFilters: false,
        }" class="lt-w-full lt-divide-y lt-divide-gray-200">
            @if ($this->searchable || $this->filterable)
                <div class="lt-p-4 lt-bg-gray-100">
                    <div class="lt-flex lt-items-center lt-gap-2 sm:lt-gap-4">
                        @if ($this->searchable)
                            <div class="lt-flex-1">
                                <div class="lt-relative">
                                    <label for="Search"
                                           class="lt-absolute lt-inset-y-0 lt-left-0 lt-grid lt-w-10 lt-place-content-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="1.5"
                                             stroke="currentColor"
                                             class="lt-w-4 lt-h-4">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                        </svg>

                                        <span class="lt-sr-only">
                                            Search
                                        </span>
                                    </label>


                                    <input type="text"
                                           id="Search"
                                           placeholder="{{ $this->searchPlaceholder }}"
                                           wire:model.debounce.500ms="query"
                                           class="lt-w-full lt-pl-10 lt-text-sm lt-text-gray-700 lt-border-gray-200 lt-rounded-md lt-form-input focus:lt-outline-none focus:lt-ring focus:lt-ring-sky-100 focus:lt-border-sky-300 lt-peer">

                                    <button wire:click="$set('query', '')"
                                            class="lt-absolute lt-top-1/2 -lt-translate-y-1/2 lt-right-2 lt-rounded-full lt-p-1 hover:lt-bg-gray-100 lt-transition peer-placeholder-shown:lt-hidden">
                                        <span class="lt-sr-only">Clear</span>

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="1.5"
                                             stroke="currentColor"
                                             class="lt-w-4 lt-h-4">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M6 18L18 6M6 6l12 12" />
                                        </svg>

                                    </button>

                                </div>
                            </div>
                        @endif

                        @if ($this->canSaveSearches && $this->hasSearchApplied)
                            <x-l-tables::button x-on:click="savingSearch = true">
                                Save Search
                            </x-l-tables::button>
                        @endif

                        @if (count($this->tableFilters) && $this->filterable)
                            <x-l-tables::button x-on:click="showFilters = !showFilters">
                                Filters

                                @if ($this->activeFiltersCount)
                                    <sup class="lt-top-0">
                                        ({{ $this->activeFiltersCount }})
                                    </sup>
                                @endif
                            </x-l-tables::button>
                        @endif
                    </div>

                    @if (count($this->savedSearches) && $this->canSaveSearches)
                        <div class="lt-flex lt-items-center lt-gap-4 lt-mt-2">
                            @foreach ($this->savedSearches as $savedSearch)
                                <div
                                     class="lt-flex lt-items-stretch lt-overflow-hidden lt-text-gray-600 lt-transition lt-bg-white lt-border lt-border-gray-200 lt-rounded-md hover:lt-shadow-sm focus-within:lt-ring focus-within:lt-ring-sky-100">
                                    <x-l-tables::button size="xs"
                                                        aria-label="Delete Saved Search"
                                                        wire:click="deleteSavedSearch({{ $savedSearch['key'] }})"
                                                        class="!lt-border-0 !lt-rounded-r-none focus:!lt-ring-transparent focus:lt-bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="1.5"
                                             stroke="currentColor"
                                             class="lt-w-3 lt-h-3">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </x-l-tables::button>

                                    <x-l-tables::button size="xs"
                                                        aria-label="Apply Saved Search"
                                                        wire:click="applySavedSearch({{ $savedSearch['key'] }})"
                                                        class="!lt-border-y-0 !lt-border-r-0 !lt-rounded-l-none focus:!lt-ring-transparent focus:lt-bg-gray-50">
                                        <span @class([
                                            'lt-inline-flex lt-items-center lt-gap-2',
                                            'lt-text-sky-600' => $this->savedSearch == $savedSearch['key'],
                                        ])>
                                            {{ $savedSearch['label'] }}

                                            @if ($this->savedSearch == $savedSearch['key'])
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     viewBox="0 0 20 20"
                                                     fill="currentColor"
                                                     class="lt-w-3 lt-h-3">
                                                    <path fill-rule="evenodd"
                                                          d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                                          clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </span>
                                    </x-l-tables::button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if ($this->filterable)
                <div x-cloak
                     x-show="showFilters || selectedRows.length"
                     class="lt-p-4 lt-bg-white">
                    <div class="lt-flow-root">
                        <div class="lt--my-4 lt-divide-y lt-divide-gray-100">
                            <div :hidden="!selectedRows.length"
                                 class="py-4">
                                <p class="lt-text-sm lt-font-medium lt-text-gray-900">
                                    Bulk Actions
                                </p>

                                <div class="lt-flex lt-flex-wrap lt-gap-4 lt-mt-2" wire:ignore>
                                    @foreach ($this->bulkActions as $action)
                                        @livewire($action->getName(), [
                                            'label' => $action->label,
                                            'livewire' => $action->getLivewire(),
                                        ])
                                    @endforeach
                                </div>
                            </div>

                            <div :hidden="!showFilters"
                                 class="lt-py-4">
                                <p class="lt-sr-only">
                                    Filters
                                </p>

                                <div class="lt-flex lt-flex-wrap lt-gap-4">
                                    @foreach ($this->tableFilters as $filter)
                                        <div>{{ $filter }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($poll)
                <div wire:poll.{{ $poll }}></div>
            @endif

            <div class="lt-overflow-x-auto">
                @if (count($this->rows))
                    <table class="lt-min-w-full lt-divide-y lt-divide-gray-200">
                        <thead class="lt-bg-white">
                            <tr>
                                @if (count($this->bulkActions))
                                    <td class="lt-w-10 lt-py-3 lt-pl-4 lt-leading-none">
                                        <input type="checkbox"
                                               x-model="selectedAll"
                                               class="lt-w-5 lt-h-5 lt-border lt-border-gray-300 lt-rounded-md lt-form-checkbox focus:lt-outline-none focus:lt-ring focus:lt-ring-sky-100 focus:lt-border-sky-300 focus:lt-ring-offset-0">
                                    </td>
                                @endif

                                @foreach ($this->columns as $column)
                                    @livewire(
                                        'lunar.livewire-tables.components.head',
                                        [
                                            'heading' => $column->getHeading(),
                                            'sortable' => $column->isSortable(),
                                            'field' => $column->field,
                                            'sortField' => $sortField,
                                            'sortDir' => $sortDir,
                                        ],
                                        key($column->field),
                                    )
                                @endforeach

                                @if (count($this->actions))
                                    <td></td>
                                @endif
                            </tr>

                            <tr x-cloak
                                x-show="selectedRows.length">
                                <td colspan="50"
                                    class="lt-p-0">
                                    <div
                                         class="lt-relative lt-px-3 lt-py-2 lt--my-px lt-text-sm lt-text-sky-700 lt-border-sky-200 lt-border-y lt-bg-sky-50">
                                        Selected <span x-text="selectedRows.length"></span> of {{ $this->rows->count() }}
                                        results.
                                    </div>
                                </td>
                            </tr>
                        </thead>

                        <tbody class="lt-relative">
                            @foreach ($this->rows as $row)
                                <tr class="lt-bg-white even:lt-bg-gray-50"
                                    wire:key="table_row_{{ $row->id }}">
                                    @if ($this->bulkActions->count())
                                        <x-l-tables::cell class="lt-w-10 lt-pr-0 lt-leading-none">
                                            <input type="checkbox"
                                                   x-model="selectedRows"
                                                   value="{{ $row->id }}"
                                                   class="lt-w-5 lt-h-5 lt-border lt-border-gray-300 lt-rounded-md lt-form-checkbox focus:lt-outline-none focus:lt-ring focus:lt-ring-sky-100 focus:lt-border-sky-300 focus:lt-ring-offset-0">
                                        </x-l-tables::cell>
                                    @endif

                                    @foreach ($this->columns as $column)
                                        <x-l-tables::cell :sort="true"
                                                          wire:key="column_{{ $column->field }}_{{ $row->id }}">
                                            @if ($column->isLivewire())
                                                <livewire:is :component="$column->getLivewire()" />
                                            @elseif($column->isViewComponent())
                                                <x-dynamic-component :component="$column->getViewComponent()"
                                                                     :record="$row" />
                                            @else
                                                {{ $column->record($row)->render() }}
                                            @endif
                                        </x-l-tables::cell>
                                    @endforeach

                                    @if (count($this->actions))
                                        <x-l-tables::cell class="lt-text-right">
                                            <x-l-tables::action-cell :actions="$this->actions"
                                                                     :record="$row" />
                                        </x-l-tables::cell>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-l-tables::support.no-entries :message="$this->emptyMessage" />
                @endif
            </div>
        </div>
    </div>

    @if ($hasPagination)
        <div class="lt-mt-4 lt-pagination">
            {{ $this->rows->links() }}
        </div>
    @endif
</div>
