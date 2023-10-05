<div class="shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6 sm:rounded-md">
        <header>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                {{ __('adminhub::partials.discounts.limitations.heading') }}
            </h3>
        </header>

        <div class="space-y-4">

            <header class="flex items-center justify-between">
                <h4 class="text-md font-medium text-gray-700">
                    {{ __('adminhub::partials.discounts.limitations.by_collection') }}
                </h4>

                @livewire('hub.components.collection-search', [
                    'existing' => $selectedCollections->pluck('id'),
                ])
            </header>

            <div class="space-y-2">
                @foreach ($selectedCollections as $index => $collection)
                    <div wire:key="collection_{{ $index }}">
                        <div class="flex items-center px-4 py-2 text-sm border rounded">
                            @if ($collection['thumbnail'])
                                <span class="flex-shrink-0 block w-12 mr-4">
                                    <img src="{{ $collection['thumbnail'] }}"
                                         class="rounded">
                                </span>
                            @endif

                            <div class="flex grow">
                                <div class="grow flex gap-1.5 flex-wrap items-center">
                                    <strong class="rounded px-1.5 py-0.5 bg-sky-50 text-xs text-sky-500">
                                        {{ $collection['group_name'] }}
                                    </strong>

                                    @if (count($collection['breadcrumb']))
                                        <span class="text-gray-500 flex gap-1.5 items-center">
                                            <span class="font-medium">
                                                {{ collect($collection['breadcrumb'])->first() }}
                                            </span>

                                            <x-hub::icon ref="chevron-right"
                                                         class="w-4 h-4"
                                                         style="solid" />
                                        </span>
                                    @endif

                                    @if (count($collection['breadcrumb']) > 1)
                                        <span class="text-gray-500 flex gap-1.5 items-center"
                                              title="{{ collect($collection['breadcrumb'])->implode(' > ') }}">
                                            <span class="font-medium cursor-help">
                                                ...
                                            </span>

                                            <x-hub::icon ref="chevron-right"
                                                         class="w-4 h-4"
                                                         style="solid" />
                                        </span>
                                    @endif

                                    <strong class="text-gray-700 truncate max-w-[40ch]"
                                            title="{{ $collection['name'] }}">
                                        {{ $collection['name'] }}
                                    </strong>
                                </div>

                                <div class="flex items-center">
                                    <x-hub::dropdown minimal>
                                        <x-slot name="options">
                                            <x-hub::dropdown.link class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50"
                                                                  :href="route('hub.collections.show', [
                                                                      'group' => $collection['group_id'],
                                                                      'collection' => $collection['id'],
                                                                  ])">
                                                {{ __('adminhub::partials.products.collections.view_collection') }}
                                            </x-hub::dropdown.link>

                                            <x-hub::dropdown.button wire:click.prevent="removeCollection({{ $index }})"
                                                                    class="flex items-center justify-between px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                {{ __('adminhub::global.remove') }}
                                            </x-hub::dropdown.button>
                                        </x-slot>
                                    </x-hub::dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <header class="flex items-center justify-between border-t pt-4">
                <h4 class="text-md font-medium text-gray-700">
                    {{ __('adminhub::partials.discounts.limitations.by_brand') }}
                </h4>

                @livewire('hub.components.brand-search', [
                    'existing' => $selectedBrands->pluck('id'),
                ])
            </header>

            <div class="space-y-2">
                @foreach ($selectedBrands as $index => $brand)
                    <div wire:key="brand_{{ $index }}">
                        <div class="flex items-center px-4 py-2 text-sm border rounded">

                            <div class="flex grow">
                                <div class="grow flex gap-1.5 flex-wrap items-center">
                                    <strong class="text-gray-700 truncate max-w-[40ch]"
                                            title="{{ $brand['name'] }}">
                                        {{ $brand['name'] }}
                                    </strong>
                                </div>

                                <div class="flex items-center">
                                    <x-hub::dropdown minimal>
                                        <x-slot name="options">
                                            <x-hub::dropdown.link class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50"
                                                                  :href="route('hub.brands.show', [
                                                                      'brand' => $brand['id'],
                                                                  ])">
                                                {{ __('adminhub::partials.discounts.limitations.view_brand') }}
                                            </x-hub::dropdown.link>

                                            <x-hub::dropdown.button wire:click.prevent="removeBrand({{ $index }})"
                                                                    class="flex items-center justify-between px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                {{ __('adminhub::global.remove') }}
                                            </x-hub::dropdown.button>
                                        </x-slot>
                                    </x-hub::dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <header class="flex items-center justify-between border-t pt-4">
                <h4 class="text-md font-medium text-gray-700">
                    {{ __('adminhub::partials.discounts.limitations.by_product') }}
                </h4>

                @livewire('hub.components.product-search', [
                    'existing' => $selectedProducts->map(fn ($product) => ['id' => $product['id']]),
                    'ref' => 'discount-limitations',
                ])
            </header>

            <div class="space-y-2">
                @foreach ($selectedProducts as $index => $product)
                    <div wire:key="product_{{ $index }}">
                        <div class="flex items-center px-4 py-2 text-sm border rounded">

                            <div class="flex grow">
                                <div class="grow flex gap-1.5 flex-wrap items-center">
                                    <strong class="text-gray-700 truncate max-w-[40ch]"
                                            title="{{ $product['name'] }}">
                                        {{ $product['name'] }}
                                    </strong>
                                </div>

                                <div class="flex items-center">
                                    <x-hub::dropdown minimal>
                                        <x-slot name="options">
                                            <x-hub::dropdown.link class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50"
                                                                  :href="route('hub.products.show', [
                                                                      'product' => $product['id'],
                                                                  ])">
                                                {{ __('adminhub::partials.discounts.limitations.view_product') }}
                                            </x-hub::dropdown.link>

                                            <x-hub::dropdown.button wire:click.prevent="removeProduct({{ $index }})"
                                                                    class="flex items-center justify-between px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                {{ __('adminhub::global.remove') }}
                                            </x-hub::dropdown.button>
                                        </x-slot>
                                    </x-hub::dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <header class="flex items-center justify-between border-t pt-4">
                <h4 class="text-md font-medium text-gray-700">
                    {{ __('adminhub::partials.discounts.limitations.by_product_variant') }}
                </h4>

                @livewire('hub.components.product-variant-search', [
                    'existing' => $selectedProductVariants->map(fn ($variant) => ['id' => $variant['id']]),
                    'ref' => 'discount-limitations',
                ])
            </header>

            <div class="space-y-2">
                @foreach ($selectedProductVariants as $index => $variant)
                    <div wire:key="variant_{{ $index }}">
                        <div class="flex items-center px-4 py-2 text-sm border rounded">

                            <div class="flex grow">
                                <div class="grow flex gap-1.5 flex-wrap items-center">
                                    <strong class="text-gray-700 truncate max-w-[40ch]"
                                            title="{{ $variant['name'] }}">
                                        {{ $variant['name'] }}
                                    </strong>
                                </div>

                                <div class="flex items-center">
                                    <x-hub::dropdown minimal>
                                        <x-slot name="options">
                                            <x-hub::dropdown.link class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50"
                                                                  :href="route('hub.products.variants.show', [
                                                                      'variant' => $variant['id'],
                                                                  ])">
                                                {{ __('adminhub::partials.discounts.limitations.view_product_variant') }}
                                            </x-hub::dropdown.link>

                                            <x-hub::dropdown.button wire:click.prevent="removeProductVariant({{ $index }})"
                                                                    class="flex items-center justify-between px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                {{ __('adminhub::global.remove') }}
                                            </x-hub::dropdown.button>
                                        </x-slot>
                                    </x-hub::dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>
