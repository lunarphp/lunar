<x-dynamic-component 
    :component="$getFieldWrapperView()" 
    :field="$field" 
    x-data="{ showTranslations: {{ $getExpanded() ? 'true' : 'false' }} }"
    >
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" class="flex items-center gap-2">
        @if ($getLanguages()->count())
            <span
                class="items-center text-xs font-semibold p-2 rounded shadow-sm bg-gray-200 text-gray-950 dark:bg-white/5 dark:text-white uppercase">
                {{ $getDefault()->code }}
            </span>
        @endif
        <x-filament::input.wrapper class="w-full">
            <x-filament::input 
                type="text" 
                x-model="state.{{ $getDefault()->code }}" 
            />
        </x-filament::input.wrapper>
    </div>

    @if ($getLanguages()->count())
        @foreach ($getLanguages() as $language)
        <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" x-show="showTranslations" class="flex items-center gap-2">
            <span class="items-center text-xs font-semibold p-2 rounded shadow-sm bg-gray-200 text-gray-950 dark:bg-white/5 dark:text-white uppercase">
                {{ $language->code }}
            </span>
            <x-filament::input.wrapper class="w-full">
                <x-filament::input 
                    type="text" 
                    x-model="state.{{ $language->code }}" 
                />
                </x-filament::input.wrapper>
        </div>
        @endforeach
    @endif

    @if ($getLanguages()->count())
        <div class="mt-2">
            <x-filament::button 
                x-on:click.prevent="showTranslations = !showTranslations" 
                size="xs" 
                color="gray"
                >
                <x-filament::icon 
                    alias="lunar::languages" 
                    @class(['w-3.5 h-3.5 inline-flex'])
                />
                <span class="ml-2">
                    {{ __('lunarpanel::fieldtypes.translatedtext.form.locales') }}
                </span>
            </x-filament::button>
            </button>
        </div>
    @endif

</x-dynamic-component>
