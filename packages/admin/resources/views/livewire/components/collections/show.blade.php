<div>
    <div class="flex items-center gap-4">
        <a href="{{ route($collection->parent_id ? 'hub.collections.show' : 'hub.collection-groups.show', [
            'group' => $collection->group,
            'collection' => $collection->parent_id,
        ]) }}"
           class="text-gray-600 rounded bg-gray-50 hover:bg-sky-500 hover:text-white"
           title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
            <x-hub::icon ref="chevron-left"
                         style="solid"
                         class="w-8 h-8" />
        </a>

        <h1 class="text-xl font-bold md:text-xl">
            {{ $collection->translateAttribute('name') }}
        </h1>
    </div>

    <x-hub::layout.bottom-panel>
        <form wire:submit.prevent="save">
            <div class="flex justify-end">
                <x-hub::button type="submit">
                    {{ __('adminhub::catalogue.collections.show.save_btn') }}
                </x-hub::button>
            </div>
        </form>
    </x-hub::layout.bottom-panel>

    <div class="pb-24 mt-8 lg:gap-8 lg:flex lg:items-start">
        <div class="space-y-6 lg:flex-1">
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
                         sort.options='{ group: "products", method: "sortProducts" }'
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
                                    <div class="flex items-center gap-4">
                                        @if ($product['thumbnail'])
                                            <x-hub::thumbnail :src="$product['thumbnail']" />
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
                            @if(!$products->count() && $this->productCount >= 30)
                                <div wire:loading.remove wire:target="loadProducts">
                                    <x-hub::button theme="gray" type="button" wire:click="loadProducts">Load {{ $this->productCount }} products</x-hub::button>
                                </div>
                                <div wire:loading wire:target="loadProducts">
                                    <x-hub::loading-indicator class="w-4" />
                                </div>
                            @else
                                <x-hub::alert>
                                    {{ __('adminhub::catalogue.collections.show.no_products') }}
                                </x-hub::alert>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>


            <x-hub::modal.dialog wire:model="showCreateChildForm"
                                 form="createChildCollection">
                <x-slot name="title">
                        {{ __('adminhub::catalogue.collections.create.child.title', [
                            'parent' => $collection->translateAttribute('name'),
                        ]) }}
                </x-slot>

                <x-slot name="content">
                    <div class="space-y-4">
                        <x-hub::input.group :label="__('adminhub::inputs.name')"
                                            for="name"
                                            :error="$errors->first('childCollection.name')"
                                            required>
                            <x-hub::input.text wire:model="childCollection.name"
                                               :error="$errors->first('childCollection.name')" />
                        </x-hub::input.group>

                        @if ($this->slugIsRequired)
                            <x-hub::input.group :label="__('adminhub::inputs.slug.label')"
                                                for="slug"
                                                :error="$errors->first('slug')"
                                                required>
                                <x-hub::input.text wire:model.lazy="slug"
                                                   :error="$errors->first('slug')" />
                            </x-hub::input.group>
                        @endif
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <x-hub::button type="button"
                                   wire:click.prevent="$set('showCreateChildForm', false)"
                                   theme="gray">
                        {{ __('adminhub::global.cancel') }}
                    </x-hub::button>

                    <x-hub::button type="submit">
                        {{ __('adminhub::catalogue.collections.create.btn') }}
                    </x-hub::button>
                </x-slot>
            </x-hub::modal.dialog>

            <div class="shadow sm:rounded-md"
                 id="collections">
                <div class="flex-col px-4 py-5 space-y-4 bg-white rounded sm:p-6">
                    <header class="flex items-center justify-between">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            {{ __('adminhub::menu.collections') }}
                        </h3>
                        
                        <x-hub::button wire:click.prevent="$set('showCreateChildForm', true)">
                            {{ __('adminhub::catalogue.collections.groups.node.add_child') }}
                        </x-hub::button>
                    </header>

                    <div class="space-y-2"
                        wire:sort
                        sort.options='{group: "collection_child", method: "sort"}'>
                        @forelse($children as $child)
                            <div
                                wire:key="child_{{ $child['id'] }}"
                                sort.item="collection_child"
                                sort.id="{{ $child['id'] }}"
                                class="flex items-center">
                                <div wire:loading
                                    wire:target="sort">
                                    <x-hub::icon ref="refresh"
                                                style="solid"
                                                class="w-5 mr-2 text-gray-300 rotate-180 animate-spin" />
                                </div>

                                <div wire:loading.remove
                                    wire:target="sort">
                                    <div sort.handle
                                        class="cursor-grab">
                                        <x-hub::icon ref="selector"
                                                    style="solid"
                                                    class="mr-2 text-gray-400 hover:text-gray-700" />
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-between w-full px-3 py-3 text-sm bg-white border rounded sort-item-element">
                                    {{ $child->translateAttribute('name') }}

                                    <a href="{{ route('hub.collections.show', [
                                        'group' => $collection->group,
                                        'collection' => $child,
                                    ]) }}"
                                    class="text-sm text-sky-500 hover:underline">
                                        {{ __('adminhub::global.edit') }}
                                    </a>
                                </div>
                            </div>
                        @empty
                            <x-hub::alert>
                                {{ __('adminhub::catalogue.collections.show.no_children') }}
                            </x-hub::alert>
                        @endforelse
                    </div>
                </div>
            </div>

            @if ($collection->id)
                <div class="bg-white border rounded shadow border-red-300">
                    <header class="px-6 py-4 bg-white border-b rounded-t border-red-300 text-red-700">
                        {{ __('adminhub::inputs.danger_zone.title') }}
                    </header>

                    <div class="p-6 text-sm">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 lg:col-span-8">
                                <strong>
                                    {{ __('adminhub::catalogue.collections.delete.title') }}
                                </strong>

                                <p class="text-xs text-gray-600">
                                    {{ __('adminhub::catalogue.collections.delete.warning') }}
                                </p>
                            </div>

                            <div class="col-span-6 text-right lg:col-span-4">
                                <x-hub::button :disabled="false"
                                            wire:click="$set('showDeleteConfirm', true)"
                                            type="button"
                                            theme="danger">
                                    {{ __('adminhub::global.delete') }}
                                </x-hub::button>
                            </div>
                        </div>
                    </div>
                </div>

                <x-hub::modal.dialog wire:model="showDeleteConfirm">
                    <x-slot name="title">
                        {{ __('adminhub::catalogue.collections.delete.title') }}
                    </x-slot>

                    <x-slot name="content">
                        @if ($childCount = $children->count())
                            <x-hub::alert level="danger">
                                {{ __('adminhub::catalogue.collections.delete.child.warning', [
                                    'count' => $childCount,
                                ]) }}
                            </x-hub::alert>
                        @else
                            <p>{{ __('adminhub::catalogue.collections.delete.root.warning') }}</p>
                        @endif
                    </x-slot>

                    <x-slot name="footer">
                        <div class="flex items-center justify-end space-x-4">
                            <x-hub::button theme="gray"
                                           type="button"
                                           wire:click.prevent="$set('showDeleteConfirm', false)">
                                {{ __('adminhub::global.cancel') }}
                            </x-hub::button>

                            <x-hub::button wire:click="deleteCollection"
                                           theme="danger">
                                {{ __('adminhub::catalogue.collections.delete.btn') }}
                            </x-hub::button>
                        </div>
                    </x-slot>
                </x-hub::modal.dialog>
            @endif
        </div>

        <x-hub::layout.page-menu>
            <nav class="space-y-2"
                 aria-label="{{ __('adminhub:global.tabs') }}"
                 x-data="{ activeAnchorLink: '' }"
                 x-init="activeAnchorLink = window.location.hash">
                @foreach ($this->sideMenu as $item)
                    <a href="#{{ $item['id'] }}"
                       @class([
                           'flex items-center gap-2 p-2 rounded text-gray-500',
                           'hover:bg-sky-50 hover:text-sky-700' => empty($item['has_errors']),
                           'text-red-600 bg-red-50' => !empty($item['has_errors']),
                       ])
                       aria-current="page"
                       x-data="{ linkId: '#{{ $item['id'] }}' }"
                       :class="{
                           'bg-sky-50 text-sky-700 hover:text-sky-600': linkId === activeAnchorLink
                       }"
                       x-on:click="activeAnchorLink = linkId">
                        @if (!empty($item['has_errors']))
                            <x-hub::icon ref="exclamation-circle"
                                         class="w-4 text-red-600" />
                        @endif

                        <span class="text-sm font-medium">
                            {{ $item['title'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </x-hub::layout.page-menu>
    </div>
</div>
