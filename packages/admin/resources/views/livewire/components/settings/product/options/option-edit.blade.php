<div class="space-y-4">
  <header>
    <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
      {{ $productOption->translate('name') }}
    </h1>
  </header>
  <div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
      <form wire:submit.prevent="create" class="space-y-4">
        <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('productOption.name.' . $this->defaultLanguage->code)">
          <x-hub::translatable>
            <x-hub::input.text
              wire:model.defer="productOption.name.{{ $this->defaultLanguage->code }}"
              :error="$errors->first('productOption.name.' . $this->defaultLanguage->code)"
              :placeholder="__('adminhub::components.option.value.edit.name.placeholder')"
            />
            @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
              <x-slot :name="$language['code']">
                <x-hub::input.text
                  wire:model="productOption.name.{{ $language->code }}"
                  :placeholder="__('adminhub::components.attribute-group-edit.name.placeholder')"
                />
              </x-slot>
            @endforeach
          </x-hub::translatable>
        </x-hub::input.group>

        <x-hub::input.group required :label="__('adminhub::inputs.handle')" for="handle" :error="$errors->first('productOption.handle')">
          <x-hub::input.text
            wire:model.defer="productOption.handle"
            id="handle"
            :error="$errors->first('productOption.handle')"
          />
        </x-hub::input.group>


        <div class="mt-6">
          <x-hub::button>
            {{ __($productOption->id ? 'adminhub::components.option-edit.update_btn' : 'adminhub::components.option-edit.create_btn') }}
          </x-hub::button>
        </div>
      </form>
    </div>
  </div>
  <div class="py-4 mt-2 space-y-2">
    <div class="space-y-2"
      wire:sort
      sort.options='{group: "values", method: "sortOptionValues", owner: {{ $productOption->id }}}'
    >
      @forelse($this->values as $value)
        <div class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300"
            wire:key="product_option_value_{{ $value['id'] }}"
            sort.item="values"
            sort.parent="{{ $productOption->id }}"
            sort.id="{{ $value['id'] }}">
          <div sort.handle class="cursor-grab">
            <x-hub::icon ref="selector"
              style="solid"
              class="mr-2 text-gray-400 hover:text-gray-700" />
          </div>
          <span class="truncate grow">{{ $value['value'] }}</span>
        </div>
      @empty
      @endforelse
    </div>
                    {{-- <div class="space-y-2"
                         wire:sort
                         sort.options='{group: "values", method: "sortOptionValues", owner: {{ $option->id }}}'
                         x-show="expanded">
                        @foreach ($option->values as $optionValue)
                            <div class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300"
                                 wire:key="attribute_{{ $optionValue->id }}"
                                 sort.item="values"
                                 sort.parent="{{ $option->id }}"
                                 sort.id="{{ $optionValue->id }}">
                                <div sort.handle
                                     class="cursor-grab">
                                    <x-hub::icon ref="selector"
                                                 style="solid"
                                                 class="mr-2 text-gray-400 hover:text-gray-700" />
                                </div>
                                <span class="truncate grow">{{ $optionValue->translate('name') }}</span>
                                <div class="mr-4 text-xs text-gray-500">
                                    {{ class_basename($optionValue->type) }}
                                </div>
                                <div>
                                    <x-hub::dropdown minimal>
                                        <x-slot name="options">
                                            <x-hub::dropdown.button type="button"
                                                                    wire:click="$set('editOptionValueId', {{ $optionValue->id }})"
                                                                    class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                                                {{ __('adminhub::components.option.edit_option.value.btn') }}
                                                <x-hub::icon ref="pencil"
                                                             style="solid"
                                                             class="w-4" />
                                            </x-hub::dropdown.button>

                                            <x-hub::dropdown.button wire:click="$set('deleteOptionValueId', {{ $optionValue->id }})"
                                                                    class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-50">
                                                <span
                                                        class="text-red-500">{{ __('adminhub::components.option.delete_option.value.btn') }}</span>
                                            </x-hub::dropdown.button>
                                        </x-slot>
                                    </x-hub::dropdown>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if (!$option->values->count())
                        <span class="mx-4 text-sm text-gray-500">
                            {{ __('adminhub::components.option.no_option_values_text') }}
                        </span>
                    @endif
                </div> --}}
      </div>
</div>
