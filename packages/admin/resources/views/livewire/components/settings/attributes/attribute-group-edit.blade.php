<form wire:submit.prevent="create" class="space-y-2">
  <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('attributeGroup.name.' . $this->defaultLanguage->code)">
    <x-hub::translatable>
      <x-hub::input.text
        wire:model="attributeGroup.name.{{ $this->defaultLanguage->code }}"
        :error="$errors->first('attributeGroup.name.' . $this->defaultLanguage->code)"
        :placeholder="__('adminhub::components.attribute-group-edit.name.placeholder')"
      />
      @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
        <x-slot :name="$language['code']">
          <x-hub::input.text
            wire:model="attributeGroup.name.{{ $language->code }}"
            :placeholder="__('adminhub::components.attribute-group-edit.name.placeholder')"
          />
        </x-slot>
      @endforeach
    </x-hub::translatable>
  </x-hub::input.group>

  <x-hub::input.group
    for="type"
    :label="__('adminhub::inputs.type.label')"
    :error="$errors->first('attributeGroup.type')"
  >
    <x-hub::input.select wire:model="attributeGroup.type">
      @foreach($this->groupTypes as $groupType => $groupTypeName)
        <option value="{{ $groupType }}">{{ $groupTypeName }}</option>
      @endforeach
    </x-hub::input.select>
  </x-hub::input.group>

  @if($attributeGroup->type === 'model')
    <x-hub::input.group
    for="type"
    :label="__('Model')"
    :error="$errors->first('attributeGroup.type')"
  >
    <x-hub::input.select wire:model="attributeGroup.source">
      @foreach($this->modelsCollection as $model)
        <option value="{{ $model }}">{{ $model }}</option>
      @endforeach
    </x-hub::input.select>
  </x-hub::input.group>
  @endif

  @if($attributeGroup->type === 'model' && $attributeGroup->source === \GetCandy\Models\Collection::class)
    <x-hub::input.group
    for="type"
    :label="__('Select Collection')"
    :error="$errors->first('attributeGroup.type')"
  >
    <x-hub::input.select wire:model="attributeGroup.source">
      @foreach($this->collectionGroups as $collectionGroup)
        <option value="{{ \GetCandy\Models\Collection::class.'::'.$collectionGroup }}">{{ $collectionGroup }}</option>
      @endforeach
    </x-hub::input.select>
  </x-hub::input.group>
  @endif

  @if($errors->has('attributeGroup.handle'))
    <div class="mt-4">
      <x-hub::alert level="danger">
        {{ __('adminhub::components.attribute-group-edit.non_unique_handle') }}
      </x-hub::alert>
    </div>
  @endif

  <div class="mt-6">
    <x-hub::button>
      {{ __($attributeGroup->id ? 'adminhub::components.attribute-group-edit.update_btn' : 'adminhub::components.attribute-group-edit.create_btn') }}
    </x-hub::button>
  </div>
</form>
