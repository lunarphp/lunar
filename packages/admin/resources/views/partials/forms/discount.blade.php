<x-hub::layout.bottom-panel>
    <div class="flex justify-end">
        <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          {{ __(
            $discount->id ? 'adminhub::components.discounts.save_btn' : 'adminhub::components.discounts.create_btn'
          ) }}
        </button>
    </div>
</x-hub::layout.bottom-panel>

<div class="pb-24 mt-8 lg:gap-8 lg:flex lg:items-start">
    <div class="space-y-6 lg:flex-1">
        <div class="space-y-6">
            @if (!$this->hasChannelAvailability)
                <div>
                    <x-hub::alert level="danger">
                        {{__('adminhub::partials.discounts.no_channel_availability')}}
                    </x-hub::alert>
                </div>
            @endif
            <div id="basic-information">
                @include('adminhub::partials.forms.discount.basic-information')
            </div>

            <div class="bg-white p-4 shadow rounded">
                <div class="grid grid-cols-2 gap-4 items-center">
                    <x-hub::input.group label="{{__('adminhub::inputs.priority.label')}}" for="priority" instructions="{{__('adminhub::inputs.priority.instructions')}}">
                        <x-hub::input.select wire:model="discount.priority" id="priority">
                            <option value="1">{{__('adminhub::global.low')}}</option>
                            <option value="5">{{__('adminhub::global.medium')}}</option>
                            <option value="10">{{__('adminhub::global.high')}}</option>
                        </x-hub::input.select>
                    </x-hub::input.group>

                    <div class="flex items-center space-x-2">
                        <x-hub::input.toggle wire:model="discount.stop" id="stop" />
                        <label for="stop" class="cursor-pointer text-gray-800 text-sm">
                            {{ __('adminhub::components.discounts.show.stop.label') }}
                        </label>
                    </div>
                </div>
            </div>

            <div id="availability">
                @include('adminhub::partials.availability', [
                    'channels' => true,
                    'type' => 'discount',
                    'customerGroups' => true,
                    'customerGroupType' => 'toggle',
                ])
            </div>

            <div id="limitations">
                @include('adminhub::partials.forms.discount.limitations')
            </div>

            <div id="conditions">
                @include('adminhub::partials.forms.discount.conditions')
            </div>

            <div id="discount-type">
                @include('adminhub::partials.forms.discount.discount-type')
            </div>

            @if($discount->id)
                <div class="space-y-4">
                    <div class="bg-white border border-red-300 rounded shadow">
                        <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
                            {{ __('adminhub::inputs.danger_zone.title') }}
                        </header>
                        <div class="p-6 space-y-4 text-sm">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 md:col-span-6">
                                    <strong>{{ __('adminhub::components.discounts.show.danger_zone.label') }}</strong>
                                    <p class="text-xs text-gray-600">{{ __('adminhub::components.discounts.show.danger_zone.instructions') }}</p>
                                </div>
                                <div class="col-span-9 lg:col-span-4">
                                    <x-hub::input.text type="email" wire:model="deleteConfirm" />
                                </div>
                                <div class="col-span-3 text-right lg:col-span-2">
                                    <x-hub::button theme="danger" :disabled="!$this->canDelete" wire:click="delete" type="button">{{ __('adminhub::global.delete') }}</x-hub::button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <x-hub::layout.page-menu>
        <nav class="space-y-2"
         aria-label="Sidebar"
         x-data="{ activeAnchorLink: '' }"
         x-init="activeAnchorLink = window.location.hash">
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
        </nav>
    </x-hub::layout.page-menu>

</div>
