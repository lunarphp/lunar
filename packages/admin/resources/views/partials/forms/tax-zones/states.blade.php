<div class="space-y-4">
  <x-hub::input.group
    :label="__('adminhub::inputs.country.label')"
    for="country"
    :error="$errors->first('country')"
  >
    <x-hub::input.select wire:model="country" id="country">
    @foreach($this->allCountries as $country)
      <option value="{{ $country->id }}">{{ $country->name }}</option>
    @endforeach
    </x-hub::input.select>
  </x-hub::input.group>


  <x-hub::input.group :label="__('adminhub::inputs.states.label')" for="states"  :error="$errors->first('currency.name')">
    <div class="grid grid-cols-2 gap-4">
      <div class="border rounded">
        <div class="p-2 border-b shadow-sm">
          <x-hub::input.text wire:model="searchTerm" :placeholder="__('adminhub::inputs.states.search_placeholder')" />
        </div>
        <div class="h-full max-h-64 overflow-y-auto">
        @foreach($this->states as $state)
          <label
            class="block border-b py-2 text-sm px-3 cursor-pointer hover:bg-gray-50"
          wire:key="state_{{ $state->id }}"
          >
          {{ $state->name }}
          <input type="checkbox" class="hidden" wire:model="selectedStates" value="{{ $state->id }}">
          </label>
        @endforeach
        </div>
      </div>

      <div class="h-full max-h-96 overflow-y-auto border rounded">
      @forelse($this->zoneStates as $state)
        <label class="block border-b py-2 text-sm px-3 cursor-pointer hover:bg-gray-50" wire:key="zone_state_{{ $state->id }}">
          {{ $state->name }}
          <input type="checkbox" class="hidden" wire:model="selectedStates" value="{{ $state->id }}">
        </label>
      @empty
        <div class="flex h-full items-center text-center w-full">
         <span class="w-full block text-center text-xs text-gray-500">
          {{ __('adminhub::inputs.states.empty_selected') }}
         </span>
        </div>
      @endforelse
      </div>
    </div>
  </x-hub::input.group>

</div>
