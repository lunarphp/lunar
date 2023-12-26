<x-dynamic-component 
    :component="$getFieldWrapperView()"
    :field="$field" 
    x-data="{ showTranslations: {{ $getExpanded() ? 'true' : 'false' }} }">

    <x-filament::input.wrapper>
        <x-slot name="prefix" x-show="showTranslations" class="uppercase">
            {{ $getDefault()->code }}
        </x-slot>
        <x-filament::input type="text" x-model="state.{{ $getDefault()->code }}" />
    </x-filament::input.wrapper>

    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" x-show="showTranslations">
        @foreach ($getLanguages() as $language)
            <x-filament::input.wrapper>
                <x-slot name="prefix" x-show="showTranslations" class="">
                    {{ $language->code }}
                </x-slot>
                <x-filament::input type="text" x-model="state.{{ $language->code }}" />
            </x-filament::input.wrapper>
        @endforeach
    </div>

    @if ($getLanguages()->count())
        <x-filament::button x-on:click.prevent="showTranslations = !showTranslations" size="xs" color="gray">
            <x-filament::icon alias="lunar::translate" @class(['w-3.5 h-3.5 inline-flex mrs-2']) />
            <span>Locales</span>
        </x-filament::button>
        </button>
    @endif

</x-dynamic-component>
