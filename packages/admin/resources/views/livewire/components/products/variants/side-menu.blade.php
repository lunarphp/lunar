<div>
    <nav class="space-y-2">
        @foreach ($product->variants as $v)
            <a href="{{ route('hub.products.variants.show', [
                'product' => $product,
                'variant' => $v,
            ]) }}"
               @class([
                   'p-2 rounded text-gray-500 flex items-center gap-2',
                   'bg-sky-50 text-sky-700 hover:text-sky-600' => $variant->id == $v->id,
                   'hover:bg-sky-50 hover:text-sky-700' => $variant->id != $v->id,
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

    <div class="mt-8">
        <x-hub::button theme="gray"
                       type="button"
                       wire:click="$set('showAddVariant', true)">
            {{ __('adminhub::catalogue.product-variants.add_variant.btn') }}
        </x-hub::button>
    </div>

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
</div>
