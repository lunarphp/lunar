<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>

        <div 
            class="flex flex-col gap-2"
            x-data="{
                state: $wire.$entangle('{{ $getStatePath() }}'),
                expanded: false,
                expand(){
                    this.expanded = true
                },
                collapse(){
                    this.expanded = false
                }
            }"
            @expand-translated-text-field.window="expand"
            @collapse-translated-text-field.window="collapse"
        >
            @capture($field, $language)
                <x-filament::input.wrapper>
                    <x-slot name="prefix">
                        <div class="flex justify-center w-8">{{ $language->code }}</div>
                    </x-slot>
                    <x-filament::input
                        type="text"
                        x-model="state.{{ $language->code }}"
                    />
                </x-filament::input.wrapper>
            @endcapture

            {{ $field($getDefaultLanguage()) }}

            @php
                $moreLanguages = $getLanguages()->where('default', false);
            @endphp
            
            @if(count($moreLanguages))
                <div
                    class="flex flex-col gap-2"
                    x-show="expanded"
                    x-transition
                >
                    @foreach ($moreLanguages as $language)
                        {{ $field($language) }}
                    @endforeach
                </div>
                
                <x-filament::link 
                    tag="button"
                    x-show="!expanded"
                    x-on:click="expand"
                >
                    {{ __('lunarpanel::global.translation.show-more') }}
                </x-filament::link>
            @endif
        </div>

</x-dynamic-component>
