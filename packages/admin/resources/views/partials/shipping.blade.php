<div class="overflow-hidden shadow sm:rounded-md" x-data="{ isShipped: false }">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.shipping.title') }}
      </h3>
    </header>

    <x-hub::input.group :label="__('adminhub::inputs.requires_shipping.label')" for="physical_product">
      <x-hub::input.toggle wire:model="variant.shippable" />
    </x-hub::input.group>


    <div class="grid grid-cols-4 gap-4 grid-3">
      <x-hub::input.group :label="__('adminhub::inputs.length.label')" for="length">
        <div class="relative rounded-md shadow-sm">
          <input type="number" id="length" wire:model.lazy="variant.length_value" step="0.0001" class="block w-full pl-3 border-gray-300 rounded-md form-input pr-14 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
          <div class="absolute inset-y-0 right-0 flex items-center">
            <select wire:model="variant.length_unit" class="h-full py-0 pl-2 text-gray-500 bg-transparent border-transparent rounded-md form-select focus:ring-indigo-500 focus:border-indigo-500 pr-7 sm:text-sm">
              @foreach($this->lengthMeasurements as $unit)
                <option value="{{ $unit }}"  @if(!$variant->length_unit && $loop->first) selected @endif>{{ $unit }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.width.label')" for="width">
        <div class="relative rounded-md shadow-sm">
          <input type="number" id="width" wire:model.lazy="variant.width_value" step="0.0001" value="0.0000" class="block w-full pl-3 border-gray-300 rounded-md form-input pr-14 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
          <div class="absolute inset-y-0 right-0 flex items-center">
            <select wire:model="variant.width_unit" class="h-full py-0 pl-2 text-gray-500 bg-transparent border-transparent rounded-md form-select focus:ring-indigo-500 focus:border-indigo-500 pr-7 sm:text-sm">
              @foreach($this->lengthMeasurements as $unit)
                <option value="{{ $unit }}"  @if(!$variant->width_unit && $loop->first) selected @endif>{{ $unit }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.height.label')" for="height">
        <div class="relative rounded-md shadow-sm">
          <input type="number" id="height" wire:model.lazy="variant.height_value" step="0.0001" value="0.0000" class="block w-full pl-3 border-gray-300 rounded-md form-input pr-14 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
          <div class="absolute inset-y-0 right-0 flex items-center">
            <select wire:model="variant.height_unit" class="h-full py-0 pl-2 text-gray-500 bg-transparent border-transparent rounded-md form-select focus:ring-indigo-500 focus:border-indigo-500 pr-7 sm:text-sm">
              @foreach($this->lengthMeasurements as $unit)
                <option value="{{ $unit }}" @if(!$variant->height_unit && $loop->first) selected @endif>{{ $unit }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.weight.label')" for="weight">
        <div class="relative rounded-md shadow-sm">
          <input type="number" id="weight" wire:model.lazy="variant.weight_value" step="0.0001" value="0.0000" class="block w-full pl-3 border-gray-300 rounded-md form-input pr-14 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
          <div class="absolute inset-y-0 right-0 flex items-center">
            <select wire:model="variant.weight_unit" class="h-full py-0 pl-2 text-gray-500 bg-transparent border-transparent rounded-md form-select focus:ring-indigo-500 focus:border-indigo-500 pr-7 sm:text-sm">
              @foreach($this->weightMeasurements as $unit)
                <option value="{{ $unit }}" @if(!$variant->weight_unit && $loop->first) selected @endif>{{ $unit }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </x-hub::input.group>
    </div>
    <x-hub::input.group :label="__('adminhub::inputs.volume.label')" for="volume">
        <div x-data="{ editVolume: @entangle('manualVolume')}">
          <div x-show="!editVolume" class="text-sm">
            {!! __('adminhub::partials.shipping.calculated_volume', [
              'value' => '<strong>'.$variant->volume->format().'</strong>',
            ]) !!}
            <a href="#" @click.prevent="editVolume = true" class="text-sm text-indigo-500 hover:underline">
              {{ __('adminhub::partials.shipping.manual_volume_btn') }}
            </a>
          </div>

          <div x-show="editVolume">

            <div class="grid grid-cols-4">
              <div class="relative rounded-md shadow-sm">
                <input wire:model.lazy="variant.volume_value" id="volume" type="number" step="0.0001" value="0.0000" class="block w-full pl-3 border-gray-300 rounded-md form-input pr-14 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
                <div class="absolute inset-y-0 right-0 flex items-center">
                  <select wire:model="variant.volume_unit" class="h-full py-0 pl-2 text-gray-500 bg-transparent border-transparent rounded-md form-select focus:ring-indigo-500 focus:border-indigo-500 pr-7 sm:text-sm">
                    @foreach($this->volumeMeasurements as $unit)
                      <option value="{{ $unit }}" @if(!$variant->volume_unit && $loop->first) selected @endif>{{ $unit }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <a href="#" @click.prevent="editVolume = false" class="text-sm text-indigo-500 hover:underline">
              {{ __('adminhub::partials.shipping.auto_volume_btn') }}
            </a>
          </div>

        </div>

      </x-hub::input.group>
  </div>
</div>
