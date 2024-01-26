<x-dynamic-component :component="$getFieldWrapperView()" :field="$field" x-data="{ showTranslations: {{ $getExpanded() ? 'true' : 'false' }} }">
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" class="flex items-center gap-2">
        @if ($getLanguages()->count())
            <span
                class="items-center w-8 place-content-center text-xs font-normal p-2 rounded shadow-sm bg-gray-200 text-gray-400 dark:bg-white/5 dark:text-white uppercase">
                {{ Str::upper($getDefault()->code) }}
            </span>
        @endif

        @if ($getRichtext())
            <div class="w-full">
                @include('filament-forms::components.rich-editor')
            </div>
        @else
            <x-filament::input.wrapper class="w-full">
                <x-filament::input type="text" x-model="state.lunar_translatedtext_field.{{ $getDefault()->code }}" />
            </x-filament::input.wrapper>
        @endif

    </div>
  
    @if ($getLanguages()->count())
        @foreach ($getLanguages() as $language)
            <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" x-show="showTranslations" class="flex items-center gap-2">
                <span
                    class="w-8 text-xs font-normal p-2 rounded shadow-sm bg-gray-200 text-gray-400 dark:bg-white/5 dark:text-white uppercase">
                    {{ Str::upper($language->code) }}
                </span>

                @if ($getRichtext())
                    <div class="w-full">
                        @include('filament-forms::components.rich-editor')
                    </div>
                @else
                    <x-filament::input.wrapper class="w-full">
                        <x-filament::input type="text" x-model="state.lunar_translatedtext_field.{{ $language->code }}" />
                    </x-filament::input.wrapper>
                @endif
            </div>
        @endforeach
    @endif

    @if ($getLanguages()->count())
        <div class="mt-2">
            <x-filament::button x-on:click.prevent="showTranslations = !showTranslations" size="xs" color="gray" outlined>
                <x-filament::icon alias="lunar::languages" @class(['w-3.5 h-3.5 inline-flex']) />
                <span class="ml-2">
                    {{ __('lunarpanel::fieldtypes.translatedtext.form.locales') }}
                </span>
            </x-filament::button>
            </button>
        </div>
    @endif

</x-dynamic-component>
