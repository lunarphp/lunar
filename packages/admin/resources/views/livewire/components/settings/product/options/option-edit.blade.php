<div class="space-y-6">
    <header>
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ $productOption->translate('name') }}
        </h1>
    </header>
    <div class="overflow-hidden shadow sm:rounded-md">
        <form wire:submit.prevent="save">
            <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
                <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('productOption.name.' . $this->defaultLanguage->code)">
                    <x-hub::translatable>
                        <x-hub::input.text wire:model.defer="productOption.name.{{ $this->defaultLanguage->code }}" :error="$errors->first('productOption.name.' . $this->defaultLanguage->code)" :placeholder="__('adminhub::components.option.value.edit.name.placeholder')" />
                        @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
                        <x-slot :name="$language['code']">
                            <x-hub::input.text wire:model="productOption.name.{{ $language->code }}" :placeholder="__('adminhub::components.attribute-group-edit.name.placeholder')" />
                        </x-slot>
                        @endforeach
                    </x-hub::translatable>
                </x-hub::input.group>

                <x-hub::input.group required :label="__('adminhub::inputs.handle')" for="handle" :error="$errors->first('productOption.handle')">
                    <x-hub::input.text wire:model.defer="productOption.handle" id="handle" :error="$errors->first('productOption.handle')" />
                </x-hub::input.group>
            </div>

            <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
                <x-hub::button>
                    {{ __($productOption->id ? 'adminhub::components.option-edit.update_btn' : 'adminhub::components.option-edit.create_btn') }}
                </x-hub::button>
            </div>
        </form>
    </div>
    <div class="flex justify-between items-center">
        <div>
            <h3>{{ __('adminhub::components.option.value_title') }}</h3>
        </div>
        <div>
            <x-hub::button
                theme="gray"
                type="button"
                wire:click="$set('showValueCreate', true)"
            >{{ __('adminhub::components.option.create_option_value') }}</x-hub::button>

            <x-hub::button
                theme="gray"
                type="button"
                wire:click="savePositions"
            >{{ __('adminhub::components.option.save_positions') }}</x-hub::button>
        </div>
    </div>
    <div>
        <div
            class="space-y-2"
            wire:sort sort.options='{group: "values", method: "sortOptionValues", owner: {{ $productOption->id }}}'
        >
            @forelse($this->values as $value)
            <div
                class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300"
                wire:key="product_option_value_{{ $value['id'] }}"
                sort.item="values"
                sort.parent="{{ $productOption->id }}"
                sort.id="{{ $value['id'] }}"
            >
                <div sort.handle class="cursor-grab">
                    <x-hub::icon ref="selector" style="solid" class="mr-2 text-gray-400 hover:text-gray-700" />
                </div>
                <span class="truncate grow">{{ $value['value'] }}</span>
                <span class="text-gray-500 text-xs block mr-2">{{ number_format($value['variants_count']) }} variant(s)</span>
                <div>
                    <x-hub::dropdown minimal>
                        <x-slot name="options">
                            <x-hub::dropdown.button
                                wire:click="$set('optionValueIdToEdit', {{ $value['id'] }})"
                                class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                                {{ __('adminhub::components.option.edit_option.value.btn') }}
                            </x-hub::dropdown.button>

                            <x-hub::dropdown.button wire:click="$set('optionValueToDeleteId', {{ $value['id'] }})"
                                class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-50">
                                <span class="text-red-500">{{ __('adminhub::components.option.delete_option.value.btn') }}</span>
                            </x-hub::dropdown.button>
                        </x-slot>
                    </x-hub::dropdown>
                </div>
            </div>
            @empty
            @endforelse
        </div>
    </div>


    @if($this->optionValueToDelete)
        <x-hub::modal.dialog wire:model="optionValueToDeleteId">
            <x-slot name="title">{{ __('adminhub::components.option.delete_title') }}</x-slot>
            <x-slot name="content">
                @if($this->optionValueToDelete->variants_count)
                <x-hub::alert level="danger">
                    {{ __('adminhub::components.option.value.edit.delete_locked', [
                        'count' => $this->optionValueToDelete->variants_count
                    ]) }}
                </x-hub::alert>
                @else
                @endif
            </x-slot>
            <x-slot name="footer">
                <div class="flex justify-between">
                    <x-hub::button theme="gray"
                                   wire:click="$set('optionValueToDeleteId', null)"
                                   type="button">
                        {{ __('adminhub::global.cancel') }}
                    </x-hub::button>
                    <x-hub::button theme="danger"
                                   type="button"
                                   wire:click="deleteOptionValue"
                                   :disabled="!!$this->optionValueToDelete->variants_count">
                        {{ __('adminhub::global.delete') }}
                    </x-hub::button>
                </div>
            </x-slot>
        </x-hub::modal.dialog>
    @endif


    <x-hub::modal.dialog wire:model="optionValueIdToEdit" form="updateOptionValue">
        <x-slot name="title">
            {{ __('adminhub::components.option.update_option_value') }}
        </x-slot>
        <x-slot name="content">
            <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('productOptionValue.name.' . $this->defaultLanguage->code)">
                <x-hub::translatable>
                    <x-hub::input.text wire:model.defer="productOptionValue.name.{{ $this->defaultLanguage->code }}" :error="$errors->first('productOptionValue.name.' . $this->defaultLanguage->code)" />
                    @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
                    <x-slot :name="$language['code']">
                        <x-hub::input.text wire:model="productOptionValue.name.{{ $language->code }}"/>
                    </x-slot>
                    @endforeach
                </x-hub::translatable>
            </x-hub::input.group>
        </x-slot>
        <x-slot name="footer">
            <div class="flex w-full justify-end">
                <x-hub::button>{{ __('adminhub::components.option.update_option_value') }}</x-hub::button>
            </div>
        </x-slot>
    </x-hub::modal.dialog>

    <x-hub::modal.dialog wire:model="showValueCreate" form="createOptionValue">
        <x-slot name="title">{{ __('adminhub::components.option.create_option_value') }}</x-slot>
        <x-slot name="content">
            <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('newProductOptionValue.name.' . $this->defaultLanguage->code)">
                <x-hub::translatable>
                    <x-hub::input.text wire:model.defer="newProductOptionValue.name.{{ $this->defaultLanguage->code }}" :error="$errors->first('newProductOptionValue.name.' . $this->defaultLanguage->code)" />
                    @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
                    <x-slot :name="$language['code']">
                        <x-hub::input.text wire:model="newProductOptionValue.name.{{ $language->code }}"/>
                    </x-slot>
                    @endforeach
                </x-hub::translatable>
            </x-hub::input.group>
        </x-slot>
        <x-slot name="footer">
            <div class="flex w-full justify-end">
                <x-hub::button>{{ __('adminhub::components.option.create_option_value') }}</x-hub::button>
            </div>
        </x-slot>
    </x-hub::modal.dialog>
</div>
