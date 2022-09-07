<div class="space-y-4">
    {{ $this->purchaseHistory->links() }}

    <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading>
                {{ __('adminhub::partials.customers.purchase-history.purchasable') }}
            </x-hub::table.heading>

            <x-hub::table.heading>
                {{ __('adminhub::partials.customers.purchase-history.identifier') }}
            </x-hub::table.heading>

            <x-hub::table.heading>
                {{ __('adminhub::partials.customers.purchase-history.quantity') }}
            </x-hub::table.heading>

            <x-hub::table.heading>
                {{ __('adminhub::partials.customers.purchase-history.revenue') }}
            </x-hub::table.heading>

            <x-hub::table.heading>
                {{ __('adminhub::partials.customers.purchase-history.order_count') }}
            </x-hub::table.heading>

            <x-hub::table.heading>
                {{ __('adminhub::partials.customers.purchase-history.last_ordered') }}
            </x-hub::table.heading>
        </x-slot>

        <x-slot name="body">
            @foreach ($this->purchaseHistory as $row)
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
