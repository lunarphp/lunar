<div class="flex-col px-8 space-y-4 md:px-12">
  <div class="items-center justify-between md:flex">
    <strong class="block text-lg font-bold md:text-2xl">
      Shipping Exclusion Lists
    </strong>
    <x-hub::button tag="a" href="{{ route('hub.exclusion-lists.create') }}">
      Add list
    </x-hub::button>
  </div>
   <div class="flex-col space-y-4">
      <x-hub::table>
        <x-slot name="head">
          <x-hub::table.heading>
            Name
          </x-hub::table.heading>

          <x-hub::table.heading>
            Product Count
          </x-hub::table.heading>
          <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
          @forelse($this->lists as $list)
          <x-hub::table.row>
              <x-hub::table.cell>
                {{ $list->name }}
              </x-hub::table.cell>

              <x-hub::table.cell>
                {{ $list->exclusions_count }}
              </x-hub::table.cell>

              <x-hub::table.cell>
                <a href="{{ route('hub.exclusion-lists.show', $list->id) }}" class="text-indigo-500 hover:underline">
                    Edit
                </a>
              </x-hub::table.cell>
          </x-hub::table.row>
          @empty
            <x-hub::table.no-results>
              {{ __('shipping::index.no_results') }}
            </x-hub::table.no-results>
          @endforelse
          {{-- @forelse($currencies as $currency)
            <x-hub::table.row>
              <x-hub::table.cell>
                <span class="block w-2 h-2 border rounded-full @if($currency->default) bg-green-400 border-green-600 @endif"></span>
              </x-hub::table.cell>
              <x-hub::table.cell>
                {{ $currency->name }}
              </x-hub::table.cell>
              <x-hub::table.cell>
                {{ $currency->code }}
              </x-hub::table.cell>
              <x-hub::table.cell>
                {{ $currency->exchange_rate }}
              </x-hub::table.cell>
              <x-hub::table.cell>
                <x-hub::icon :ref="$currency->enabled ? 'check' : 'x'" :class="$currency->enabled ? 'text-green-500' : 'text-red-500'" style="solid" />
              </x-hub::table.cell>
              <x-hub::table.cell class="text-right">
                <a href="{{ route('hub.currencies.show', $currency->id) }}" class="text-indigo-500 hover:underline">
                    {{ __('adminhub::settings.currencies.index.table_row_action_text') }}
                  </a>
              </x-hub::table.cell>
            </x-hub::table.row>
          @empty
            <x-hub::table.no-results>
              {{ __('adminhub::settings.currencies.index.no_results') }}
            </x-hub::table.no-results>
          @endforelse --}}
        </x-slot>
      </x-hub::table>
      <div>
        {{-- {{ $currencies->links() }} --}}
      </div>
    </div>
</div>
