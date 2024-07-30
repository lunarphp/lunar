<x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
>

    @forelse($getAttributeGroups() as $group)
        <x-filament::fieldset
                :label="$group->translate('name')"
        >
            <div class="space-y-2">
                @forelse($getAttributes($group->id) as $attribute)
                    <label class="block hover:cursor-pointer">
                        <x-filament::section compact>
                            <x-filament::input.checkbox
                                    wire:model="{{ $getStatePath() }}"
                                    value="{{ $attribute->id }}"
                            />
                            <span class="ml-2">{{ $attribute->translate('name') }}</span>
                        </x-filament::section>
                    </label>
                @empty
                    {{ __('lunarpanel::producttype.attributes.no_attributes') }}
                @endforelse
            </div>
        </x-filament::fieldset>
    @empty
        {{ __('lunarpanel::producttype.attributes.no_groups') }}
    @endforelse
</x-dynamic-component>