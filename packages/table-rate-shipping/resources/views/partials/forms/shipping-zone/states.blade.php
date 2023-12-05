<div class="space-y-4">
	<x-hub::input.group
	  label="Country"
	  for="country"
	  :error="$errors->first('country')"
	>
	  <x-hub::input.select wire:model="country" id="country">
		@foreach($this->allCountries as $country)
		  <option value="{{ $country->id }}">{{ $country->name }}</option>
		@endforeach
	  </x-hub::input.select>
	</x-hub::input.group>


	<x-hub::input.group label="States" for="states"  :error="$errors->first('currency.name')">
	  <div class="grid grid-cols-2 gap-4">
			<div class="border rounded">
			  <div class="p-2 border-b shadow-sm">
				  <x-hub::input.text wire:model="searchTerm" placeholder="Search states" />
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
			  <label class="block border-b py-2 text-sm px-3 cursor-pointer hover:bg-gray-50" wire:key="zone_country_{{ $country->id }}">
				  {{ $state->name }}
				  <input type="checkbox" class="hidden" wire:model="selectedCountries" value="{{ $state->id }}">
			  </label>
			@empty
			  <div class="flex h-full items-center text-center w-full">
				 <span class="w-full block text-center text-xs text-gray-500">States you select will appear here</span>
			  </div>
			@endforelse
			</div>
	  </div>
	</x-hub::input.group>

</div>
