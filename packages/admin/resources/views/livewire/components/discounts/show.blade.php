<div>
    <div class="flex items-center gap-4">
        <a href="{{ route('hub.discounts.index') }}"
           class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white"
           title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
            <x-hub::icon ref="chevron-left"
                         style="solid"
                         class="w-8 h-8" />
        </a>

        <h1 class="text-xl font-bold md:text-xl">
            {{ $discount->name }}
        </h1>
    </div>

    <div class="pb-24 mt-8 lg:gap-8 lg:flex lg:items-start">
        <div class="space-y-6 lg:flex-1">
            <div class="space-y-6">
                <div id="basic-information">
                    @include('adminhub::partials.forms.discount.basic-information')
                </div>

                <div id="availability">
                    @include('adminhub::partials.forms.discount.availability')
                </div>
            </div>
        </div>
        <x-hub::layout.page-menu>
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
        </x-hub::layout.page-menu>
    </div>

    {{-- <div class="space-y-4">

        <form action="#"
              method="POST"
              wire:submit.prevent="save">
            @include('adminhub::partials.forms.discount')
        </form>



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
    </div> --}}

</div>
