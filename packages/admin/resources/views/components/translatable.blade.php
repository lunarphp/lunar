<div x-data="{ showTranslations: {{ $expanded ? 'true' : 'false' }} }">
    <div class="flex items-center gap-2">
        <div x-show="showTranslations"
             x-cloak>
            <span class="px-2 py-1 text-xs text-gray-600 uppercase bg-gray-100 border rounded">
                {{ $default->code }}
            </span>
        </div>

        <div class="w-full">
            {{ $slot }}
        </div>
    </div>

    @if ($languages->count())
        <div class="mt-2 space-y-2"
             x-show="showTranslations"
             x-cloak>
            @foreach ($languages as $language)
                @if (${"{$language->code}"} ?? null)
                    <div class="relative flex items-center gap-2"
                         wire:key="language-{{ $language->id }}">
                        <span class="px-2 py-1 text-xs text-gray-600 uppercase bg-gray-100 border rounded">
                            {{ $language->code }}
                        </span>

                        <div class="w-full">
                            {{ ${"{$language->code}"} ?? null }}
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if ($languages->count())
        <button x-on:click.prevent="showTranslations = !showTranslations"
                class="inline-flex items-center gap-2 px-2 py-1 mt-2 text-gray-700 bg-white border rounded shadow-sm hover:bg-gray-100"
                :class="{ 'bg-gray-100': showTranslations }">
            <x-hub::icon ref="translate"
                         class="w-4 h-4" />

            <span class="text-xs font-medium">Locales</span>
        </button>
    @endif
</div>
