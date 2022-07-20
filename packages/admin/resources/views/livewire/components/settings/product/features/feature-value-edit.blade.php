<form wire:submit.prevent="save">
  <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('featureValue.name.' . $this->defaultLanguage->code)">
    <x-hub::translatable>
      <x-hub::input.text
              wire:model="featureValue.name.{{ $this->defaultLanguage->code }}"
              :error="$errors->first('featureValue.name.' . $this->defaultLanguage->code)"
              :placeholder="__('adminhub::components.feature-edit.name.placeholder')"
      />
      @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
        <x-slot :name="$language['code']">
          <x-hub::input.text
                  wire:model="featureValue.name.{{ $language->code }}"
                  :placeholder="__('adminhub::components.attribute-group-edit.name.placeholder')"
          />
        </x-slot>
      @endforeach
    </x-hub::translatable>
  </x-hub::input.group>

  <div class="mt-6">
    <x-hub::button>
      {{ __('adminhub::components.feature.value.edit.save_feature.value.btn') }}
    </x-hub::button>
  </div>
</form>
