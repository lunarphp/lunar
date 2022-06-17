<div class="mx-12 lg:mx-24">
    <div class="flex items-center space-x-4">
      {{--
        Product title.
       --}}
      <a href="{{ route('hub.products.index') }}" class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white" title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
        <x-hub::icon ref="chevron-left" style="solid" class="w-8 h-8" />
      </a>
      <h1 class="text-xl font-bold md:text-xl">
        @if($discount->id)
            {{ $discount->name }}
        @else
            Create Discount
        @endif
      </h1>
    </div>

    <div class="py-12 pb-24">
      <div class="sm:px-6 lg:px-0 lg:col-span-9">
        <div class="space-y-6">
            <div
              class="flex-col space-y-4 bg-white rounded px-4 py-5 sm:p-6"
            >
                <x-hub::input.group for="title" :label="__('adminhub::inputs.name')">
                    <x-hub::input.text wire:model="discount.name" />
                </x-hub::input.group>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-hub::input.group for="starts_at" :label="__('adminhub::inputs.starts_at.label')">
                            <x-hub::input.datepicker wire:model="discount.starts_at" :enable-time="true" />
                        </x-hub::input.group>
                    </div>

                    <div>
                        <x-hub::input.group for="starts_at" :label="__('adminhub::inputs.ends_at.label')" :error="$errors->first('discount.ends_at')">
                            <x-hub::input.datepicker wire:model="discount.ends_at" :enable-time="true" />
                        </x-hub::input.group>
                    </div>
                </div>

                <x-hub::input.group for="type" label="Type">
                    <x-hub::input.select wire:model="discount.type">
                    @foreach($this->discountTypes as $discountType)
                        <option value="{{ get_class($discountType) }}">
                            {{ $discountType->getName() }}
                        </option>
                    @endforeach
                    </x-hub::input.select>
                </x-hub::input.group>

                @if($this->ui)
                    @livewire($this->ui, [
                        'discount' => $discount,
                    ])
                @endif

                <x-hub::input.group for="type" label="Limit discount">
                    <x-hub::input.select wire:model="discount.type">
                        <option value="">All products</option>
                        <option value="">Specific products</option>
                        <option value="">Products in a collection</option>
                    </x-hub::input.select>
                </x-hub::input.group>
            </div>

        </div>
      </div>
    </div>
</div>
