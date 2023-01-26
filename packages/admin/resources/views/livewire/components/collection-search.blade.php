<div>
    <x-hub::button type="button"
                   wire:click.prevent="$set('showBrowser', true)">
        {{ __('adminhub::components.collection-search.btn') }}
    </x-hub::button>

    <x-hub::slideover :title="__('adminhub::components.collection-search.title')"
                      wire:model="showBrowser">
        <div class="space-y-4"
             x-data="{ tab: 'search' }">
            <div>
                <nav class="flex space-x-4"
                     aria-label="Tabs">
                    <button x-on:click.prevent="tab = 'search'"
                            class="px-3 py-2 text-sm font-medium rounded-md"
                            :class="{
                                'bg-indigo-100 text-indigo-700': tab == 'search',
                                'text-gray-500 hover:text-gray-700': tab != 'search'
                            }">
                        {{ __('adminhub::components.collection-search.first_tab') }}
                    </button>

                    <button class="px-3 py-2 text-sm font-medium rounded-md"
                            @click.prevent="tab = 'selected'"
                            :class="{
                                'bg-indigo-100 text-indigo-700': tab == 'selected',
                                'text-gray-500 hover:text-gray-700': tab != 'selected'
                            }">
                        {{ __('adminhub::components.collection-search.second_tab') }}

                        ({{ $this->selectedModels->count() }})
                    </button>
                </nav>
            </div>

            <div x-show="tab == 'search'">
                <x-hub::input.text wire:model.debounce.300ms="searchTerm" />

                @if ($this->searchTerm)
                    @if ($this->results->total() > $maxResults)
                        <span class="block p-3 my-2 text-xs text-blue-600 rounded bg-blue-50">
                            {{ __('adminhub::components.collection-search.max_results_exceeded', [
                                'max' => $maxResults,
                                'total' => $this->results->total(),
                            ]) }}
                        </span>
                    @endif
                    <div class="mt-4 space-y-1">
                        @forelse($this->results as $collection)
                            <div @class([
                                'flex w-full items-center justify-between rounded shadow-sm text-left border px-2 py-2 text-sm',
                                'opacity-25' => $this->existingIds->contains($collection->id),
                            ])>
                                <div class="truncate max-w-64">
                                    <strong class="rounded px-1.5 py-0.5 mr-1 bg-blue-50 text-xs text-blue-600">
                                        {{ $collection->group->name }}
                                    </strong>

                                    {{ $collection->translateAttribute('name') }} {{ $collection->deleted_at }}

                                    <p class="pr-32 mt-0.5 text-xs text-gray-400 truncate">
                                        {{ collect($collection['breadcrumb'])->implode(' > ') }}
                                    </p>
                                </div>

                                @if (!$this->existingIds->contains($collection->id))
                                    @if (collect($this->selected)->contains($collection->id))
                                        <button class="px-2 py-1 text-xs text-red-700 border border-red-200 rounded shadow-sm hover:bg-red-50"
                                                wire:click.prevent="removeCollection('{{ $collection->id }}')">
                                            {{ __('adminhub::global.deselect') }}
                                        </button>
                                    @else
                                        <button class="px-2 py-1 text-xs text-blue-700 border border-blue-200 rounded shadow-sm hover:bg-blue-50"
                                                wire:click.prevent="selectCollection('{{ $collection->id }}')">
                                            {{ __('adminhub::global.select') }}
                                        </button>
                                    @endif
                                @else
                                    <span class="text-xs">
                                        {{ __('adminhub::components.collection-search.exists_in_collection') }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            {{ __('adminhub::components.collection-search.no_results') }}
                        @endforelse
                    </div>
                @else
                    <div class="px-3 py-2 mt-4 text-sm text-gray-500 bg-gray-100 rounded">
                        {{ __('adminhub::components.collection-search.pre_search_message') }}
                    </div>
                @endif
            </div>

            <div x-show="tab == 'selected'"
                 class="space-y-2">
                @forelse($this->selectedModels as $collection)
                    <div class="flex items-center justify-between w-full px-2 py-2 text-sm text-left border rounded shadow-sm "
                         wire:key="selected_{{ $collection->id }}">
                        <div class="truncate max-w-64">
                            <strong class="rounded px-1.5 py-0.5 mr-1 bg-blue-50 text-xs text-blue-600">
                                {{ $collection->group->name }}
                            </strong>

                            {{ $collection->translateAttribute('name') }}

                            <p class="pr-32 mt-0.5 text-xs text-gray-400 truncate">
                                {{ collect($collection['breadcrumb'])->implode(' > ') }}
                            </p>
                        </div>

                        <button class="px-2 py-1 text-xs text-red-700 border border-red-200 rounded shadow-sm hover:bg-red-50"
                                wire:click.prevent="removeCollection('{{ $collection->id }}')">
                            {{ __('adminhub::global.deselect') }}
                        </button>
                    </div>
                @empty
                    <div class="px-3 py-2 mt-4 text-sm text-gray-500 bg-gray-100 rounded">
                        {{ __('adminhub::components.collection-search.select_empty') }}
                    </div>
                @endforelse
            </div>
        </div>

        <x-slot name="footer">
            <x-hub::button wire:click.prevent="triggerSelect">
                {{ __('adminhub::components.collection-search.commit_btn') }}
            </x-hub::button>
        </x-slot>
    </x-hub::slideover>
</div>
