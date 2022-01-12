<form wire:submit.prevent="create">
  {{ json_encode($errors->all()) }}
  <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('attributeGroup.name.' . $this->defaultLanguage->code)">
    <x-hub::translatable>
      <x-hub::input.text
        wire:model="attributeGroup.name.{{ $this->defaultLanguage->code }}"
        :error="$errors->first('attributeGroup.name.' . $this->defaultLanguage->code)"
        :placeholder="__('adminhub::components.attribute-groups.create.name.placeholder')"
      />
      @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
        <x-slot :name="$language->code">
          <x-hub::input.text
            wire:model="attributeGroup.name.{{ $language->code }}"
            :placeholder="__('adminhub::components.attribute-groups.create.name.placeholder')"
          />
        </x-slot>
      @endforeach
    </x-hub::translatable>
  </x-hub::input.group>

  <div class="mt-6">
    <x-hub::button>Create Attribute group</x-hub::button>
  </div>
</form>