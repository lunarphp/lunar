<div class="flex-col space-y-4">
  @if($this->isLocked)
  <x-hub::alert level="danger">
    {{ __('adminhub::settings.attributes.show.locked') }}
  </x-hub::alert>
  @endif
  <div class="shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6" x-data x-init="
      generateHandle = (value) => @this.generateHandle(value)
    ">
      <x-hub::input.group
        :label="__('adminhub::inputs.name')"
        for="name"
        :error="$errors->first('attribute.name.'.$this->defaultLanguage->code)"
      >
        <x-hub::translatable>
          <x-hub::input.text
            wire:model="attribute.name.{{ $this->defaultLanguage->code }}"
            @keyup="generateHandle($event.target.value)"
            id="name"
            :error="$errors->first('attribute.name.'.$this->defaultLanguage->code)"
            :disabled="$this->isLocked"
          />
          @foreach($this->languages as $language)
            <x-slot :name="$language['code']">
              <x-hub::input.text wire:model="attribute.name.{{ $language->code }}" />
            </x-slot>
          @endforeach
        </x-hub::translatable>
      </x-hub::input.group>

      <x-hub::input.group
        :label="__('adminhub::inputs.handle')"
        for="handle"
        :error="$errors->first('attribute.handle')"
      >
        <x-hub::input.text id="handle" wire:model="attribute.handle" wire:change="$set('manualHandle', true)" :error="$errors->first('attribute.handle')" :disabled="$this->isLocked" />
      </x-hub::input.group>

      <x-hub::input.group
        :label="__('adminhub::inputs.required')"
        for="required"
        :error="$errors->first('attribute.required')"
      >
        <x-hub::input.toggle id="handle" wire:model="attribute.required" :error="$errors->first('attribute.required')" :disabled="$this->isLocked" />
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.attribute_type.label')" for="attributeType" :instructions="__('adminhub::inputs.attribute_type.instructions')">
        <x-hub::input.select wire:model="attribute.attribute_type" id="attributeType" :disabled="$this->isLocked">
          @foreach($this->attributeTypes as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
          @endforeach
        </x-hub::input.select>
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.attribute_group')" :error="$errors->first('attribute.attribute_group_id')" for="group">
        <x-hub::input.select wire:model="attribute.attribute_group_id" :disabled="$this->isLocked" :error="$errors->first('attribute.attribute_group_id')">
          <option value readonly>{{ __('adminhub::inputs.select_attribute_group') }}</option>
          @foreach($this->attributeGroups as $group)
            <option value="{{ $group->id }}">{{ $group->translate('name') }}</option>
          @endforeach
        </x-hub::input.select>
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.type.label')" for="type" :instructions="__('adminhub::inputs.type.instructions')">
        <x-hub::input.select wire:model="attribute.type" :disabled="$this->isLocked" required>
          @foreach($this->types as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
          @endforeach
        </x-hub::input.select>
      </x-hub::input.group>

      @if($this->configType == 'text')
        <x-hub::input.group
          label="Field Type"
          for="fieldType"
          :error="$errors->first('attribute.configuration.type')"
        >
          <x-hub::input.select wire:model="attribute.configuration.type" id="fieldType" :error="$errors->first('attribute.configuration.type')" :disabled="$this->isLocked">
            <option value>{{ __('adminhub::inputs.select_field_type') }}</option>
            <option value="text">{{ __('adminhub::inputs.text') }}</option>
            <option value="richtext">{{ __('adminhub::inputs.richtext') }}</option>
          </x-hub::input.select>
        </x-hub::input.group>
      @endif
    </div>
    <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
      <x-hub::button type="submit" :disabled="$this->isLocked">
        {{ __(
          $attribute->id ? 'adminhub::settings.attributes.form.update_btn' : 'adminhub::settings.attributes.form.create_btn'
        ) }}
      </x-hub::button>
    </div>
  </div>

  @if($attribute->id && !$attribute->system && !$attribute->wasRecentlyCreated)
    <div class="bg-white border border-red-300 rounded shadow">
      <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
        {{ __('adminhub::inputs.danger_zone.title') }}
      </header>
      <div class="p-6 space-y-4 text-sm">
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-12 md:col-span-6">
            <strong>{{ __('adminhub::inputs.danger_zone.label', [
              'model' => __('adminhub::types.attribute')
            ]) }}</strong>
            <p class="text-xs text-gray-600">{{ __('adminhub::inputs.danger_zone.instructions', [
              'model' => 'attribute',
              'attribute' => 'handle',
            ]) }}</p>
          </div>
          <div class="col-span-9 lg:col-span-4">
            <x-hub::input.text type="email" wire:model="deleteConfirm" />
          </div>
          <div class="col-span-3 text-right lg:col-span-2">
            <x-hub::button theme="danger" :disabled="!$this->canDelete" wire:click="delete" type="button">{{ __('adminhub::global.delete') }}</x-hub::button>
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
