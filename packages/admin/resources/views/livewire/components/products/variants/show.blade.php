@section('menu')
    <x-hub::layout.side-menu>
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
                        @if ($media = $v->media->first())
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

        <div class="mt-8">
            <x-hub::button theme="gray"
                           type="button"
                           wire:click="$set('showAddVariant', true)">
                {{ __('adminhub::catalogue.product-variants.add_variant.btn') }}
            </x-hub::button>
        </div>
    </x-hub::layout.side-menu>
@stop

<form wire:submit.prevent="save"
      class="space-y-6 pb-28">
    <x-hub::slideover :title="__('adminhub::catalogue.product-variants.add_variant.title')"
                      wire:model="showAddVariant">
        <div class="space-y-4">
            @foreach ($this->variantOptions() as $option)
                <x-hub::input.group :label="$option->translate('name')"
                                    for="name"
                                    :error="$errors->first('newValues.' . $option->id)">
                    <div class="flex items-center">
                        <div class="w-full">
                            <x-hub::input.select wire:model="newValues.{{ $option->id }}">
                                <option value>
                                    {{ __('adminhub::catalogue.product-variants.add_variant.null_option') }}
                                </option>
                                @foreach ($option->values as $value)
                                    <option value="{{ $value->id }}">{{ $value->translate('name') }}
                                    </option>
                                @endforeach
                            </x-hub::input.select>
                        </div>
                        <div class="w-1/3 text-right">
                            <x-hub::button type="button"
                                           theme="gray"
                                           size="sm"
                                           wire:click.prevent="$emit('variant-show.selected-option', '{{ $option->id }}')">
                                {{ __('adminhub::catalogue.product-variants.add_variant.add_new_option') }}
                            </x-hub::button>
                        </div>
                    </div>
                </x-hub::input.group>
            @endforeach

            @livewire('hub.components.product-options.option-value-create-modal', [
                'canPersist' => false,
            ])
        </div>
        @if (session()->has('variant_exists'))
            <div class="mt-4">
                <x-hub::alert level="danger">
                    {{ __('adminhub::catalogue.product-variants.add_variant.already_exists') }}
                </x-hub::alert>
            </div>
        @endif
        <div class="mt-4">
            <x-hub::button theme="gray"
                           type="button"
                           wire:click="generateVariants">
                {{ __('adminhub::catalogue.product-variants.add_variant.btn') }}
            </x-hub::button>
        </div>
    </x-hub::slideover>

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
                            @if ($media = $v->media->first())
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

    <div class="fixed bottom-0 left-0 right-0 z-40 p-6 border-t border-gray-100 lg:left-auto bg-white/75"
         :class="{
             'lg:w-[calc(100vw_-_12rem)]': showExpandedMenu,
             'lg:w-[calc(100vw_-_5rem)]': !showExpandedMenu
         }">
        <div class="flex justify-end space-x-6">
            <x-hub::button>Save Variant</x-hub::button>
        </div>
    </div>

    <div class="space-y-6">
        @foreach ($this->getSlotsByPosition('top') as $slot)
            <div id="{{ $slot->handle }}">
                <div>
                    @livewire($slot->component, ['slotModel' => $product], key('top-slot-{{ $slot->handle }}'))
                </div>
            </div>
        @endforeach

        <div id="attributes">
            @include('adminhub::partials.attributes')
        </div>

        @include('adminhub::partials.pricing')

        @include('adminhub::partials.products.variants.image')

        @include('adminhub::partials.products.variants.identifiers')

        @include('adminhub::partials.products.variants.inventory')

        @include('adminhub::partials.shipping')

        @foreach ($this->getSlotsByPosition('bottom') as $slot)
            <div id="{{ $slot->handle }}">
                <div>
                    @livewire($slot->component, ['slotModel' => $product], key('bottom-slot-{{ $slot->handle }}'))
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

        <div class="pt-12 mt-12 border-t">
            @livewire('hub.components.activity-log-feed', [
                'subject' => $variant,
            ])
        </div>
    </div>
</form>
