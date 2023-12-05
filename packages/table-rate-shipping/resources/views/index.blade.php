<div class="flex-col px-8 space-y-4 md:px-12">
  <div class="items-center justify-between md:flex">
    <strong class="block text-lg font-bold md:text-2xl">
      {{ __('shipping::index.title') }}
    </strong>
    <x-hub::button tag="a" href="{{ route('hub.shipping-zone.create') }}">
      {{ __('shipping::index.add_zone_btn') }}
    </x-hub::button>
  </div>
   <div class="flex-col space-y-4">
      <x-hub::table>
        <x-slot name="head">
          <x-hub::table.heading>
            {{ __('adminhub::global.name') }}
          </x-hub::table.heading>
          <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
          @forelse($this->shippingZones as $zone)
          <x-hub::table.row>
              <x-hub::table.cell>
                {{ $zone->name }}
              </x-hub::table.cell>

              <x-hub::table.cell>
                <a href="{{ route('hub.shipping.shipping-zone.show', $zone->id) }}" class="text-indigo-500 hover:underline">
                  {{ __('adminhub::global.edit') }}
                </a>
              </x-hub::table.cell>
          </x-hub::table.row>
          @empty
            <x-hub::table.no-results>
              {{ __('shipping::index.no_results') }}
            </x-hub::table.no-results>
          @endforelse
        </x-slot>
      </x-hub::table>
      <div>
        {{ $this->shippingZones->links() }}
      </div>
    </div>
</div>
