<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header class="flex items-center justify-between">
      <div class="flex">
        <h3 class="mr-4 text-lg font-medium leading-6 text-gray-900">
          {{ __('adminhub::partials.products.associations.heading') }}
        </h3>
        <div class="flex items-center space-x-2 text-xs">
          <span class="@if($showInverseAssociations)text-green-500 @endif">
            {{ __('adminhub::partials.products.associations.show_inverse') }}
          </span>
          <x-hub::input.toggle wire:model="showInverseAssociations" />
        </div>
      </div>
      <div class="flex items-center space-x-2">
          <x-hub::dropdown>
              <x-slot name="value">
                {{ __(
                    $showInverseAssociations ? 'adminhub::partials.products.associations.add_inverse'
                    : 'adminhub::partials.products.associations.add_association')
                }}
              </x-slot>
              <x-slot name="options">
                <x-hub::dropdown.button wire:click.prevent="openAssociationBrowser('alternate')">
                  {{ __('adminhub::partials.products.associations.alternate') }}
                </x-hub::dropdown.button>
                <x-hub::dropdown.button wire:click.prevent="openAssociationBrowser('cross-sell')">
                  {{ __('adminhub::partials.products.associations.cross-sell') }}
                </x-hub::dropdown.button>
                <x-hub::dropdown.button wire:click.prevent="openAssociationBrowser('up-sell')">
                  {{ __('adminhub::partials.products.associations.up-sell') }}
                </x-hub::dropdown.button>
              </x-slot>
            </x-hub::dropdown>
          @livewire('hub.components.product-search', [
            'existing' => $this->associatedProductIds,
            'ref' => 'product-associations',
            'showBtn' => false,
            'exclude' => [$product->id]
          ])
      </div>
    </header>

    <div>
      <div class="lt-overflow-hidden lt-border lt-border-gray-200 lt-rounded-lg">
        <table class="lt-min-w-full lt-divide-y lt-divide-gray-200">
          <thead class="lt-bg-white">
            <th></th>

            <th class="lt-px-4 lt-py-3 lt-text-sm lt-font-medium lt-text-left lt-text-gray-700">
              {{ __('adminhub::global.product') }}
            </th>

            <th class="lt-px-4 lt-py-3 lt-text-sm lt-font-medium lt-text-left lt-text-gray-700">
              {{ __('adminhub::global.type') }}
            </th>

            <th></th>
          </thead>

          <tbody>
            @foreach($associations->filter(fn($product) => $product['inverse'] == $showInverseAssociations) as $index => $product)
              <tr class="lt-bg-white even:lt-bg-gray-50" wire:key="table_row_{{ $product['target_id'] }}">
                <x-l-tables::cell>
                  <img src="{{ $product['thumbnail']}}" class="w-12 rounded">
                </x-l-tables::cell>

                <x-l-tables::cell>
                  <a href="{{ route('hub.products.show', $product['target_id']) }}" class="lt-text-blue-600 hover:underline">
                    {{ $product['name'] }}
                  </a>
                </x-l-tables::cell>

                <x-l-tables::cell>
                  <x-hub::input.select wire:model="associations.{{ $index }}.type">
                    <option value="alternate">
                      {{ __('adminhub::partials.products.associations.alternate') }}
                    </option>
                    <option value="cross-sell">
                      {{ __('adminhub::partials.products.associations.cross-sell') }}
                    </option>
                    <option value="up-sell">
                      {{ __('adminhub::partials.products.associations.up-sell') }}
                    </option>
                  </x-hub::input.select>
                </x-l-tables::cell>

                <x-l-tables::cell>
                  <button type="button" wire:click.prevent="removeAssociation({{ $index }})" class="text-red-500 hover:underline">
                    {{ __('adminhub::global.remove') }}
                  </button>
                </x-l-tables::cell>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
