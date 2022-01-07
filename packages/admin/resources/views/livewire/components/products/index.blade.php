<div class="flex-col px-12 space-y-4">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">Products</strong>
    <div class="text-right">
      <x-hub::button tag="a" href="{{ route('hub.products.create') }}">Create Product</x-hub::button>
    </div>
  </div>

  <div>
    {{-- <div class="sm:hidden">
      <label for="tabs" class="sr-only">Select a tab</label>
      <select id="tabs" name="tabs" class="block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        <option>My Account</option>

        <option>Company</option>

        <option selected>Team Members</option>

        <option>Billing</option>
      </select>
    </div> --}}
    {{-- <div class="hidden sm:block">
      <div class="border-b border-gray-200">
        <nav class="flex -mb-px space-x-8" aria-label="Tabs">
          <!-- Current: "border-indigo-500 text-indigo-600", Default: "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" -->
          <a href="#" class="px-1 py-4 text-sm font-medium text-indigo-600 border-b-2 border-indigo-500 whitespace-nowrap">
            All products
          </a>

          <a href="#" class="px-1 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
            Featured
          </a>

          <a href="#" class="px-1 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" aria-current="page">
            Out of stock
          </a>

          <a href="#" class="inline-flex items-center px-1 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
            <x-hub::icon ref="plus" style="solid" />
            Add custom view
          </a>
        </nav>
      </div>
    </div> --}}
  </div>
  <x-hub::table>
    <x-slot name="toolbar">
      <div class="p-4 space-y-4 border-b" x-data="{ filtersVisible: false }">
        <div class="flex items-center space-x-4">
          @if(count($selected))
          <div>
            <x-hub::dropdown value="Actions" position="right">
              <x-slot name="options" >
                <x-hub::dropdown.button>Export</x-hub::dropdown.button>
              </x-slot>
            </x-hub::dropdown>
          </div>
          @endif
          <div class="flex items-center w-full space-x-4">
            <x-hub::input.text placeholder="Search by attribute or SKU" class="py-2" wire:model="search" />

            {{-- <x-hub::button theme="gray" class="inline-flex items-center" @click.prevent="filtersVisible = !filtersVisible">
              <x-hub::icon ref="filter" class="w-4 mr-1" />
              Filter
            </x-hub::button> --}}
          </div>
        </div>

        <div class="grid grid-cols-4 gap-4" x-show="filtersVisible" x-cloak>
          <x-hub::input.group label="Status" for="brand">
            <x-hub::input.select wire:model="filters.status">
              <option>Any</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </x-hub::input.select>
          </x-hub::input.group>

          <x-hub::input.group label="Stock Level" for="brand">
            <div class="grid grid-cols-2 gap-2">
              <x-hub::input.text placeholder="From" />
              <x-hub::input.text placeholder="To" />
            </div>
          </x-hub::input.group>

          <x-hub::input.group label="Quantity Sold" for="brand">
            <div class="grid grid-cols-2 gap-2">
              <x-hub::input.text placeholder="From" />
              <x-hub::input.text placeholder="To" />
            </div>
          </x-hub::input.group>

          <x-hub::input.group label="Price Range" for="brand">
            <div class="grid grid-cols-2 gap-2">
              <x-hub::input.text placeholder="From" />
              <x-hub::input.text placeholder="To" />
            </div>
          </x-hub::input.group>

        </div>
      </div>
    </x-slot>
    <x-slot name="head">
      <x-hub::table.heading>
        <x-hub::input.checkbox wire:model="selectPage" />
      </x-hub::table.heading>
      <x-hub::table.heading>
      </x-hub::table.heading>
      <x-hub::table.heading>
        {{ __('adminhub::global.name') }}
      </x-hub::table.heading>
      <x-hub::table.heading>
        Brand
      </x-hub::table.heading>
      <x-hub::table.heading>
        SKU
      </x-hub::table.heading>
      <x-hub::table.heading>
        Type
      </x-hub::table.heading>
      <x-hub::table.heading>
        Stock
      </x-hub::table.heading>
      <x-hub::table.heading></x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @if($selectPage)
        <x-hub::table.row class="border-b bg-indigo-50">
          <x-hub::table.cell colspan="24">
            @unless($selectAll)
              <span class="text-sm text-indigo-800">You have selected <strong>{{ count($selected) }}</strong> products, do you want to select all <strong>{{ $products->total() }}</strong>?</span>
              <button wire:click="selectAll" class="ml-1 text-blue-700 hover:underline">Select all</button>
            @else
              <span class="text-sm text-indigo-800">You have selected all <strong>{{ $products->total() }}</strong> products.</span>
            @endif

          </x-hub::table.cell>
        </x-hub::table.row>
      @endif
      @forelse($products as $product)
      <x-hub::table.row wire:key="row-{{ $product->id }}">
        <x-hub::table.cell>
          <x-hub::input.checkbox wire:model="selected" :value="$product->id" />
        </x-hub::table.cell>

        <x-hub::table.cell class="w-24">
          @if($product->thumbnail)
            <img class="rounded shadow" src="{{ $product->thumbnail->getUrl('small') }}" />
          @else
              <x-hub::icon ref="photograph" class="w-8 h-8 mx-auto text-gray-300" />
          @endif
        </x-hub::table.cell>
        <x-hub::table.cell>
          {{ $product->translateAttribute('name') }}
        </x-hub::table.cell>

        <x-hub::table.cell>
          {{ $product->brand }}
        </x-hub::table.cell>

        <x-hub::table.cell>
          @if($product->variants->count() > 2)
            <x-hub::tooltip text="{{ $product->variants->pluck('sku')->join(', ') }}" left>
              Multiple
            </x-hub::tooltip>
          @else
            {{ $product->variants->pluck('sku')->join(', ') }}
          @endif
        </x-hub::table.cell>

        <x-hub::table.cell>
          {{ $product->productType->name }}
        </x-hub::table.cell>

        <x-hub::table.cell>
          {{ $product->variants->sum('stock') }}
        </x-hub::table.cell>

        <x-hub::table.cell>
            <a href="{{ route('hub.products.show', $product->id) }}" class="text-indigo-500 hover:underline">
              Edit
            </a>
        </x-hub::table.cell>
      </x-hub::table.row>
      @empty
        <x-hub::table.no-results>
          Unable to find products matching search/filters.
        </x-hub::table.no-results>
      @endforelse
    </x-slot>
  </x-hub::table>
  <div>
    {{ $products->links() }}
  </div>
</div>
