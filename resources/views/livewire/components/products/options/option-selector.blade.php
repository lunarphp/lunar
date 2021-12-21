<div>
  <x-hub::slideover :title="__('adminhub::components.products.product-selector.title')" wire:model="mainPanelVisible">
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
                'bg-indigo-100 text-indigo-700': tab == 'search',
                'text-gray-500 hover:text-gray-700': tab != 'search'
              }"
            >

              {{ __('adminhub::components.products.product-selector.available_tab') }}
            </button>

            <button
              class="px-3 py-2 text-sm font-medium rounded-md"
              @click.prevent="tab = 'selected'"
              :class="{
                'bg-indigo-100 text-indigo-700': tab == 'selected',
                'text-gray-500 hover:text-gray-700': tab != 'selected'
              }"
            >
              {{ __('adminhub::components.products.product-selector.selected_tab') }} ({{ $this->selectedModels->count() }})
            </button>
          </nav>
        </div>

        <div class="space-y-4" x-show="tab == 'search'">
          <div>
            <x-hub::input.text wire:model.debounce.300ms="searchTerm" />
          </div>
          <div class="space-y-2">
            @forelse($this->options as $option)
              <div class="flex items-center justify-between w-full px-2 py-2 text-sm text-left border rounded shadow-sm">
                  <div class="truncate">{{ $option->translate('name') }}</div>

                    @if(collect($this->selected)->contains($option->id))
                      <button
                        class="px-2 py-1 text-xs text-red-700 border border-red-200 rounded shadow-sm hover:bg-red-50"
                        wire:click.prevent="deselect('{{ $option->id }}')"
                      >
                        {{ __('adminhub::global.deselect') }}
                      </button>
                    @else
                      <button
                        class="px-2 py-1 text-xs text-blue-700 border border-blue-200 rounded shadow-sm hover:bg-blue-50"
                        wire:click.prevent="select('{{ $option->id }}')"
                      >
                        {{ __('adminhub::global.select') }}
                      </button>
                    @endif
                </div>
              @empty
                  {{ __('adminhub::components.products.product-selector.no_results') }}
              @endforelse
          </div>
        </div>

        <div class="space-y-4" x-show="tab == 'selected'">
          @forelse($this->selectedModels as $option)
            <div
              class="flex items-center justify-between w-full px-2 py-2 text-sm text-left border rounded shadow-sm "
              wire:key="selected_{{ $option->id }}"
            >
              {{ $option->translate('name') }}
              <button
                class="px-2 py-1 text-xs text-red-700 border border-red-200 rounded shadow-sm hover:bg-red-50"
                wire:click.prevent="deselect('{{ $option->id }}')"
              >
                {{ __('adminhub::global.deselect') }}
              </button>
          </div>
          @empty
            <div class="px-3 py-2 mt-4 text-sm text-gray-500 bg-gray-100 rounded">
              {{ __('adminhub::components.products.product-selector.selected_empty') }}
            </div>
          @endforelse
        </div>
      </div>

      <x-slot name="footer">
        <x-hub::button type="button" wire:click.prevent="$set('createPanelVisible', true)" theme="green">
          {{ __('adminhub::components.products.product-selector.add_new_btn') }}
        </x-hub::button>
        <x-hub::button type="submit" wire:click.prevent="submitOptions" :disabled="!$this->selectedModels->count()">
          {{ __('adminhub::components.products.product-selector.use_selected_btn') }}
        </x-hub::button>
      </x-slot>
      <x-hub::slideover wire:model="createPanelVisible" nested :title="__('adminhub::components.products.option-creator.title')">
        @livewire('hub.components.products.options.option-creator')
      </x-hub::slideover>
  </x-hub::slideover>
</div>
