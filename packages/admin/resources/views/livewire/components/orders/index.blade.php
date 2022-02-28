<div class="flex-col px-12 space-y-4">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">
      {{ __('adminhub::orders.index.title') }}
    </strong>
  </div>

  <div class="mt-4">
    <button type="button" wire:click="export" class="flex items-center px-2 py-1 text-xs text-gray-600 border rounded hover:bg-gray-50">
      <x-hub::icon ref="download" style="solid" class="w-3 mr-1" />
      {{ __('adminhub::orders.index.export_btn') }}
    </button>

    <button type="button" wire:click="export" class="flex items-center px-2 py-1 text-xs text-gray-600 border rounded hover:bg-gray-50">
      <x-hub::icon ref="download" style="solid" class="w-3 mr-1" />
      {{ __('adminhub::orders.index.update_statuses_btn') }}
    </button>
  </div>

<x-hub::modal.dialog form="saveSearch" wire:model="showSaveSearch">
    <x-slot name="title">
      {{ __('adminhub::orders.index.save_search.title') }}
    </x-slot>
    <x-slot name="content">
      <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" required :error="$errors->first('savedSearch.name')">
        <x-hub::input.text wire:model.defer="savedSearch.name" :error="$errors->first('savedSearch.name')" required/>
      </x-hub::input.group>
    </x-slot>
    <x-slot name="footer">
      <x-hub::button type="button" wire:click.prevent="$set('showSaveSearch', false)" theme="gray">{{ __('adminhub::global.cancel') }}</x-hub::button>
      <x-hub::button type="submit">
        {{ __('adminhub::orders.index.save_search.btn') }}
      </x-hub::button>
    </x-slot>
  </x-hub::modal.dialog>



  <div class="space-y-4">
    <div class="hidden sm:block">
      <nav class="flex space-x-4" aria-label="Tabs">
        <!-- Current: "bg-gray-200 text-gray-800", Default: "text-gray-600 hover:text-gray-800" -->
        <button
          type="button"
          wire:click.prevent="resetSearch"
          class="
            text-sm font-medium px-3
            @if(!$this->activeSavedSearch && !$this->hasCustomFilters)
              text-blue-600
            @else
              text-gray-500 hover:text-gray-700
            @endif"
        >
          {{ __('adminhub::orders.index.all_orders') }}
        </button>

        @foreach($this->savedSearches as $savedSearch)
          <div class="flex" wire:key="saved_search_{{ $savedSearch->id }}">
              <button
                type="button"
                wire:click.prevent="applySavedSearch({{ $savedSearch->id }})"
                class="
                  text-sm font-medium px-3
                  @if($this->activeSavedSearch && $this->activeSavedSearch->id == $savedSearch->id)
                    text-blue-600
                  @else
                    text-gray-500 hover:text-gray-700
                  @endif"
              >
                {{ $savedSearch->name }}
              </button>
              <button class="text-gray-400 hover:text-red-500" type="button" wire:click.prevent="deleteSavedSearch({{ $savedSearch->id }})">
                <x-hub::icon ref="x" style="solid" class="w-3" />
              </button>
          </div>
        @endforeach
      </nav>
    </div>
    <x-hub::table>
      <x-slot name="toolbar">
        <div class="p-4 space-y-4 border-b" x-data="{ filtersVisible: false }">
          <div class="flex items-center space-x-4">
            <div class="flex items-center w-full space-x-4">
              <div class="grow">
                <x-hub::input.text :placeholder="__('adminhub::orders.index.search_placeholder')" class="py-2" wire:model.debounce.400ms="search" />
              </div>

              <div class="flex items-center justify-end space-x-4">
                <x-hub::button theme="gray" class="relative inline-flex items-center" @click.prevent="filtersVisible = !filtersVisible">
                  <x-hub::icon ref="filter" style="solid"  class="w-4 mr-1" />
                  {{ __('adminhub::global.filter') }}
                  @if($this->hasCustomFilters)
                    <span class="absolute block w-3 h-3 bg-red-500 rounded-full -right-1 -top-1"></span>
                  @endif
                </x-hub::button>

                @if($this->hasCustomFilters)
                  <x-hub::button theme="gray" class="inline-flex items-center" type="button" wire:click.prevent="resetSearch">
                    <x-hub::icon ref="trash" style="solid" class="w-4 mr-1" />
                    {{ __('adminhub::global.clear') }}
                  </x-hub::button>

                  @if(!$this->activeSavedSearch)
                    <x-hub::button wire:click.prevent="$set('showSaveSearch', true)" class="inline-flex items-center">
                      <x-hub::icon ref="bookmark" style="solid" class="w-4 mr-1" />
                      {{ __('adminhub::global.save') }}
                    </x-hub::button>
                  @endif
                @endif
              </div>
            </div>
          </div>

          <div class="grid grid-cols-4 gap-4" x-show="filtersVisible" x-cloak>
            <x-hub::input.group :label="__('adminhub::inputs.status.label')" for="status">
              <x-hub::input.select wire:model="filters.status">
                <option value>{{ __('adminhub::global.any') }}</option>
                @foreach($this->orders->facets->get('status') as $facet)
                  <option value="{{ $facet->value }}">{{ $facet->value }}</option>
                @endforeach
              </x-hub::input.select>
            </x-hub::input.group>

            @foreach($this->availableFilters as $filter)
              <x-hub::input.group :label="$filter->heading" for="{{ $filter->field }}">
                <x-hub::input.select wire:model="filters.{{ $filter->field }}">
                  <option value>{{ __('adminhub::global.any') }}</option>
                  @foreach($this->orders->facets->get($filter->field) as $facet)
                    <option value="{{ $facet->value }}">{{ $filter->format($facet->value) }}</option>
                  @endforeach
                </x-hub::input.select>
              </x-hub::input.group>
            @endforeach

            <x-hub::input.group :label="__('adminhub::inputs.from_date.label')" for="from_date">
              <x-hub::input.datepicker wire:model="filters.from" />
            </x-hub::input.group>

            <x-hub::input.group :label="__('adminhub::inputs.to_date.label')" for="to_date">
              <x-hub::input.datepicker wire:model="filters.to" />
            </x-hub::input.group>

          </div>
        </div>
      </x-slot>
      <x-slot name="head">
        <x-hub::table.heading>
          <x-hub::input.checkbox wire:model="selectAll" />
        </x-hub::table.heading>
        <x-hub::table.heading>
          {{ __('adminhub::global.status') }}
        </x-hub::table.heading>
        <x-hub::table.heading>
          {{ __('adminhub::global.reference') }}
        </x-hub::table.heading>
        <x-hub::table.heading>
          {{ __('adminhub::global.customer') }}
        </x-hub::table.heading>
        <x-hub::table.heading>
          {{ __('adminhub::global.total') }}
        </x-hub::table.heading>
        @foreach($this->columns as $column)
          <x-hub::table.heading>
            {{ $column->heading }}
          </x-hub::table.heading>
        @endforeach
        <x-hub::table.heading>
          {{ __('adminhub::global.date') }}
        </x-hub::table.heading>
        <x-hub::table.heading>
          {{ __('adminhub::global.time') }}
        </x-hub::table.heading>
        <x-hub::table.heading></x-hub::table.heading>
      </x-slot>
      <x-slot name="body">
        @forelse($this->orders->items as $order)
          <x-hub::table.row wire:key="row-{{ $order->id }}" :selected="in_array($order->id, $selected)">
            <x-hub::table.cell>
              <x-hub::input.checkbox wire:model.defer="selected" value="{{ $order->id }}" />
            </x-hub::table.cell>
            <x-hub::table.cell>
              <x-hub::orders.status :status="$order->status" />
            </x-hub::table.cell>
            <x-hub::table.cell>
              {{ $order->reference }}
            </x-hub::table.cell>
            <x-hub::table.cell>
              {{ $order->billingAddress->first_name }}
            </x-hub::table.cell>
            <x-hub::table.cell>
              {{ $order->total->formatted() }}
            </x-hub::table.cell>
            @foreach($this->columns as $column)
              <x-hub::table.cell>
                @if($column->callback)
                  {{ $column->getValue($order) }}
                @endif
              </x-hub::table.cell>
            @endforeach
            <x-hub::table.cell>
              @if($order->placed_at)
                {{ $order->placed_at->format('jS M Y') }}
              @else
                {{ $order->created_at->format('jS M Y') }}
              @endif
            </x-hub::table.cell>
            <x-hub::table.cell>
              @if($order->placed_at)
                {{ $order->placed_at->format('h:ma') }}
              @else
                {{ $order->created_at->format('h:ma') }}
              @endif
            </x-hub::table.cell>
            <x-hub::table.cell>
              <a href="{{ route('hub.orders.show', $order->id) }}" class="text-indigo-500 hover:underline">View</a>
            </x-hub::table.cell>
          </x-hub::table.row>
        @empty

        @endforelse
      </x-slot>
    </x-hub::table>
    <div>
      {{ $this->orders->items->links() }}
    </div>
  </div>
</div>
