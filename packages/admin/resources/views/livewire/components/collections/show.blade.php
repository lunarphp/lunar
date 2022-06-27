<div>
    <div class="flex items-center space-x-4">
        <a href="{{ route($collection->parent_id ? 'hub.collections.show' : 'hub.collection-groups.show', [
            'group' => $collection->group,
            'collection' => $collection->parent_id,
        ]) }}"
           class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white"
           title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
            <x-hub::icon ref="chevron-left"
                         style="solid"
                         class="w-8 h-8" />
        </a>

        <h1 class="text-xl font-bold md:text-xl">
            {{ $collection->translateAttribute('name') }}
        </h1>
    </div>

    <form wire:submit.prevent="save"
          class="fixed bottom-0 right-0 left-auto z-40 p-6 border-t bg-white/75"
          :class="{ 'w-[calc(100vw_-_12rem)]': showExpandedMenu, 'w-[calc(100vw_-_5rem)]': !showExpandedMenu }">
        <div class="flex justify-end">
            <x-hub::button type="submit">
                {{ __('adminhub::catalogue.collections.show.save_btn') }}
            </x-hub::button>
        </div>
    </form>

    <div class="py-12 pb-24 lg:grid lg:grid-cols-12 lg:gap-x-12">
        <div class="sm:px-6 lg:px-0 lg:col-span-9">
            <div class="space-y-6">
                <div id="attributes">
                    @include('adminhub::partials.attributes')
                </div>

                <div id="images">
                    @include('adminhub::partials.image-manager', [
                        'existing' => $images,
                        'wireModel' => 'imageUploadQueue',
                        'filetypes' => ['image/*'],
                    ])
                </div>

                <div id="availability">
                    @include('adminhub::partials.availability', [
                        'type' => 'collection',
                        'channels' => true,
                        'customerGroups' => [
                            'purchasable' => false,
                        ],
                    ])
                </div>

                <div id="urls">
                    @include('adminhub::partials.urls')
                </div>

                <div class="shadow sm:rounded-md"
                     id="products">
                    <div class="flex-col px-4 py-5 space-y-4 bg-white rounded sm:p-6">
                        <header class="flex items-center justify-between">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                {{ __('adminhub::menu.products') }}
                            </h3>

                            <div>
                                @livewire('hub.components.product-search', [
                                    'existing' => collect($products),
                                ])
                            </div>
                        </header>

                        @if ($products->count())
                            <div class="grid items-center grid-cols-2 gap-4">
                                <x-hub::input.group label="Sort Products"
                                                    for="sortProducts">
                                    <x-hub::input.select wire:model="collection.sort">
                                        <option value="base_price:asc">
                                            {{ __('adminhub::catalogue.collections.show.sort.base_price_asc') }}
                                        </option>

                                        <option value="base_price:desc">
                                            {{ __('adminhub::catalogue.collections.show.sort.base_price_desc') }}
                                        </option>

                                        <option value="sku:asc">
                                            {{ __('adminhub::catalogue.collections.show.sort.sku_asc') }}
                                        </option>

                                        <option value="sku:desc">
                                            {{ __('adminhub::catalogue.collections.show.sort.sku_desc') }}
                                        </option>

                                        <option value="custom">
                                            {{ __('adminhub::catalogue.collections.show.sort.custom') }}
                                        </option>
                                    </x-hub::input.select>
                                </x-hub::input.group>
                            </div>
                        @endif

                        <div wire:sort
                             sort.options='{group: "products", method: "sortProducts"}'
                             class="space-y-2">
                            @forelse($products as $product)
                                <div sort.item="products"
                                     sort.id="{{ $product['id'] }}"
                                     wire:key="product_{{ $product['id'] }}"
                                     class="flex items-center">
                                    @if ($collection->sort == 'custom')
                                        <div sort.handle
                                             class="cursor-grab">
                                            <x-hub::icon ref="selector"
                                                         style="solid"
                                                         class="mr-2 text-gray-400 hover:text-gray-700" />
                                        </div>
                                    @endif

                                    <div
                                         class="flex items-center justify-between w-full px-3 py-3 text-sm bg-white border rounded sort-item-element">
                                        <div class="flex items-center">
                                            @if ($product['thumbnail'])
                                                <img src="{{ $product['thumbnail'] }}"
                                                     class="w-6 mr-2 rounded" />
                                            @endif

                                            <div>
                                                {{ $product['name'] }}
                                            </div>
                                        </div>

                                        <div class="w-48 text-xs text-right text-gray-500">
                                            @if (strpos($collection->sort, 'base_price') !== false)
                                                {{ $product['base_price'] }}
                                            @elseif(strpos($collection->sort, 'sku') !== false)
                                                <div class="truncate">{{ $product['sku'] }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="ml-4">
                                        <button type="button"
                                                wire:click.prevent="removeProduct('{{ $product['id'] }}')">
                                            <x-hub::icon ref="trash"
                                                         class="w-4 h-4 text-gray-400 hover:text-red-600" />
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <x-hub::alert>
                                    {{ __('adminhub::catalogue.collections.show.no_products') }}
                                </x-hub::alert>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="shadow sm:rounded-md"
                     id="collections">
                    <div class="flex-col px-4 py-5 space-y-4 bg-white rounded sm:p-6">
                        <header class="flex items-center justify-between">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                {{ __('adminhub::menu.collections') }}
                            </h3>
                        </header>

                        <div class="space-y-2">
                            @forelse($collection->children as $child)
                                <div
                                     class="flex items-center justify-between w-full px-3 py-3 text-sm bg-white border rounded">
                                    {{ $child->translateAttribute('name') }}

                                    <a href="{{ route('hub.collections.show', [
                                        'group' => $collection->group,
                                        'collection' => $child,
                                    ]) }}"
                                       class="text-sm text-blue-500 hover:underline">
                                        {{ __('adminhub::global.edit') }}
                                    </a>
                                </div>
                            @empty
                                <x-hub::alert>
                                    {{ __('adminhub::catalogue.collections.show.no_children') }}
                                </x-hub::alert>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <aside class="fixed hidden px-2 py-6 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3 md:block">
                <nav class="space-y-2"
                     aria-label="Sidebar">
                    @foreach ($this->sideMenu as $item)
                        <a href="#{{ $item['id'] }}"
                           class="@if (!empty($item['has_errors'])) text-red-600 @else text-gray-900 @endif flex items-center text-sm font-medium bg-gray-100 rounded-md hover:text-indigo-500 hover:underline group"
                           aria-current="page">
                            @if (!empty($item['has_errors']))
                                <x-hub::icon ref="exclamation-circle"
                                             class="w-4 mr-1 text-red-600" />
                            @endif

                            <span class="truncate">
                                {{ $item['title'] }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </aside>
        </div>
    </div>
</div>
