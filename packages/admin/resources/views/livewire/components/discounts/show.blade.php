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

    <form action="#"
      method="POST"
      wire:submit.prevent="save">
        @include('adminhub::partials.forms.discount')
    </form>

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
