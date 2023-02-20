@section('menu')
    <x-hub::layout.side-menu>
        @livewire('hub.components.products.variants.side-menu', [
            'product' => $product,
            'variant' => $variant,
        ])
    </x-hub::layout.side-menu>
@stop

<div class="pb-28">
    <form wire:submit.prevent="save"
          class="space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('hub.products.show', $product) }}"
               class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white"
               title="Go back to product listing">
                <x-hub::icon ref="chevron-left"
                             style="solid"
                             class="w-8 h-8" />
            </a>

            <h1 class="text-xl font-bold md:text-xl">
                @foreach ($variant->values as $value)
                    {{ $value->translate('name') }} {{ !$loop->last ? '/' : null }}
                @endforeach
            </h1>
        </div>

        <div x-data="{ showVariantSlideover: false }">
            <div class="flex items-center gap-4 mt-4 lg:hidden">
                <x-hub::button type="button"
                               theme="gray"
                               x-on:click="showVariantSlideover = !showVariantSlideover">
                    {{ __('View Variants') }}
                </x-hub::button>

                <x-hub::button theme="gray"
                               type="button"
                               wire:click="$set('showAddVariant', true)">
                    {{ __('adminhub::catalogue.product-variants.add_variant.btn') }}
                </x-hub::button>
            </div>

            <x-hub::slideover-simple target="showVariantSlideover">
                <nav class="space-y-2">
                    @foreach ($product->variants as $v)
                        <a href="{{ route('hub.products.variants.show', [
                            'product' => $product,
                            'variant' => $v,
                        ]) }}"
                           @class([
                               'p-2 rounded text-gray-500 flex items-center gap-2',
                               'bg-blue-50 text-blue-700 hover:text-blue-600' => $variant->id == $v->id,
                               'hover:bg-blue-50 hover:text-blue-700' => $variant->id != $v->id,
                           ])
                           aria-current="page">
                            <div class="shrink-0">
                                @if ($media = $v->images->first())
                                    <img class="block object-cover w-6 h-6 rounded shadow"
                                         src="{{ $media->getFullUrl('small') }}">
                                @else
                                    <x-hub::icon ref="photograph"
                                                 class="w-6 h-6" />
                                @endif
                            </div>

                            <div class="flex-1">
                                <span class="block text-sm font-medium truncate w-44">
                                    @foreach ($v->values as $value)
                                        {{ $value->translate('name') }} {{ !$loop->last ? '/' : null }}
                                    @endforeach
                                </span>
                            </div>
                        </a>
                    @endforeach
                </nav>
            </x-hub::slideover-simple>
        </div>

        <x-hub::layout.bottom-panel>
            <div class="flex justify-end">
                <x-hub::button>Save Variant</x-hub::button>
            </div>
        </x-hub::layout.bottom-panel>

        <div class="space-y-6">
            @foreach ($this->getSlotsByPosition('top') as $slot)
                <div id="{{ $slot->handle }}">
                    <div>
                        @livewire($slot->component, ['slotModel' => $variant], key('top-slot-{{ $slot->handle }}'))
                    </div>
                </div>
            @endforeach

            <div id="attributes">
                @include('adminhub::partials.attributes')
            </div>

            @include('adminhub::partials.image-manager', [
                'existing' => $images,
                'wireModel' => 'imageUploadQueue',
                'filetypes' => ['image/*'],
                'chooseFrom' => $this->productImages,
            ])

            @include('adminhub::partials.pricing')

            @include('adminhub::partials.products.variants.identifiers')

            @include('adminhub::partials.products.variants.inventory')

            @include('adminhub::partials.shipping')

            @foreach ($this->getSlotsByPosition('bottom') as $slot)
                <div id="{{ $slot->handle }}">
                    <div>
                        @livewire($slot->component, ['slotModel' => $variant], key('bottom-slot-{{ $slot->handle }}'))
                    </div>
                </div>
            @endforeach

            <div class="bg-white border border-red-300 rounded shadow">
                <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
                    {{ __('adminhub::inputs.danger_zone.title') }}
                </header>

                <div class="p-6 text-sm">
                    <div class="grid items-center grid-cols-2 gap-4">
                        <div>
                            <strong>
                                {{ __('adminhub::inputs.danger_zone.label', ['model' => 'variant']) }}
                            </strong>
                        </div>

                        <div class="text-right">
                            <x-hub::button wire:click.prevent="$set('showDeleteConfirm', true)"
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
                    {{ __('adminhub::catalogue.product-variants.delete_confirm.title') }}
                </x-slot>

                <x-slot name="content">
                    {{ __('adminhub::catalogue.product-variants.delete_confirm.strapline') }}
                </x-slot>

                <x-slot name="footer">
                    <div class="flex items-center justify-end space-x-4">
                        <x-hub::button theme="gray"
                                       type="button"
                                       wire:click.prevent="$set('showDeleteConfirm', false)">
                            {{ __('adminhub::global.cancel') }}
                        </x-hub::button>

                        <x-hub::button wire:click.prevent="delete"
                                       theme="danger"
                                       type="button">
                            {{ __('adminhub::catalogue.product-variants.delete_confirm.btn') }}
                        </x-hub::button>
                    </div>
                </x-slot>
            </x-hub::modal.dialog>
        </div>
    </form>

    <div class="pt-12 mt-12 border-t">
        @livewire('hub.components.activity-log-feed', [
            'subject' => $variant,
        ])
    </div>
</div>
