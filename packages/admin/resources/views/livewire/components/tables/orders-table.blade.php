<div>
    <x-hub::modal.dialog form="deleteSavedSearch"
                     wire:model="savedSearchToDelete">
    <x-slot name="title">
        {{ __('adminhub::orders.index.delete_saved_search.title') }}
    </x-slot>

    <x-slot name="content">
        {{ __('adminhub::orders.index.delete_saved_search.confirm') }}
    </x-slot>

    <x-slot name="footer">
        <x-hub::button type="button"
                       wire:click.prevent="$set('savedSearchToDelete', null)"
                       theme="gray">
            {{ __('adminhub::global.cancel') }}
        </x-hub::button>

        <x-hub::button type="submit"
                       theme="danger">
            {{ __('adminhub::orders.index.delete_saved_search.btn') }}
        </x-hub::button>
    </x-slot>
</x-hub::modal.dialog>

<x-hub::modal.dialog form="saveSearch"
                     wire:model="showSaveSearch">
    <x-slot name="title">
        {{ __('adminhub::orders.index.save_search.title') }}
    </x-slot>

    <x-slot name="content">
        <x-hub::input.group :label="__('adminhub::inputs.name')"
                            for="name"
                            required
                            :error="$errors->first('savedSearch.name')">

            <x-hub::input.text wire:model.defer="savedSearch.name"
                               :error="$errors->first('savedSearch.name')"
                               required
                               autofocus />
        </x-hub::input.group>
    </x-slot>

    <x-slot name="footer">
        <x-hub::button type="button"
                       wire:click.prevent="$set('showSaveSearch', false)"
                       theme="gray">
            {{ __('adminhub::global.cancel') }}
        </x-hub::button>

        <x-hub::button type="submit">
            {{ __('adminhub::orders.index.save_search.btn') }}
        </x-hub::button>
    </x-slot>
</x-hub::modal.dialog>

<div class="space-y-4">
    @if ($this->savedSearches->count())
        <div class="sm:block">
            <nav class="flex pb-4 space-x-4 overflow-x-auto"
                 aria-label="Tabs">
                <button type="button"
                        wire:click.prevent="resetSearch"
                        class="text-sm font-medium px-3 flex-shrink-0 rounded p-2
        @if (!$this->activeSavedSearch && !$this->hasCustomFilters) bg-blue-500 text-white
        @else
          text-gray-500 border hover:bg-white @endif">
                    {{ __('adminhub::orders.index.all_orders') }}
                </button>

                @foreach ($this->savedSearches as $savedSearch)
                    <div class="flex flex-shrink-0 no-wrap"
                         wire:key="saved_search_{{ $savedSearch->id }}">
                        <button type="button"
                                wire:click.prevent="applySavedSearch({{ $savedSearch->id }})"
                                class="
              text-sm font-medium px-3 rounded rounded-r-none
              @if ($this->activeSavedSearch && $this->activeSavedSearch->id == $savedSearch->id) bg-blue-500 text-white
              @else
                text-gray-500 border hover:bg-white @endif">
                            {{ $savedSearch->name }}
                        </button>
                        <button class="px-2 border border-l-0 rounded-r
              @if ($this->activeSavedSearch && $this->activeSavedSearch->id == $savedSearch->id) text-white bg-blue-400 hover:bg-blue-600 hover:text-white border-none
              @else
                text-gray-500 hover:bg-gray-200 @endif"
                                type="button"
                                wire:click.prevent="$set('savedSearchToDelete', {{ $savedSearch->id }})">
                            <x-hub::icon ref="x"
                                         style="solid"
                                         class="w-3" />
                        </button>
                    </div>
                @endforeach
            </nav>
        </div>
    @endif

    @if ($this->hasCustomFilters && !$this->activeSavedSearch)
      <x-hub::button wire:click.prevent="$set('showSaveSearch', true)" class="inline-flex items-center">
        <x-hub::icon ref="bookmark" style="solid" class="w-4 mr-1" />
        {{ __('adminhub::global.save') }}
      </x-hub::button>
    @endif

    {{ $this->table }}
</div>
</div>
