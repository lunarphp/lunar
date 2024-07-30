<x-dynamic-component 
    :component="$getFieldWrapperView()" 
    :field="$field" 
    x-data="{ showTranslations: {{ $getExpanded() ? 'true' : 'false' }} }"
>   

    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <div class="flex items-center gap-2">
            @if ($getMoreLanguages()->count())
                <span x-show="showTranslations"
                    class="items-center w-8 place-content-center text-xs font-normal p-2 rounded shadow-sm bg-gray-200 text-gray-400 dark:bg-white/5 dark:text-white uppercase">
                    {{ Str::upper($getDefaultLanguage()->code) }}
                </span>
            @endif
            <x-filament::input.wrapper class="w-full">
                {{ $getComponentByLanguage($getDefaultLanguage()) }}
            </x-filament::input.wrapper>
        </div>

        @if ($getMoreLanguages()->count())
            @foreach ($getMoreLanguages() as $language)
                <div x-show="showTranslations" class="flex items-center gap-2 mt-4">
                    <span x-show="showTranslations"
                        class="w-8 text-xs font-normal p-2 rounded shadow-sm bg-gray-200 text-gray-400 dark:bg-white/5 dark:text-white uppercase">
                        {{ Str::upper($language->code) }}
                    </span>
                    <x-filament::input.wrapper class="w-full">
                        {{ $getComponentByLanguage($language) }}
                    </x-filament::input.wrapper>    
                </div>        
            @endforeach
        @endif
    </div>

    @if ($getMoreLanguages()->count())
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