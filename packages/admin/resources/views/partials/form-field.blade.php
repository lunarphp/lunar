<x-hub::input.group
        for="{{ $field->name }}"
        :label="__($field->label ?? 'adminhub::forms.'.strtolower(class_basename($model)).'.'.Str::snake($field->name))"
        :error="$errors->first('model.'.$field->name.($this->defaultLanguage ? '.'.$this->defaultLanguage->code : ''))">
    @switch(class_basename($field))
        @case('Text')
            <x-hub::input.text
                    wire:model="{{ $field->modelName }}"
                    :error="$errors->first('model.'.$field->name)" />
            @break
        @case('Select')
            <x-hub::input.select
                    wire:model="{{ $field->modelName }}"
                    :error="$errors->first('model.'.$field->name)">
                <option value="0">
                    {{ __('adminhub::forms.default.choose_option') }}
                </option>
                @foreach ($field->options as $option)
                    <option value="{{ $option['value'] ?? $option }}">
                        {{ $option['label'] ?? $option }}
                    </option>
                @endforeach
            </x-hub::input.select>
            @break
        @case('Tags')
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
