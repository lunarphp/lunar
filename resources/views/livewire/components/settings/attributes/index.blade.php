<div class="flex-col space-y-4">
  <div class="text-right">
    <x-hub::button tag="a" href="{{ route('hub.attributes.create') }}">Create Attribute</x-hub::button>
  </div>
  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>
        {{ __('adminhub::global.name') }}
      </x-hub::table.heading>
      <x-hub::table.heading sortable>
        {{ __('adminhub::global.attribute_type') }}
      </x-hub::table.heading>
      <x-hub::table.heading sortable>
        {{ __('adminhub::global.type') }}
      </x-hub::table.heading>
      <x-hub::table.heading>
        {{ __('adminhub::global.required') }}
      </x-hub::table.heading>
      <x-hub::table.heading></x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @forelse($attributes as $attribute)
        <x-hub::table.row>
          <x-hub::table.cell>
            {{ $attribute->name['en'] }}
          </x-hub::table.cell>
          <x-hub::table.cell>
            {{ class_basename($attribute->attribute_type) }}
          </x-hub::table.cell>
          <x-hub::table.cell>
            {{ class_basename($attribute->type) }}
          </x-hub::table.cell>
          <x-hub::table.cell>
            <x-hub::icon :ref="$attribute->required ? 'check' : 'x'" :class="$attribute->required ? 'text-green-500' : 'text-red-500'" style="solid" />
          </x-hub::table.cell>
          <x-hub::table.cell class="text-right">
            <a href="{{ route('hub.attributes.show', $attribute) }}" class="text-indigo-500 hover:underline">
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
