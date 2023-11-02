<div>
  @if($showBtn)
  <x-hub::button type="button" wire:click.prevent="$set('showBrowser', true)">
    @if(!empty($label))
      {{ $label }}
    @else
      {{ __('adminhub::components.product-variant-search.btn') }}
    @endif
  </x-hub::button>
  @endif

  <x-hub::slideover :title="__('adminhub::components.product-variant-search.title')" wire:model="showBrowser">
    <div
      class="space-y-4"
      x-data="{
        tab: 'search'
      }"
    >
      <div>
        <nav class="flex space-x-4" aria-label="Tabs">
          <button
            x-on:click.prevent="tab = 'search'"
            class="px-3 py-2 text-sm font-medium rounded-md"
            :class="{
              'bg-sky-100 text-sky-700': tab == 'search',
              'text-gray-500 hover:text-gray-700': tab != 'search'
            }"
          >
            {{ __('adminhub::components.product-variant-search.first_tab') }}
          </button>

          <button
            class="px-3 py-2 text-sm font-medium rounded-md"
            @click.prevent="tab = 'selected'"
            :class="{
              'bg-sky-100 text-sky-700': tab == 'selected',
              'text-gray-500 hover:text-gray-700': tab != 'selected'
            }"
          >
            {{ __('adminhub::components.product-variant-search.second_tab') }} ({{ $this->selectedModels->count() }})
          </button>
        </nav>
      </div>

      <div x-show="tab == 'search'">
        <x-hub::input.text wire:model.debounce.300ms="searchTerm" />
        @if($this->searchTerm)
          @if($this->results->total() > $maxResults)
            <span class="block p-3 my-2 text-xs text-sky-600 rounded bg-sky-50">
              {{ __('adminhub::components.product-variant-search.max_results_exceeded', [
                'max' => $maxResults,
                'total' => $this->results->total()
              ]) }}
            </span>
          @endif
          <div class="mt-4 space-y-1">
            @forelse($this->results as $variant)
              <div
                class="
                  flex w-full items-center justify-between rounded shadow-sm text-left border px-2 py-2 text-sm
                  @if($this->existingIds->contains($variant->id) || collect($this->exclude)->contains($variant->id))
                    opacity-25
                  @endif
                "
              >
                <div class="truncate">{{ $variant->sku }} {{ $variant->deleted_at }}</div>
                @if(!$this->existingIds->contains($variant->id) && !collect($this->exclude)->contains($variant->id))
                  @if(collect($this->selected)->contains($variant->id))
                    <button
                      class="px-2 py-1 text-xs text-red-700 border border-red-200 rounded shadow-sm hover:bg-red-50"
                      wire:click.prevent="removeProductVariant('{{ $variant->id }}')"
                    >
                      {{ __('adminhub::global.deselect') }}
                    </button>
                  @elseif (!collect($this->exclude)->contains($variant->id))
                    <button
                      class="px-2 py-1 text-xs text-sky-700 border border-sky-200 rounded shadow-sm hover:bg-sky-50"
                      wire:click.prevent="selectProductVariant('{{ $variant->id }}')"
                    >
                      {{ __('adminhub::global.select') }}
                    </button>
                  @endif
                @elseif(collect($this->exclude)->contains($variant->id))
                  <span class="text-xs">
                    {{ __('adminhub::components.product-variant-search.associate_self') }}
                  </span>
                @else
                  <span class="text-xs">
                    {{ __('adminhub::components.product-variant-search.exists_in_collection') }}
                  </span>
                @endif
              </div>
            @empty
                {{ __('adminhub::components.product-variant-search.no_results') }}
            @endforelse
          </div>
        @else
          <div class="px-3 py-2 mt-4 text-sm text-gray-500 bg-gray-100 rounded">
            {{ __('adminhub::components.product-variant-search.pre_search_message') }}
          </div>
        @endif
      </div>

      <div x-show="tab == 'selected'" class="space-y-2">
        @forelse($this->selectedModels as $variant)
          <div
            class="flex items-center justify-between w-full px-2 py-2 text-sm text-left border rounded shadow-sm "
            wire:key="selected_{{ $variant->id }}"
          >
            {{ $variant->sku }}
            <button
              class="px-2 py-1 text-xs text-red-700 border border-red-200 rounded shadow-sm hover:bg-red-50"
              wire:click.prevent="removeProductVariant('{{ $variant->id }}')"
            >
              {{ __('adminhub::global.deselect') }}
            </button>
        </div>
        @empty
          <div class="px-3 py-2 mt-4 text-sm text-gray-500 bg-gray-100 rounded">
            {{ __('adminhub::components.product-variant-search.select_empty') }}
          </div>
        @endforelse
      </div>
    </div>

    <x-slot name="footer">
      <x-hub::button wire:click.prevent="triggerSelect">
        {{ __('adminhub::components.product-variant-search.commit_btn') }}
      </x-hub::button>
    </x-slot>
  </x-hub::slideover>
</div>
