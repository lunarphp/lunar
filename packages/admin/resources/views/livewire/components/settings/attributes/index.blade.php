<div class="flex-col space-y-4">
  <div class="text-right">
    <x-hub::button tag="a" href="{{ route('hub.attributes.create') }}">Create Attribute</x-hub::button>
  </div>
  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>
        {{ __('adminhub::global.attribute_type') }}
      </x-hub::table.heading>
      <x-hub::table.heading sortable>
        {{ __('adminhub::global.attribute_groups') }}
      </x-hub::table.heading>
      <x-hub::table.heading>
        {{ __('adminhub::global.attributes') }}
      </x-hub::table.heading>
      <x-hub::table.heading></x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @forelse($this->attributeTypes as $handle => $attributeType)
        <x-hub::table.row>
          <x-hub::table.cell>
            {{ $attributeType }}
          </x-hub::table.cell>
          <x-hub::table.cell>
            {{ $this->getStats($attributeType)['group_count'] }}
          </x-hub::table.cell>
          <x-hub::table.cell>
            {{ $this->getStats($attributeType)['attribute_count'] }}
          </x-hub::table.cell>
          <x-hub::table.cell class="text-right">
            <a href="{{ route('hub.attributes.show', $handle) }}" class="text-indigo-500 hover:underline">
                {{ __('adminhub::settings.currencies.index.table_row_action_text') }}
              </a>
          </x-hub::table.cell>
        </x-hub::table.row>
      @empty
        <x-hub::table.no-results>
          {{ __('adminhub::settings.attributes.index.no_results') }}
        </x-hub::table.no-results>
      @endforelse
    </x-slot>
  </x-hub::table>
  <div>
    {{ $attributes->links() }}
  </div>
</div>
