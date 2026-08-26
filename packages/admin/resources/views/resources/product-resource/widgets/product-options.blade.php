<x-filament-widgets::widget>

  @if(!$this->configuringOptions)
    <div class="space-y-4">
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-ta-header flex flex-col gap-3 p-4 sm:px-6 sm:flex-row sm:items-center">
          <div class="grid gap-y-1">
            <h3 class="fi-ta-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
              {{ __('lunarpanel::productoption.widgets.product-options.options-table.title') }}
            </h3>
          </div>
          <div class="fi-ta-actions flex shrink-0 items-center gap-3 flex-wrap justify-start sm:ms-auto">
            <x-filament::button type="button" wire:click="$set('configuringOptions', true)">
              {{ __('lunarpanel::productoption.widgets.product-options.options-table.configure-options.label') }}
            </x-filament::button>
          </div>
        </div>
        <div class="fi-ta-content divide-gray-200 overflow-x-auto">
          @if(count($this->configuredOptions))
            <table class="fi-ta-table">
              <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                  <th class="fi-ta-header-cell">
                      <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('lunarpanel::productoption.widgets.product-options.options-table.table.option.label') }}
                      </span>
                  </th>
                  <th class="fi-ta-header-cell">
                      <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('lunarpanel::productoption.widgets.product-options.options-table.table.values.label') }}
                      </span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
              @foreach($this->configuredOptions as $option)
                <tr class="fi-ta-row">
                  <td class="fi-ta-cell">
                    <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                      <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                        {{ $option['value'] }}
                      </span>
                    </div>
                  </td>
                  <td class="fi-ta-cell">
                    <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                      <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                      {{ collect($option['option_values'])
                        ->filter(
                            fn ($value) => $value['enabled']
                        )->map(
                          fn ($value) => $value['value']
                      )->join(', ') }}
                      </span>
                    </div>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <div class="fi-ta-empty-state">
              <div class="fi-ta-empty-state-content">
                <div class="fi-ta-empty-state-icon-bg">
                  {{ \Filament\Support\generate_icon_html('lucide-shapes', size: \Filament\Support\Enums\IconSize::Large) }}
                </div>
                <h4 class="fi-ta-empty-state-heading">{{ __('lunarpanel::productoption.widgets.product-options.options-list.empty.heading') }}</h4>
              </div>
            </div>
          @endif
        </div>
      </div>

      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-ta-header flex flex-col gap-3 p-4 sm:px-6 sm:flex-row sm:items-center">
          <div class="grid gap-y-1">
            <h3 class="fi-ta-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
              {{ __('lunarpanel::productoption.widgets.product-options.variants-table.title') }}
            </h3>
          </div>
        </div>
        <div class="fi-ta-content divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
          @if(count($this->variants))
              <table class="fi-ta-table">
                <thead class="divide-y divide-gray-200 dark:divide-white/5">
                  <tr class="bg-gray-50 dark:bg-white/5">
                    @if($this->hasNewVariants)
                      <th class="fi-ta-header-cell">
                      </th>
                    @endif
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                      <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('lunarpanel::productoption.widgets.product-options.variants-table.table.option.label') }}
                      </span>
                    </th>
                    <th class="fi-ta-header-cell">
                      <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('lunarpanel::productoption.widgets.product-options.variants-table.table.sku.label') }}
                      </span>
                    </th>
                    <th class="fi-ta-header-cell">
                      <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('lunarpanel::productoption.widgets.product-options.variants-table.table.price.label') }}
                      </span>
                    </th>
                    <th class="fi-ta-header-cell">
                      <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('lunarpanel::productoption.widgets.product-options.variants-table.table.stock.label') }}
                      </span>
                    </th>
                    <th class="fi-ta-header-cell">
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">

                @foreach($this->variants as $permutationIndex => $permutation)
                  <tr class="fi-ta-row" wire:key="permutation_{{ $permutation['key'] }}">
                    @if($this->hasNewVariants)
                      <td class="fi-ta-cell">
                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                          @if(!$permutation['variant_id'])
                            <x-filament::badge color="info">
                              {{ __('lunarpanel::productoption.widgets.product-options.variants-table.table.new.label') }}
                            </x-filament::badge>
                          @endif
                        </div>
                      </td>
                    @endif
                    <td class="fi-ta-cell">
                      <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                        <span class="fi-ta-text-item-label flex flex-col text-sm leading-6 text-gray-950 dark:text-white">
                          @foreach($permutation['values'] as $option => $value)
                            <small><strong>{{ $option }}:</strong> {{ $value }}</small>
                          @endforeach
                        </span>
                      </div>
                    </td>
                    <td class="fi-ta-cell">
                      <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                        <x-filament::input.wrapper>
                          <x-filament::input
                                  type="text"
                                  wire:model="variants.{{ $permutationIndex }}.sku"
                          />
                        </x-filament::input.wrapper>
                      </div>
                    </td>
                    <td class="fi-ta-cell w-32">
                      <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                        <x-filament::input.wrapper>
                          <x-filament::input
                                  type="text"
                                  wire:model="variants.{{ $permutationIndex }}.price"
                          />
                        </x-filament::input.wrapper>
                      </div>
                    </td>
                    <td class="fi-ta-cell w-32">
                      <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                        <x-filament::input.wrapper>
                          <x-filament::input
                                  type="text"
                                  wire:model="variants.{{ $permutationIndex }}.stock"
                          />
                        </x-filament::input.wrapper>
                      </div>
                    </td>
                    <td class="fi-ta-cell">
                      <div class="flex items-center space-x-2">
                        @if($permutation['variant_id'])
                          <x-filament::link :href="$this->getVariantLink($permutation['variant_id'])">
                            {{ __('lunarpanel::productoption.widgets.product-options.variants-table.actions.edit.label') }}
                          </x-filament::link>
                        @endif
                        <button type="button" wire:click="removeVariant('{{ $permutationIndex }}')" class="text-red-500 font-semibold text-sm hover:underline">
                          {{ __('lunarpanel::productoption.widgets.product-options.variants-table.actions.delete.label') }}
                        </button>
                      </div>
                    </td>

                  </tr>
                @endforeach
                </tbody>
              </table>
            @else
              <div class="fi-ta-empty-state">
                <div class="fi-ta-empty-state-content">
                  <div class="fi-ta-empty-state-icon-bg">
                    {{ \Filament\Support\generate_icon_html('lucide-shapes', size: \Filament\Support\Enums\IconSize::Large) }}
                  </div>
                  <h4 class="fi-ta-empty-state-heading">{{ __('lunarpanel::productoption.widgets.product-options.variants-table.empty.heading') }}</h4>
                </div>
              </div>
            @endif
        </div>
      </div>
    </div>

    <div class="mt-4 flex">
      {{ $this->saveVariantsAction }}
    </div>

  @else
    <div class="space-y-4">
      <div class="text-right">
        <div class="flex space-x-2 items-end justify-end">
        <x-filament::button color="gray" wire:click="addRestrictedOption">
          {{ __('lunarpanel::productoption.widgets.product-options.actions.add-restricted-option.label') }}
        </x-filament::button>
        {{ $this->addSharedOptionAction }}
        </div>
      </div>
      @if(!count($this->configuredOptions))
        <div wire:key="product_options">
          <div class="fi-ta-empty-state">
            <div class="fi-ta-empty-state-content">
              <div class="fi-ta-empty-state-icon-bg">
                {{ \Filament\Support\generate_icon_html('lucide-shapes', size: \Filament\Support\Enums\IconSize::Large) }}
              </div>
              <h4 class="fi-ta-empty-state-heading">{{ __('lunarpanel::productoption.widgets.product-options.options-list.empty.heading') }}</h4>
              <p class="fi-ta-empty-state-description">{{ __('lunarpanel::productoption.widgets.product-options.options-list.empty.description') }}</p>
            </div>
          </div>
        </div>
      @else
        <div>
          <x-lunarpanel::products.variants.product-options-list
            :items="$configuredOptions"
            group="product_options"
            state-path="configuredOptions"
          />
        </div>
      @endif

      <div class="flex space-x-2 border-t dark:border-white/10 pt-4">
        <x-filament::button type="button" wire:click="updateConfiguredOptions">
          {{ __('lunarpanel::productoption.widgets.product-options.actions.save-options.label') }}
        </x-filament::button>
        <x-filament::button color="gray" wire:click="cancelOptionConfiguring">
          {{ __('lunarpanel::productoption.widgets.product-options.actions.cancel.label') }}
        </x-filament::button>
      </div>
    </div>
    <x-filament-actions::modals />
  @endif
</x-filament-widgets::widget>
