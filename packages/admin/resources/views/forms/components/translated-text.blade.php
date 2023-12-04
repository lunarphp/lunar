<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>

        <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
            @foreach ($getLanguages() as $language)

                <div class="mt-2">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            x-model="state.{{ $language->code }}"
                        />
                    </x-filament::input.wrapper>
                </div>

            @endforeach
        </div>

</x-dynamic-component>
