<x-filament-widgets::widget>

  @if(!$this->configuringOptions)
    {{ $this->table }}

    <pre>
        {{ json_encode($this->variants) }}
    </pre>
  @else
    <x-lunarpanel::products.variants.product-options-list
            :items="$configuredOptions"
            group="product_options"
            state-path="configuredOptions"
    />

  <div class="fi-ta-content divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">

    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
      <thead class="bg-gray-50 dark:bg-white/5">
      <x-filament-tables::header-cell>
      </x-filament-tables::header-cell>
        <x-filament-tables::header-cell>
          Option/Values
        </x-filament-tables::header-cell>
        <x-filament-tables::header-cell>
          SKU
        </x-filament-tables::header-cell>
        <x-filament-tables::header-cell>
          Price
        </x-filament-tables::header-cell>
      </thead>
      <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
      @foreach($this->variantPermutations as $permutation)
        <x-filament-tables::row>
          <x-filament-tables::cell>
            @if(!$permutation['variant_id'])
              NEW
            @endif
          </x-filament-tables::cell>
          <x-filament-tables::cell>
            @foreach($permutation['values'] as $option => $value)
              <strong>{{ $option }}:</strong> {{ $value }}
            @endforeach
          </x-filament-tables::cell>
          <x-filament-tables::cell>
            <x-filament::input.wrapper>
              <x-filament::input
                      type="text"
                      :value="$permutation['sku']"
                      :disabled="$permutation['variant_id']"
              />
            </x-filament::input.wrapper>
          </x-filament-tables::cell>
        </x-filament-tables::row>
      @endforeach
      </tbody>
  </table>

  </div>
    <div class="mt-4">
      <x-filament::button color="gray" wire:click="cancelOptionConfiguring">Cancel</x-filament::button>
      <x-filament::button>Save Options</x-filament::button>
    </div>
  @endif


</x-filament-widgets::widget>
