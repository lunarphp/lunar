<div class="flex-col px-12 space-y-4">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">
        {{ __('adminhub::components.products.index.title') }}
    </strong>
    <div class="text-right">
      <x-hub::button tag="a" href="{{ route('hub.products.create') }}">{{ __('adminhub::components.products.index.create_product') }}</x-hub::button>
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

            <x-hub::button theme="gray" class="inline-flex items-center" @click.prevent="filtersVisible = !filtersVisible">
              <x-hub::icon ref="filter" class="w-4 mr-1" />
              Filter
            </x-hub::button>
          </div>
        </div>

        <div class="grid grid-cols-4 gap-4" x-show="filtersVisible" x-cloak>
          <x-hub::input.group label="Status" for="brand">
            <x-hub::input.select wire:model="filters.status">
              <option value>{{ __('adminhub::global.any') }}</option>
              <option value="published">{{ __('adminhub::global.published') }}</option>
              <option value="draft">{{ __('adminhub::global.draft') }}</option>
            </x-hub::input.select>
          </x-hub::input.group>

          <x-hub::input.group label="Show Deleted" for="brand">
            <x-hub::input.toggle wire:model="filters.soft_deleted" />
          </x-hub::input.group>

        </div>
      </div>
    </x-slot>
    <x-slot name="head">
      <x-hub::table.heading>
        <x-hub::input.checkbox wire:model="selectPage" />
      </x-hub::table.heading>
      <x-hub::table.heading>
        Status
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
              <span class="text-sm text-indigo-800">{{ __('adminhub::components.products.index.selected_products', [
                'count' => count($selected),
              ]); }}
                <strong>{{ $products->total() }}</strong>?</span>
              <button wire:click="selectAll" class="ml-1 text-blue-700 hover:underline">{{ __('adminhub::components.products.index.select_all_btn') }}</button>
            @else
              <span class="text-sm text-indigo-800">{{ __('adminhub::components.products.index.you_have_selected_all', [
                'count' => $products->total(),
              ]) }}
            </span>
            @endif
          </x-hub::table.cell>
        </x-hub::table.row>
      @endif
      @if($this->filters['soft_deleted'] ?? false)
        <x-hub::table.row class="border-b !bg-red-50">
          <x-hub::table.cell colspan="24">
              <span class="text-sm text-red-800">
                {{ __('adminhub::components.products.index.only_deleted_visible') }}
              </span>
          </x-hub::table.cell>
        </x-hub::table.row>
      @endif
      @forelse($products as $product)
      <x-hub::table.row wire:key="row-{{ $product->id }}">
        <x-hub::table.cell>
          <x-hub::input.checkbox wire:model="selected" :value="$product->id" />
        </x-hub::table.cell>

        <x-hub::table.cell>
          <span
            @class([
              'text-xs inline-block py-1 px-2 rounded',
              'text-green-600 bg-green-50' => $product->status == 'published' && !$product->deleted_at,
              'text-yellow-600 bg-yellow-50' => $product->status == 'draft' && !$product->deleted_at,
              'text-red-600 bg-red-50' => $product->deleted_at,
            ])
          >
            {{ __('adminhub::components.products.index.' .  ($product->deleted_at ? 'deleted' : $product->status)) }}
          </span>
        </x-hub::table.cell>

        <x-hub::table.cell class="w-24">
          @if($thumbnail = $this->getThumbnail($product))
            <img class="rounded shadow" src="{{ $thumbnail->getUrl('small') }}" loading="lazy" />
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
          @if($product->deleted_at)
            <x-hub::button
              size="xs"
              theme="gray"
              type="button"
              wire:click="restoreProduct({{ $product->id }})"
            >
              {{ __('adminhub::global.restore') }}
            </x-hub::button>
          @else
            <a href="{{ route('hub.products.show', $product->id) }}" class="text-indigo-500 hover:underline">
                {{ __('adminhub::global.edit') }}
            </a>
          @endif
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
