<x-hub::slideover
        wire:model="slideoverForms.{{ $slideoverForm['handle'] }}.show"
        form="{{ $slideoverForm['submitAction'] }}">
    <div class="space-y-4">
        <div class="overflow-hidden shadow sm:rounded-md">
            @livewire($slideoverForm['component'], ['model' => $slideoverForm['model']])
        </div>
    </div>

    <x-slot name="footer">
        <x-hub::button wire:click.prevent="$set('slideoverForms.{{ $slideoverForm['handle'] }}.show', null)" theme="gray">
            {{ __('adminhub::global.cancel') }}
        </x-hub::button>

        <x-hub::button type="submit">
            {{ __('adminhub::global.save') }}
        </x-hub::button>
    </x-slot>
</x-hub::slideover>
