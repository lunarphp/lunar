<div class="flex-col px-8 space-y-4 md:px-12">
  <div class="space-y-4">
    <form action="#" wire:submit.prevent="save" class="shadow sm:rounded-md">
      @include('shipping::partials.forms.shipping-zone')
    </form>

    <x-hub::modal.dialog form="deleteMethod" wire:model="shippingMethodToRemove">
      <x-slot name="title">
        {{ __('shipping::shipping-zones.show.shipping_method.delete')}}
      </x-slot>
      <x-slot name="content">
        <x-hub::alert level="danger">
          {{ __('shipping::shipping-zones.show.shipping_method.delete_confirm')}}
        </x-hub::alert>
      </x-slot>
      <x-slot name="footer">
        <x-hub::button type="button" wire:click.prevent="$set('shippingMethodToRemove', null)" theme="gray">
          {{ __('adminhub::global.cancel') }}
        </x-hub::button>
        <x-hub::button type="submit" theme="danger">
          {{ __('adminhub::global.remove') }}
        </x-hub::button>
      </x-slot>
    </x-hub::modal.dialog>

    <x-hub::modal.dialog form="deleteZone" wire:model="showDeleteConfirm">
      <x-slot name="title">
        {{ __('shipping::shipping-zones.show.shipping_zone.delete')}}
      </x-slot>
      <x-slot name="content">
        <x-hub::alert level="danger">
          {{ __('shipping::shipping-zones.show.shipping_zone.delete_message')}}
        </x-hub::alert>
      </x-slot>
      <x-slot name="footer">
        <x-hub::button type="button" wire:click.prevent="$set('showDeleteConfirm', false)" theme="gray">
          {{ __('adminhub::global.cancel') }}
        </x-hub::button>
        <x-hub::button type="submit" theme="danger">
          {{ __('adminhub::global.remove') }}
        </x-hub::button>
      </x-slot>
    </x-hub::modal.dialog>

    <div class="shadow sm:rounded-md">
      <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
        <header class="flex items-center justify-between">
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            {{ __('shipping::shipping-zones.show.shipping_methods.title') }}
          </h3>

          <div>
            <x-hub::dropdown :value="__('shipping::shipping-zones.show.shipping_methods.add_btn')">
              <x-slot name="options">
                @foreach($this->supportedShippingMethods as $shippingMethod)
                  <x-hub::dropdown.button type="button" wire:click="addShippingMethod('{{ $shippingMethod['key'] }}')">
                    {{ $shippingMethod['name'] }}
                  </x-hub::dropdown.button>
                @endforeach
              </x-slot>
            </x-hub::dropdown>
          </div>
        </header>

        <div class="space-y-4">
          @foreach($this->shippingMethods as $key => $method)
            <div class="flex items-center justify-between pb-4 border-b" wire:key="shipping_method_row_{{ $key }}">
              <div class="grow">
                @if($method['custom_name'])
                  <div>
                    <strong>{!! $method['custom_name'] !!}</strong>
                    <small class="text-gray-500">({{ $method['name'] }})</small>
                  </div>
                @else
                  <strong>{{ $method['name'] }}</strong>
                @endif
                <p class="text-sm text-gray-500">{!! $method['custom_description'] ?: $method['description'] !!}</p>
              </div>

              <div class="ml-4">
                <x-hub::input.toggle :on="$method['enabled']" wire:click="toggleMethod('{{ $key }}')" />
              </div>

              @if($method['method_id'] && $method['enabled'])
                <div class="ml-4">
                  <x-hub::button type="button" wire:click="$set('methodToEdit', '{{ $key }}')">
                    {{ __('adminhub::global.edit') }}
                  </x-hub::button>
                </div>
              @endif

              <div class="ml-4">
                <button type="button" class="text-gray-500 hover:text-gray-900" type="button" wire:click="$set('shippingMethodToRemove', {{ $method['id'] }})">
                  <x-hub::icon ref="trash" class="w-4" />
                </button>
              </div>

              <div @if($methodToEdit != $key) class="hidden" @endif>
                <x-hub::slideover :title="$method['custom_name'] ?: $method['name']" wire:model="methodToEdit">
                  @livewire($method['component'], [
                    'shippingMethodId' => $method['method_id'],
                    'shippingZone' => $shippingZone,
                  ], key('shipping_method_'.$key))
                </x-hub::slideover>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>



  {{-- @include('shipping::partials.ship-by-total')
  @include('shipping::partials.free-shipping')
  @include('shipping::partials.flat-rate') --}}
</div>
