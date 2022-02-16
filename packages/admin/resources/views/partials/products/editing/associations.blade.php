<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header class="flex items-center justify-between">
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.products.associations.heading') }}
      </h3>
      <div class="flex items-center space-x-2">
        {{-- <div class="flex items-center space-x-2 text-xs">
          <span class="@if($showInverseAssociations)text-green-500 @endif">Show inverse</span>
          <x-hub::input.toggle wire:model="showInverseAssociations" />
        </div> --}}
        <x-hub::button size="sm">Add associations</x-hub::button>
        <x-hub::button size="sm" theme="gray">Add inverse associations</x-hub::button>
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
          @foreach($associations->filter(fn($product) => $showInverseAssociations ? true : !$product['inverse']) as $product)
          <x-hub::table.row>
            <x-hub::table.cell>
              <img src="{{ $product['thumbnail']}}" class="w-12 rounded">
            </x-hub::table.cell>
            <x-hub::table.cell>{{ $product['name'] }}</x-hub::table.cell>
            <x-hub::table.cell>
              @if(!$product['inverse'])
              <x-hub::input.select>
                <option>{{ $product['type']}}</option>
              </x-hub::input.select>
              @else
                <span class="text-gray-500">Inverse: {{ $product['type'] }}</span>
              @endif
            </x-hub::table.cell>
            <x-hub::table.cell>
              <a href="#" class="text-red-500 hover:underline">Remove</a>
            </x-hub::table.cell>
          </x-hub::table.row>
          @endforeach
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