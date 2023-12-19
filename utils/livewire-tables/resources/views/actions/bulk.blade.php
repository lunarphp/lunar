<div x-data="{ showModal: false }">
    <button type="button"
            wire:click="$set('showModal', true)"
            class="lt-px-3 lt-py-2 lt-text-sm lt-font-medium lt-text-gray-600 lt-transition lt-bg-white lt-border lt-border-gray-200 lt-rounded-md hover:lt-bg-gray-50 hover:lt-text-gray-700 hover:lt-shadow-sm focus:lt-outline-none focus:lt-ring focus:lt-ring-sky-100 focus:lt-border-sky-300">
        <span class="lt-capitalize">
            {{ $label }}
        </span>
    </button>

    <x-l-tables::support.modal wire:model="showModal">
        <div>
            @livewire($livewire, [
                'ids' => $selectedIds,
            ])
        </div>
    </x-l-tables::support.modal>
</div>
