<div class="p-4 space-y-2">
    <x-hub::input.select wire:model="status">
        <option>Select Status</option>
        @foreach($this->statuses as $handle => $status)
            <option value="{{ $handle }}">{{ $status['label'] }}</option>
        @endforeach
    </x-hub::input.select>

    <x-hub::button type="button" wire:click="updateStatus">Update</x-hub::button>
</div>
