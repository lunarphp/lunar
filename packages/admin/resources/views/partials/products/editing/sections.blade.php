<div class="flex justify-between items-center">
    <div class="flex items-center gap-4">
        <a href="{{ route('hub.products.index') }}"
           class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white"
           title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
            <x-hub::icon ref="chevron-left"
                         style="solid"
                         class="w-8 h-8" />
        </a>

        <h1 class="text-xl font-bold md:text-xl">
            @if ($product->id)
                {{ $product->translateAttribute('name') }}
            @else
                {{ __('adminhub::global.new_product') }}
            @endif
        </h1>
    </div>
    <div>
        <x-hub::model-url :model="$product" :preview="$product->status == 'draft'" />
    </div>
</div>

<x-hub::layout.bottom-panel>
    <form wire:submit.prevent="save">
        <div class="flex justify-end gap-6">
            @include('adminhub::partials.products.status-bar')

            <x-hub::button type="submit">
                {{ __('adminhub::catalogue.products.show.save_btn') }}
            </x-hub::button>
        </div>
    </form>
</x-hub::layout.bottom-panel>

<div class="pb-24 mt-8 lg:gap-8 lg:flex lg:items-start">
    <div class="space-y-6 lg:flex-1">
        <div class="space-y-6">
            @if (!$this->hasChannelAvailability)
                <div>
                    <x-hub::alert level="danger">
                        {{ __('adminhub::catalogue.products.show.no_channel_availability') }}
                    </x-hub::alert>
                </div>
            @endif

            @foreach ($this->getSlotsByPosition('top') as $slot)
                <div id="{{ $slot->handle }}">
                    <div>
                        @livewire($slot->component, ['slotModel' => $product], key('top-slot-' . $slot->handle))
                    </div>
                </div>
            @endforeach

            <div id="basic-information">
                @include('adminhub::partials.products.editing.basic-information')
            </div>

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
                    'channels' => true,
                    'customerGroups' => true,
                ])
            </div>

            @if (!$this->variantsDisabled)
                <div id="variants">
                    @include('adminhub::partials.products.editing.variants')
                </div>
            @endif

            @if ($this->getVariantsCount() <= 1)
                <div id="pricing">
                    @include('adminhub::partials.pricing')
                </div>

                <div id="identifiers">
                    @include('adminhub::partials.products.variants.identifiers')
                </div>

                <div id="inventory">
                    @include('adminhub::partials.products.variants.inventory')
                </div>

                <div id="shipping">
                    @include('adminhub::partials.shipping')
                </div>

                @if ($this->getVariantsCount() == 1)
                    @foreach ($this->getSlotsByPosition('variant') as $slot)
                        <div id="{{ $slot->handle }}">
                            <div>
                                @livewire($slot->component, ['slotModel' => $variant], key('variant-slot-' . $slot->handle))
                            </div>
                        </div>
                    @endforeach
                @endif
            @endif

            <div id="urls">
                @include('adminhub::partials.urls')
            </div>

            <div id="associations">
                @include('adminhub::partials.products.editing.associations')
            </div>

            <div id="collections">
                @include('adminhub::partials.products.editing.collections')
            </div>

            @foreach ($this->getSlotsByPosition('bottom') as $slot)
                <div id="{{ $slot->handle }}">
                    <div>
                        @livewire($slot->component, ['slotModel' => $product], key('bottom-slot-' . $slot->handle))
                    </div>
                </div>
            @endforeach

            @if ($product->id)
                <div
                    @class([
                        'bg-white border rounded shadow',
                        'border-red-300' => !$product->deleted_at,
                        'border-gray-300' => $product->deleted_at,
                    ])
                >
                    <header
                        @class([
                            'px-6 py-4 bg-white border-b rounded-t',
                            'border-red-300 text-red-700' => !$product->deleted_at,
                            'border-gray-300 text-gray-700' => $product->deleted_at,
                        ])
                    >
                        @if($product->deleted_at)
                            {{ __('adminhub::inputs.restore_zone.title') }}
                        @else
                            {{ __('adminhub::inputs.danger_zone.title') }}
                        @endif

                    </header>

                    <div class="p-6 text-sm">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 lg:col-span-8">
                                <strong>
                                    @if($product->deleted_at)
                                        {{ __('adminhub::inputs.restore_zone.label', ['model' => __('adminhub::types.product')] }}
                                    @else
                                        {{ __('adminhub::inputs.danger_zone.label', ['model' => __('adminhub::types.product')]) }}
                                    @endif
                                </strong>

                                <p class="text-xs text-gray-600">
                                    @if($product->deleted_at)
                                        {{ __('adminhub::catalogue.products.show.restore_strapline') }}
                                    @else
                                        {{ __('adminhub::catalogue.products.show.delete_strapline') }}
                                    @endif

                                </p>
                            </div>

                            <div class="col-span-6 text-right lg:col-span-4">
                                @if($product->deleted_at)
                                    <x-hub::button :disabled="false"
                                                   wire:click="$set('showRestoreConfirm', true)"
                                                   type="button"
                                                   theme="green">
                                        {{ __('adminhub::global.restore') }}
                                    </x-hub::button>
                                @else
                                    <x-hub::button :disabled="false"
                                                   wire:click="$set('showDeleteConfirm', true)"
                                                   type="button"
                                                   theme="danger">
                                        {{ __('adminhub::global.delete') }}
                                    </x-hub::button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <x-hub::modal.dialog wire:model="showRestoreConfirm">
                    <x-slot name="title">
                        {{ __('adminhub::catalogue.products.show.restore_title') }}
                    </x-slot>

                    <x-slot name="content">
                        {{ __('adminhub::catalogue.products.show.restore_strapline') }}
                    </x-slot>

                    <x-slot name="footer">
                        <div class="flex items-center justify-end space-x-4">
                            <x-hub::button theme="gray"
                                           type="button"
                                           wire:click.prevent="$set('showRestoreConfirm', false)">
                                {{ __('adminhub::global.cancel') }}
                            </x-hub::button>

                            <x-hub::button wire:click="restore"
                                           theme="green">
                                {{ __('adminhub::catalogue.products.show.restore_btn') }}
                            </x-hub::button>
                        </div>
                    </x-slot>
                </x-hub::modal.dialog>

                <x-hub::modal.dialog wire:model="showDeleteConfirm">
                    <x-slot name="title">
                        {{ __('adminhub::catalogue.products.show.delete_title') }}
                    </x-slot>

                    <x-slot name="content">
                        {{ __('adminhub::catalogue.products.show.delete_strapline') }}
                    </x-slot>

                    <x-slot name="footer">
                        <div class="flex items-center justify-end space-x-4">
                            <x-hub::button theme="gray"
                                           type="button"
                                           wire:click.prevent="$set('showDeleteConfirm', false)">
                                {{ __('adminhub::global.cancel') }}
                            </x-hub::button>

                            <x-hub::button wire:click="delete"
                                           theme="danger">
                                {{ __('adminhub::catalogue.products.show.delete_btn') }}
                            </x-hub::button>
                        </div>
                    </x-slot>
                </x-hub::modal.dialog>
            @endif

            <div class="pt-12 mt-12 border-t">
                @livewire('hub.components.activity-log-feed', [
                    'subject' => $product,
                ])
            </div>
        </div>
    </div>

    <x-hub::layout.page-menu>
        <nav class="space-y-2"
             aria-label="Sidebar"
             x-data="{ activeAnchorLink: '' }"
             x-init="activeAnchorLink = window.location.hash">
            @foreach ($this->getSlotsByPosition('top') as $slot)
                <a href="#{{ $slot->handle }}"
                   @class([
                       'flex items-center gap-2 p-2 rounded text-gray-500',
                       'hover:bg-blue-50 hover:text-blue-700' => empty(
                           $this->getSlotErrorsByHandle($slot->handle)
                       ),
                       'text-red-600 bg-red-50' => !empty(
                           $this->getSlotErrorsByHandle($slot->handle)
                       ),
                   ])
                   aria-current="page"
                   x-data="{ linkId: '#{{ $slot->handle }}' }"
                   :class="{
                       'bg-blue-50 text-blue-700 hover:text-blue-600': linkId === activeAnchorLink
                   }"
                   x-on:click="activeAnchorLink = linkId">
                    @if (!empty($this->getSlotErrorsByHandle($slot->handle)))
                        <x-hub::icon ref="exclamation-circle"
                                     class="w-4 text-red-600" />
                    @endif

                    <span class="text-sm font-medium">
                        {{ $slot->title }}
                    </span>
                </a>
            @endforeach

            @foreach ($this->sideMenu as $item)
                <a href="#{{ $item['id'] }}"
                   @class([
                       'flex items-center gap-2 p-2 rounded text-gray-500',
                       'hover:bg-blue-50 hover:text-blue-700' => empty($item['has_errors']),
                       'text-red-600 bg-red-50' => !empty($item['has_errors']),
                   ])
                   aria-current="page"
                   x-data="{ linkId: '#{{ $item['id'] }}' }"
                   :class="{
                       'bg-blue-50 text-blue-700 hover:text-blue-600': linkId === activeAnchorLink
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

            @foreach ($this->getSlotsByPosition('bottom') as $slot)
                <a href="#{{ $slot->handle }}"
                   @class([
                       'flex items-center gap-2 p-2 rounded text-gray-500',
                       'hover:bg-blue-50 hover:text-blue-700' => empty(
                           $this->getSlotErrorsByHandle($slot->handle)
                       ),
                       'text-red-600 bg-red-50' => !empty(
                           $this->getSlotErrorsByHandle($slot->handle)
                       ),
                   ])
                   aria-current="page"
                   x-data="{ linkId: '#{{ $slot->handle }}' }"
                   :class="{
                       'bg-blue-50 text-blue-700 hover:text-blue-600': linkId === activeAnchorLink
                   }"
                   x-on:click="activeAnchorLink = linkId">
                    @if (!empty($this->getSlotErrorsByHandle($slot->handle)))
                        <x-hub::icon ref="exclamation-circle"
                                     class="w-4 text-red-600" />
                    @endif

                    <span class="text-sm font-medium">
                        {{ $slot->title }}
                    </span>
                </a>
            @endforeach
        </nav>
    </x-hub::layout.page-menu>
</div>
