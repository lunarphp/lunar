<x-hub::slideover
  :title="__(
    $attribute->id ? 'adminhub::components.attribute-edit.update_title' : 'adminhub::components.attribute-edit.create_title',
  )"
  wire:model="panelVisible"
>
  <div class="space-y-4">
    @if($attribute->system)
      <x-hub::alert level="danger">
        {{ __('adminhub::components.attribute-edit.system_locked') }}
      </x-hub::alert>
    @endif
    <x-hub::input.group
      :label="__('adminhub::inputs.name')"
      for="name"
      :error="$errors->first('attribute.name.' . $this->defaultLanguage->code)"
    >
      <x-hub::translatable>
        <x-hub::input.text
          wire:model="attribute.name.{{ $this->defaultLanguage->code }}"
          :error="$errors->first('attribute.name.' . $this->defaultLanguage->code)"
          :placeholder="__('adminhub::components.attribute-edit.name.placeholder')"
          wire:change="formatHandle"
        />
        @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
          <x-slot :name="$language->code">
            <x-hub::input.text
              wire:model="attributeGroup.name.{{ $language->code }}"
              :placeholder="__('adminhub::components.attribute-edit.name.placeholder')"
            />
          </x-slot>
        @endforeach
      </x-hub::translatable>
    </x-hub::input.group>

    <x-hub::input.group
      :label="__('adminhub::inputs.handle')"
      for="handle"
      :error="$errors->first('attribute.handle')"
    >
      <x-hub::input.text
        id="handle"
        wire:model.lazy="attribute.handle"
        wire:change="$set('manualHandle', true)"
        :error="$errors->first('attribute.handle')"
        :disabled="$attribute->system"
      />
    </x-hub::input.group>

    <div class="grid grid-cols-3">
      <x-hub::input.group
        :label="__('adminhub::inputs.required')"
        for="required"
        :error="$errors->first('attributeGroup.required')"
        :instructions="__('adminhub::components.attribute-edit.required.instructions')"
      >
        <x-hub::input.toggle :disabled="!!$attribute->system" id="required" wire:model="attribute.required" value="1" />
      </x-hub::input.group>

      <x-hub::input.group
        :label="__('adminhub::inputs.searchable.label')"
        for="searchable"
        :error="$errors->first('attributeGroup.searchable')"
        :instructions="__('adminhub::components.attribute-edit.searchable.instructions')"
      >
        <x-hub::input.toggle id="searchable" wire:model="attribute.searchable" />
      </x-hub::input.group>

      <x-hub::input.group
        :label="__('adminhub::inputs.filterable.label')"
        for="filterable"
        :error="$errors->first('attributeGroup.filterable')"
        :instructions="__('adminhub::components.attribute-edit.filterable.instructions')"
      >
        <x-hub::input.toggle :disabled="!!$attribute->system" id="filterable" wire:model="attribute.filterable" />
      </x-hub::input.group>
    </div>

    <x-hub::input.group
      :label="__('adminhub::inputs.validation_rules.label')"
      for="handle"
      :error="$errors->first('attribute.validation_rules')"
      :instructions="__('adminhub::components.attribute-edit.validation.instructions')"
    >
      <x-hub::input.text :disabled="!!$attribute->system" id="validation_rules" wire:model="attribute.validation_rules" />
    </x-hub::input.group>

    <x-hub::input.group
      :label="__('adminhub::inputs.type.label')"
      for="handle"
      :error="$errors->first('attribute.type')"
    >
      <x-hub::input.select wire:model="attribute.type" :disabled="!!$attribute->system">
        @foreach($this->fieldTypes as $fieldType)
          <option value="{{ get_class($fieldType) }}">{{ $fieldType->getLabel() }}</option>
        @endforeach
      </x-hub::input.select>
    </x-hub::input.group>

    @if($this->getFieldType()->getSettingsView())
      @include($this->getFieldType()->getSettingsView())
    @endif
  </div>

  <x-slot name="footer">
    <div class="flex justify-between">
      <x-hub::button theme="gray" type="button" wire:click="$set('panelVisible', false)">Cancel</x-hub::button>
      <x-hub::button wire:click="save">Save Attribute</x-hub::button>
    </div>
  </x-slot>
</x-hub::slideover>