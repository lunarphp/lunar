<div class="flex-col space-y-4">
    <div class="flex-col px-4 py-5 space-y-4 bg-white shadow sm:rounded-md sm:p-6">
      <x-hub::input.group label="Name" for="name" :error="$errors->first('productType.name')" required>
        <x-hub::input.text wire:model="productType.name" name="name" id="name" :error="$errors->first('productType.name')" />
      </x-hub::input.group>
    </div>

    <div x-data="{ view: 'products' }">
      @if(!$this->variantsDisabled)
        <nav class="flex space-x-4" aria-label="Tabs">
          <!-- Current: "bg-gray-100 text-gray-700", Default: "text-gray-500 hover:text-gray-700" -->
          <button type="button" wire:click="$set('view', 'products')" class="px-3 py-3 text-sm font-medium @if($view == 'products') text-gray-800 bg-white @else test-gray-500 hover:text-gray-700 @endif rounded-t">
              {{ __('adminhub::partials.product-type.product_attributes_btn') }}
          </button>

          <button
            type="button"
            wire:click="$set('view', 'variants')"
            class="px-3 py-3 text-sm font-medium @if($view == 'variants') text-gray-800 bg-white @else test-gray-500 hover:text-gray-700 @endif rounded-t"
          >
          {{ __('adminhub::partials.product-type.variant_attributes_btn') }}
          </button>
        </nav>
      @endif

      <div class="p-6 bg-white rounded-b shadow">
        @if($view == 'products')
          @include('adminhub::partials.product-types.attributes', [
            'type' => 'products',
          ])
        @endif

        @if($view == 'variants')
          @include('adminhub::partials.product-types.attributes', [
            'type' => 'variants',
          ])
        @endif
      </div>
    </div>

    {{-- <div x-data="{ view: 'available' }">
      <div class="grid grid-cols-2 gap-6 lg:hidden">
        <button
          class="px-3 py-2 text-sm rounded"
          type="button"
          x-on:click.prevent="view = 'available'"
          :class="{
            'bg-white shadow' : view == 'available',
            'bg-gray-200 text-gray-600 hover:bg-gray-400': view != 'available',
          }"
        >
          {{ __('adminhub::partials.product-type.available_title') }}
        </button>
        <button
          class="px-3 py-2 text-sm rounded"
          type="button"
          x-on:click.prevent="view = 'selected'"
          :class="{
            'bg-white shadow' : view == 'selected',
            'bg-gray-200 text-gray-600 hover:bg-gray-400': view != 'selected',
          }"
        >
          {{ __('adminhub::partials.product-type.selected_title', [
            'count' => $this->selectedAttributes->count()
          ]) }}
        </button>
      </div>
      <div class="lg:grid lg:grid-cols-2 lg:gap-8">
        <div class="space-y-2">
          <h3 class="hidden lg:block">
            {{ __('adminhub::partials.product-type.available_title') }}
          </h3>

          <div class="space-y-2" :class="view == 'available' ? 'block' : 'hidden lg:block'">
            <x-hub::input.text :placeholder="__('adminhub::partials.product-type.attribute_search_placeholder')" wire:model="attributeSearch" />

            <div class="space-y-2">
              @forelse($this->availableAttributes() as $attribute)
                <div class="flex items-center justify-between p-3 text-sm bg-white rounded shadow">
                  <div>{{ $attribute->translate('name') }}</div>

                  <div>
                    <x-hub::button type="button" theme="gray" size="xs" wire:click="addAttribute('{{ $attribute->id }}')">
                      {{ __('adminhub::global.add') }}
                    </x-hub::button>
                  </div>
                </div>
              @empty
                <div class="p-4 text-sm text-gray-600 border border-gray-200 rounded">
                  @if(!$attributeSearch)
                    {{ __('adminhub::catalogue.product-types.attribute.search.empty') }}
                  @else
                    {{ __('adminhub::catalogue.product-types.attribute.search.no_results', [
                      'search' => $attributeSearch,
                    ]) }}
                  @endif
                </div>
              @endforelse
            </div>
          </div>

          {{ $this->availableAttributes()->links() }}
        </div>

        <div class="space-y-2" :class="view == 'selected' ? 'block' : 'hidden lg:block'">
          <h3 class="hidden lg:block">
            {{ __('adminhub::partials.product-type.selected_title', [
              'count' => $this->selectedAttributes->count()
            ]) }}
          </h3>
          <div class="space-y-2">
            @foreach($this->selectedAttributes as $attribute)
              <div class="flex items-center justify-between p-3 text-sm bg-white rounded shadow">
                <div class="flex items-center">
                  @if($attribute->system)
                    <x-hub::icon ref="lock-closed" class="w-4 mr-2 text-yellow-700" />
                  @endif
                  {{ $attribute->translate('name') }}
                </div>
                <div>
                  @if(!$attribute->system)
                  <x-hub::button type="button" theme="gray" size="xs" wire:click="removeAttribute('{{ $attribute->id }}')">
                    {{ __('adminhub::global.remove') }}
                  </x-hub::button>
                  @else
                    <span class="text-xs text-gray-500">
                      {{ __('adminhub::partials.product-type.attribute_system_required') }}
                    </span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div> --}}
  </div>
