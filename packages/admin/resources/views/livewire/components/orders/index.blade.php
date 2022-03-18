<div class="flex-col px-12 space-y-4">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">{{ __('adminhub::global.orders') }}</strong>
  </div>

  <div class="space-y-4">
    <x-hub::table>
      <x-slot name="toolbar">
        <div class="p-4 space-y-4 border-b" x-data="{ filtersVisible: true }">
          <div class="flex items-center space-x-4">
            <div class="flex items-center w-full space-x-4">
              <x-hub::input.text placeholder="Search by reference or customer name" class="py-2" wire:model.debounce.400ms="search" />

              <x-hub::button theme="gray" class="inline-flex items-center" @click.prevent="filtersVisible = !filtersVisible">
                <x-hub::icon ref="filter" class="w-4 mr-1" />
                Filter
              </x-hub::button>
            </div>
          </div>

          <div class="grid grid-cols-4 gap-4" x-show="filtersVisible" x-cloak>
            <x-hub::input.group label="Status" for="brand">
              <x-hub::input.select wire:model="filters.status">
                <option value>Any</option>
                @foreach($this->statuses as $status => $label)
                <option value="{{ $status }}">{{ $label }}</option>
                @endforeach
              </x-hub::input.select>
            </x-hub::input.group>
          </div>
        </div>
      </x-slot>
      <x-slot name="head">
        <x-hub::table.heading>
          Status
        </x-hub::table.heading>
        <x-hub::table.heading>
          Reference
        </x-hub::table.heading>
        <x-hub::table.heading>
          Customer
        </x-hub::table.heading>
        <x-hub::table.heading>
          Total
        </x-hub::table.heading>
        <x-hub::table.heading>
          Date
        </x-hub::table.heading>
        <x-hub::table.heading>
          Time
        </x-hub::table.heading>
        <x-hub::table.heading></x-hub::table.heading>
      </x-slot>
      <x-slot name="body">
        @forelse($this->orders as $order)
          <x-hub::table.row wire:key="row-{{ $order->id }}">
            <x-hub::table.cell>
              {{ $order->statusLabel }}
            </x-hub::table.cell>
            <x-hub::table.cell>
              {{ $order->reference }}
            </x-hub::table.cell>
            <x-hub::table.cell>
              {{ $order->billingAddress->first_name }}
            </x-hub::table.cell>
            <x-hub::table.cell>
              {{ $order->total }}
            </x-hub::table.cell>
            <x-hub::table.cell>
              @if($order->placed_at)
                {{ $order->placed_at->format('jS M Y') }}
              @else
                {{ $order->created_at->format('jS M Y') }}
              @endif
            </x-hub::table.cell>
            <x-hub::table.cell>
              @if($order->placed_at)
                {{ $order->placed_at->format('h:ma') }}
              @else
                {{ $order->created_at->format('h:ma') }}
              @endif
            </x-hub::table.cell>
            <x-hub::table.cell>
              <a href="{{ route('hub.orders.show', $order->id) }}" class="text-indigo-500 hover:underline">View</a>
            </x-hub::table.cell>
          </x-hub::table.row>
        @empty

        @endforelse
      </x-slot>
    </x-hub::table>
    <div>
      {{ $this->orders->links() }}
    </div>
  </div>
</div>
