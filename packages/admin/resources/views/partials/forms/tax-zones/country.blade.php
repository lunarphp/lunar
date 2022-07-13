<x-hub::input.group label="Countries" for="type"  :error="$errors->first('selectedCountries')">
  <div class="grid grid-cols-2 gap-4">
    <div class="border rounded">
      <div class="p-2 border-b shadow-sm">
        <x-hub::input.text wire:model="searchTerm" placeholder="Search for country by name" />
      </div>
      <div class="h-full max-h-64 overflow-y-auto">
        @foreach($this->countries as $country)
        <label class="block border-b py-2 text-sm px-3 cursor-pointer hover:bg-gray-50" wire:key="country_{{ $country->id }}">
          {{ $country->emoji }} {{ $country->name }}
          <input type="checkbox" class="hidden" wire:model="selectedCountries" value="{{ $country->id }}">
        </label>
        @endforeach
      </div>
    </div>

    <div class="h-full max-h-96 overflow-y-auto border rounded">
    @forelse($this->zoneCountries as $country)
      <label class="block border-b py-2 text-sm px-3 cursor-pointer hover:bg-gray-50" wire:key="zone_country_{{ $country->id }}">
        {{ $country->emoji }} {{ $country->name }}
        <input type="checkbox" class="hidden" wire:model="selectedCountries" value="{{ $country->id }}">
      </label>
    @empty
      <div class="flex h-full items-center text-center w-full">
       <span class="w-full block text-center text-xs text-gray-500">Countries you select will appear here</span>
      </div>
    @endforelse
    </div>
  </div>
</x-hub::input.group>
