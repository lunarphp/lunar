<div class="space-y-4">
  {{ $this->orders->links() }}
  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>
        {{ __('adminhub::global.id') }}
      </x-hub::table.heading>

      <x-hub::table.heading>
        {{ __('adminhub::global.user') }}
      </x-hub::table.heading>

      <x-hub::table.heading>
        {{ __('adminhub::global.status') }}
      </x-hub::table.heading>

      <x-hub::table.heading>
        {{ __('adminhub::global.reference') }}
      </x-hub::table.heading>

      <x-hub::table.heading>
        {{ __('adminhub::global.total') }}
      </x-hub::table.heading>

      <x-hub::table.heading>
        {{ __('adminhub::global.date') }}
      </x-hub::table.heading>

      <x-hub::table.heading>
      </x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($this->orders as $order)
        <x-hub::table.row>
          <x-hub::table.cell>
            {{ $order->id }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $order->user->name }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            <x-hub::orders.status :status="$order->status" />
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $order->reference }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $order->sub_total->formatted }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $order->placed_at?->format('Y-m-d h:ia') }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            <a href="{{ route('hub.orders.show', $order->id) }}" class="text-indigo-500 hover:underline">
              {{ __('adminhub::global.view') }}
            </a>
          </x-hub::table.cell>
        </x-hub::table.row>

      @endforeach
    </x-slot>
  </x-hub::table>
</div>