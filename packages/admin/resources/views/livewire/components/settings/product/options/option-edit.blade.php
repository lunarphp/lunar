<form wire:submit.prevent="create" class="space-y-4">
  <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('productOption.name.' . $this->defaultLanguage->code)">
    <x-hub::translatable>
      <x-hub::input.text
        wire:model.defer="productOption.name.{{ $this->defaultLanguage->code }}"
        :error="$errors->first('productOption.name.' . $this->defaultLanguage->code)"
        :placeholder="__('adminhub::components.option.value.edit.name.placeholder')"
      />
      @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
        <x-slot :name="$language['code']">
          <x-hub::input.text
            wire:model="productOption.name.{{ $language->code }}"
            :placeholder="__('adminhub::components.attribute-group-edit.name.placeholder')"
          />
        </x-slot>
      @endforeach
    </x-hub::translatable>
  </x-hub::input.group>

  <x-hub::input.group required :label="__('adminhub::inputs.handle')" for="handle" :error="$errors->first('productOption.handle')">
    <x-hub::input.text
      wire:model.defer="productOption.handle"
      id="handle"
      :error="$errors->first('productOption.handle')"
     />
  </x-hub::input.group>


  <div class="mt-6">
    <x-hub::button>
      {{ __($productOption->id ? 'adminhub::components.option-edit.update_btn' : 'adminhub::components.option-edit.create_btn') }}
    </x-hub::button>
  </div>
</form>
