<form wire:submit.prevent="save" class="px-12 pb-24 space-y-6">
  <div class="flex items-center">
    <div class="flex items-center space-x-4">
      <a href="{{ route('hub.products.show', $product) }}" class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white" title="Go back to product listing">
        <x-hub::icon ref="chevron-left" style="solid" class="w-8 h-8" />
      </a>
      <strong class="text-xl">
         @foreach($variant->values as $value)
          {{ $value->translate('name') }} {{ !$loop->last ? '/' : null }}
         @endforeach
      </strong>
    </div>
  </div>

  <div class="gap-4 lg:grid lg:grid-cols-12">
    <div class="space-y-6 lg:col-span-3">
      <!-- This example requires Tailwind CSS v2.0+ -->
      <nav class="space-y-1" aria-label="Sidebar">
        <!-- Current: "bg-gray-100 text-gray-900", Default: "text-gray-600 hover:bg-gray-50 hover:text-gray-900" -->
        @foreach($product->variants as $v)
          <a
            href="{{ route('hub.products.variants.show', [
              'product' => $product,
              'variant' => $v,
            ])}}"
            class="flex items-center px-3 py-2 text-sm font-medium text-gray-900 rounded-md @if($variant->id == $v->id) bg-gray-50 @else @endif"
            aria-current="page"
          >
            <div class="w-12 mr-2">
                @if($media = $v->media->first())
                  <img class="w-8 h-8 rounded shadow" src="{{ $media->getFullUrl('small') }}">
                @else

                  <x-hub::icon ref="photograph" />
                @endif
            </div>
            <div class="w-full">
              <span class="block truncate w-44">
                @foreach($v->values as $value)
                  {{ $value->translate('name') }} {{ !$loop->last ? '/' : null }}
                @endforeach
              </span>
            </div>
          </a>
        @endforeach
      </nav>
      <x-hub::button theme="gray" type="button" wire:click="$set('showAddVariant', true)">
        {{ __('adminhub::catalogue.product-variants.add_variant.btn') }}
      </x-hub::button>

      <x-hub::slideover :title="__('adminhub::catalogue.product-variants.add_variant.title')" wire:model="showAddVariant">
        <div class="space-y-4">
          @foreach($this->variantOptions() as $option)
            <x-hub::input.group
              :label="$option->translate('name')"
              for="name"
              :error="$errors->first('newValues.'.$option->id)"
            >
              <div class="flex items-center">
                <div class="w-full">
                  <x-hub::input.select wire:model="newValues.{{ $option->id }}">
                    <option value>
                      {{ __('adminhub::catalogue.product-variants.add_variant.null_option') }}
                    </option>
                    @foreach($option->values as $value)
                      <option value="{{ $value->id }}">{{ $value->translate('name') }}</option>
                    @endforeach
                  </x-hub::input.select>
                </div>
                <div class="w-1/3 text-right">
                  <x-hub::button
                    type="button" theme="gray" size="sm"
                    wire:click.prevent="$emit('variant-show.selected-option', '{{ $option->id }}')"
                  >{{ __('adminhub::catalogue.product-variants.add_variant.add_new_option') }}</x-hub::button>
                </div>
              </div>
            </x-hub::input.group>
          @endforeach

          @livewire('hub.components.product-options.option-value-create-modal', [
            'canPersist' => false,
          ])
        </div>
        @if(session()->has('variant_exists'))
          <div class="mt-4">
          <x-hub::alert level="danger">
            {{ __('adminhub::catalogue.product-variants.add_variant.already_exists') }}
          </x-hub::alert>
          </div>
        @endif
        <div class="mt-4">
          <x-hub::button theme="gray" type="button" wire:click="generateVariants">
            {{ __('adminhub::catalogue.product-variants.add_variant.btn') }}
          </x-hub::button>
        </div>
      </x-hub::slideover>
    </div>
    <div class="space-y-6 lg:col-span-9">

      <div id="attributes">
        @include('adminhub::partials.attributes')
      </div>
      @include('adminhub::partials.pricing')
      @include('adminhub::partials.products.variants.image')
      @include('adminhub::partials.products.variants.identifiers')
      @include('adminhub::partials.products.variants.inventory')
      @include('adminhub::partials.shipping')


      <div class="bg-white border border-red-300 rounded shadow">
        <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
          {{ __('adminhub::inputs.danger_zone.title') }}
        </header>
        <div class="p-6 text-sm">
          <div class="grid items-center grid-cols-2 gap-4">
            <div>
              <strong>{{ __('adminhub::inputs.danger_zone.label', ['model' => 'variant']) }}</strong>
            </div>
            <div class="text-right">
              <x-hub::button wire:click.prevent="$set('showDeleteConfirm', true)" type="button" theme="danger">
                {{ __('adminhub::global.delete') }}
              </x-hub::button>
            </div>
          </div>
        </div>
      </div>

      <x-hub::modal.dialog wire:model="showDeleteConfirm">
          <x-slot name="title">
            {{ __('adminhub::catalogue.product-variants.delete_confirm.title') }}
          </x-slot>

          <x-slot name="content">
            {{ __('adminhub::catalogue.product-variants.delete_confirm.strapline') }}
          </x-slot>

          <x-slot name="footer">
            <div class="flex items-center justify-end space-x-4">
              <x-hub::button theme="gray" type="button" wire:click.prevent="$set('showDeleteConfirm', false)">
                {{ __('adminhub::global.cancel') }}
              </x-hub::button>
              <x-hub::button wire:click.prevent="delete" theme="danger" type="button">
                {{ __('adminhub::catalogue.product-variants.delete_confirm.btn') }}
              </x-hub::button>
            </div>
          </x-slot>
        </x-hub::modal.dialog>

      <div class="pt-12 mt-12 border-t">
        @livewire('hub.components.activity-log-feed', [
          'subject' => $variant,
        ])
      </div>
    </div>

    <div class="fixed bottom-0 right-0 z-50 p-6 mr-0 text-right bg-white bg-opacity-75 border-t left-64">
      <div class="flex justify-end space-x-6">
        <x-hub::button>Save Variant</x-hub::button>
      </div>
    </div>
  </div>




                      {{-- <div>
                        <header class="mt-6 mb-4">
                          <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Pricing
                          </h3>
                        </header>

                        <div class="flex items-center justify-between">
                          <div>
                            <strong>Customer group pricing</strong>
                            <p class="text-xs text-gray-600">Determines if you would like different pricing across customer groups.</strong>
                          </div>

                          <x-hub::input.toggle />
                        </div>
                      </div>

                      <div>
                        <div class="grid grid-cols-2">
                          <x-hub::input.group label="Unit Price" instructions="The unit price, including tax." for="sku">
                            <x-hub::input.price value="2.99" symbol="£" currencyCode="GBP" />
                          </x-hub::input.group>
                        </div>
                      </div>

                      <div>
                      <div class="flex items-center justify-between">
                          <div>
                            <strong>Customer group pricing</strong>
                            <p class="text-xs text-gray-600">Determines if you would like different pricing across customer groups.</strong>
                          </div>

                          <x-hub::input.toggle />
                        </div> --}}
</form>
