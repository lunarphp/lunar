<div class="space-y-4">
  {{ $this->orders->links() }}
  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>
        ID
      </x-hub::table.heading>

      <x-hub::table.heading>
        Status
      </x-hub::table.heading>

      <x-hub::table.heading>
        Reference
      </x-hub::table.heading>

      <x-hub::table.heading>
        Total
      </x-hub::table.heading>

      <x-hub::table.heading>
        Date
      </x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($this->orders as $order)
        <x-hub::table.row>
          <x-hub::table.cell>
            <a href="{{ route('hub.orders.show', $order->id) }}" class="text-indigo-500 hover:underline">{{ $order->id }}</a>
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $order->status }}
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
        </x-hub::table.row>

      @endforeach
    </x-slot>
  </x-hub::table>
</div>