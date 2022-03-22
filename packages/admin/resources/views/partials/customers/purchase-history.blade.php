<div class="space-y-4">
  {{ $this->purchaseHistory->links() }}

  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>
        Purchasable
      </x-hub::table.heading>
      <x-hub::table.heading>
        Idenfitier
      </x-hub::table.heading>
      <x-hub::table.heading>
        Quantity
      </x-hub::table.heading>
      <x-hub::table.heading>
        Revenue
      </x-hub::table.heading>

      <x-hub::table.heading>
        No. Orders
      </x-hub::table.heading>

      <x-hub::table.heading>
        Last ordered
      </x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($this->purchaseHistory as $row)
        <x-hub::table.row>
          <x-hub::table.cell>
            {{ $row->description }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $row->identifier }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $row->quantity }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $row->sub_total->formatted }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $row->order_count }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            {{ $row->last_ordered }}
          </x-hub::table.cell>
        </x-hub::table.row>

      @endforeach
    </x-slot>
  </x-hub::table>
</div>
