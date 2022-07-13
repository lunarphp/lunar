
<div class="flex-col space-y-4">
  @include('adminhub::partials.navigation.taxes')

  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>Name</x-hub::table.heading>
      <x-hub::table.heading>Type</x-hub::table.heading>
      <x-hub::table.heading>Active</x-hub::table.heading>
      <x-hub::table.heading>Default</x-hub::table.heading>
      <x-hub::table.heading></x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($taxZones as $zone)
      <x-hub::table.row>
        <x-hub::table.cell>
          {{ $zone->name }}
        </x-hub::table.cell>

        <x-hub::table.cell>
          {{ $zone->zone_type }}
        </x-hub::table.cell>

        <x-hub::table.cell>
          <x-hub::icon :ref="$zone->active ? 'check' : 'x'" :class="$zone->active ? 'text-green-500' : 'text-red-500'" style="solid" />
        </x-hub::table.cell>

        <x-hub::table.cell>
          <x-hub::icon :ref="$zone->default ? 'check' : 'x'" :class="$zone->default ? 'text-green-500' : 'text-red-500'" style="solid" />
        </x-hub::table.cell>

        <x-hub::table.cell class="text-right">
            <a href="{{ route('hub.taxes.show', $zone->id) }}" class="text-indigo-500 hover:underline">
              {{ __('adminhub::settings.taxes.tax-zones.index.table_row_action_text') }}
            </a>
        </x-hub::table.cell>
      </x-hub::table.row>
      @endforeach
    </x-slot>
  </x-hub::table>
  <div>
    {{ $taxZones->links() }}
  </div>
</div>
