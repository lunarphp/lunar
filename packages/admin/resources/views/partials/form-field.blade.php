<x-hub::input.group
        for="{{ $field->name }}"
        :label="__($field->label ?? 'adminhub::inputs.'.$field->name)"
        :error="$errors->first('model.'.$field->name.'.'.$this->defaultLanguage->code)">
    @switch(class_basename($field))
        @case('Text')
            <x-hub::input.text
                    wire:model="{{ $field->modelName }}"
                    :error="$errors->first('model.'.$field->name)" />
            @break
        @case('Toggle')
            <x-hub::input.toggle
                    wire:model="{{ $field->modelName }}"
                    :disabled="$model->id && $model->getOriginal('default')"
                    value="1" />
            @break
    @endswitch
</x-hub::input.group>
