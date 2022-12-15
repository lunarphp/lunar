<div class="space-y-4">
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.product.options.index.title') }}
        </h1>

        <div class="mt-4 sm:mt-0">
            <x-hub::button wire:click.prevent="$set('showOptionCreate', true)">
                {{ __('adminhub::settings.product.options.index.create_btn') }}
            </x-hub::button>
        </div>
    </header>

    <div wire:sort
         sort.options='{group: "groups", method: "sortGroups"}'
         class="space-y-2">
        @forelse($productOptions as $option)
            <div wire:key="group_{{ $option['id'] }}"
                 x-data="{ expanded: false }"
                 sort.item="groups"
                 sort.id="{{ $option['id'] }}">
                <div class="flex items-center">
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
                            class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300">
                        <div class="flex items-center justify-between expand">
                            {{ $option['name'] }}
                        </div>

                        <div class="flex">
                            <span class="text-gray-500 text-xs mr-2 block">{{ number_format($option['values_count']) }} value(s)</span>
                            <x-hub::dropdown minimal>
                                <x-slot name="options">
                                    <x-hub::dropdown.link href="{{ route('hub.product.options.edit', $option['id']) }}"
                                                            class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                                        {{ __('adminhub::components.option.edit_group_btn') }}
                                    </x-hub::dropdown.link>

                                    <x-hub::dropdown.button wire:click="$set('deleteOptionId', {{ $option['id'] }})"
                                                            class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-50">
                                        <span
                                                class="text-red-500">{{ __('adminhub::components.option.delete_group_btn') }}</span>
                                    </x-hub::dropdown.button>
                                </x-slot>
                            </x-hub::dropdown>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="w-full text-center text-gray-500">
                {{ __('adminhub::components.option.no_groups') }}
            </div>
        @endforelse
    </div>

    <x-hub::modal.dialog wire:model="showOptionCreate" form="createOption">
        <x-slot name="title">{{ __('adminhub::components.option.create_title') }}</x-slot>
        <x-slot name="content">
            <x-hub::input.group label="Name" for="optionName" :error="$errors->first('newProductOption.name.'.$this->defaultLanguage->code)">
                <div class="flex space-x-4">
                  <div class="relative w-full">
                    <x-hub::translatable :expanded="false">
                      <x-hub::input.text
                        wire:model="newProductOption.name.{{ $this->defaultLanguage->code }}"
                        :error="$errors->first('name.'.$this->defaultLanguage->code)"
                        placeholder="Size"
                      />
                      @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
                        <x-slot :name="$language['code']">
                          <x-hub::input.text
                            wire:model="name.{{ $language->code }}"
                            :error="$errors->first('newProductOption.name.'.$language->code)"
                          />
                        </x-slot>
                      @endforeach
                    </x-hub::translatable>

                    @error('option_handle')
                        <div  class="mt-2">
                        <x-hub::alert level="danger">
                            {{ $message }}
                        </x-hub::alert>
                        </div>
                    @enderror
                  </div>
                </div>
            </x-hub::input.group>
        </x-slot>
        <x-slot name="footer">
            <x-hub::button>Create option</x-hub::button>
        </x-slot>
    </x-hub::modal.dialog>

    @if ($this->optionToDelete)
        <x-hub::modal.dialog wire:model="deleteOptionId">
            <x-slot name="title">{{ __('adminhub::components.option.delete_title') }}</x-slot>
            <x-slot name="content">
                @if($this->optionToDelete->values_count)
                <x-hub::alert level="danger">
                    {{ __('adminhub::components.option.delete_warning') }}
                </x-hub::alert>
                @endif
            </x-slot>
            <x-slot name="footer">
                <div class="flex justify-between">
                    <x-hub::button theme="gray"
                                   wire:click="$set('deleteOptionId', null)"
                                   type="button">
                        {{ __('adminhub::global.cancel') }}
                    </x-hub::button>
                    <x-hub::button theme="danger"
                                   type="button"
                                   wire:click="deleteOption"
                                   :disabled="!!$this->optionToDelete->values_count">
                        {{ __('adminhub::global.delete') }}
                    </x-hub::button>
                </div>
            </x-slot>
        </x-hub::modal.dialog>
    @endif
</div>
