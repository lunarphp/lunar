<form wire:submit.prevent="create">
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
