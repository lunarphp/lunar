<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header class="flex items-center justify-between">
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.products.associations.heading') }}
      </h3>
      <div class="flex items-center space-x-6">
        <div class="flex items-center space-x-2 text-xs">
          <span class="text-green-500">Show inverse</span>
          <x-hub::input.toggle :on="true" />
        </div>
        @livewire('hub.components.product-search', [
          'existing' => collect([]),
        ])
      </div>
    </header>

    <div>
      <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading class="w-24">

            </x-hub::table.heading>
            <x-hub::table.heading>
              Product
            </x-hub::table.heading>
            <x-hub::table.heading>
              Type
            </x-hub::table.heading>
            <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
          <x-hub::table.row>
            <x-hub::table.cell>
              <img src="http://demo-store.test/storage/products/2022/02/10/conversions/black_jeans-small.jpg" class="w-12 rounded">
            </x-hub::table.cell>
            <x-hub::table.cell>Product A</x-hub::table.cell>
            <x-hub::table.cell>
              <x-hub::input.select>
                <option>Cross Sell</option>
              </x-hub::input.select>
            </x-hub::table.cell>
            <x-hub::table.cell>
              <a href="#" class="text-red-500 hover:underline">Remove</a>
            </x-hub::table.cell>
          </x-hub::table.row>

          <x-hub::table.row>
            <x-hub::table.cell>
              <img src="http://demo-store.test/storage/products/2022/02/10/conversions/nike_orange_white-small.jpg" class="w-12 rounded">
            </x-hub::table.cell>
            <x-hub::table.cell>Product B</x-hub::table.cell>
            <x-hub::table.cell>
              <span class="text-gray-500">Inverse: Cross Sell</span>
            </x-hub::table.cell>
            <x-hub::table.cell>
              <a href="#" class="text-red-500 hover:underline">Remove</a>
            </x-hub::table.cell>
          </x-hub::table.row>
        </x-slot>
      </x-hub::table>
    </div>



    {{-- <x-hub::modal.dialog wire:model="showAssociationAttach" >
      <x-slot name="title">Add association</x-slot>
      <x-slot name="content">
        Test
      </x-slot>
      <x-slot name="footer"></x-slot>
    </x-hub::modal.dialog> --}}
  </div>
</div>