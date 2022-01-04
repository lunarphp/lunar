<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('adminhub::partials.products.variants.heading') }}
        </h3>
        <p class="text-sm text-gray-500">{{ __('adminhub::partials.products.variants.strapline') }}</p>
      </div>
      <div>
        <x-hub::input.toggle wire:model="variantsEnabled" />
      </div>
    </header>
      @if($variantsEnabled)
        @if($this->getVariantsCount() <= 1)
          @include('adminhub::partials.products.editing.options')
        @else
          <x-hub::table>
            <x-slot name="head">
              <x-hub::table.heading>{{ __('adminhub::global.options') }}</x-hub::table.heading>
              <x-hub::table.heading>{{ __('adminhub::global.sku') }}</x-hub::table.heading>
              <x-hub::table.heading>{{ __('adminhub::global.unit_price_tax') }}</x-hub::table.heading>
              <x-hub::table.heading>{{ __('adminhub::global.stock_incoming') }}</x-hub::table.heading>
              <x-hub::table.heading></x-hub::table.heading>
              <x-hub::table.heading></x-hub::table.heading>
            </x-slot>
            <x-slot name="body">
              @foreach($product->variants as $variant)
              <x-hub::table.row>
                <x-hub::table.cell class="w-full">
                  @foreach($variant->values as $value)
                    {{ $value->name->en }} {{ !$loop->last ? '/' : null }}
                  @endforeach
                </x-hub::table.cell>
                <x-hub::table.cell>
                  {{ $variant->sku }}
                </x-hub::table.cell>
                <x-hub::table.cell>
                  @php
                    $price = $variant->basePrices->first(fn($price) => $price->currency->default);
                  @endphp
                  {{ $price->price->formatted }}
                </x-hub::table.cell>
                <x-hub::table.cell>
                  {{ $variant->stock }} ({{ $variant->backorder }})
                </x-hub::table.cell>
                <x-hub::table.cell class="w-3">
                  <a href="{{ route('hub.products.variants.show', [
                    'product' => $product,
                    'variant' => $variant,
                  ]) }}" class="text-indigo-500 hover:underline">{{ __('adminhub::partials.products.variants.table_row_action_text') }}</a>
                </x-hub::table.cell>
                <x-hub::table.cell>
                  @if($variant->created_at == $variant->updated_at)
                    <button
                      class="text-red-600 hover:underline"
                      type="button"
                      wire:click.prevent="deleteVariant('{{ $variant->id }}')"
                    >
                      {{ __('adminhub::partials.products.variants.table_row_delete_text') }}
                    </button>
                  @endif
                </x-hub::table.cell>
              </x-hub::table.row>
              @endforeach
            </x-slot>
          </x-hub::table>
        @endif
      @endif
  </div>
</div>
