@props([
    'activeTab',
    'tabs',
])

<div x-data="{ activeTab: '{{ $activeTab }}' }">
    <nav class="flex space-x-4" aria-label="Tabs">
        @foreach ($tabs as $tabName => $tab)
            <x-hub::tabs.heading :key="$tabName">
                <button
                  type="button"
                  wire:click="$set('activeTab', '{{ $tabName }}')"
                  class="px-3 py-3 text-sm font-medium @if($tabName == $activeTab) text-gray-800 bg-white @else test-gray-500 hover:text-gray-700 @endif rounded-t"
                >
                {{ $tab }}
                </button>
            </x-hub::tabs.heading>
        @endforeach
    </nav>
    <div class="p-6 bg-white rounded-b shadow">
        <x-hub::tabs.content :tab="$tab" :key="$tabName">
            {{ $slot }}
        </x-hub::tabs.content>
    </div>
</div>

