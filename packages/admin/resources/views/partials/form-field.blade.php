<x-hub::input.group :label="__('adminhub::inputs.'.$field->name)" for="name" :error="$errors->first('model.'.$field->name.'.'.$this->defaultLanguage->code)">
    @switch(class_basename($field))
        @case('Text')
            <x-hub::input.text
                    wire:model="{{ $field->modelName }}"
                    :error="$errors->first('model.'.$field->name)" />
            @break
    @endswitch
</x-hub::input.group>
