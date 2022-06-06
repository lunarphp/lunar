<div class="space-y-4">
    <x-hub::input.group label="Name" for="name" :error="$errors->first('name.' . $this->defaultLanguage->code)">
      <x-hub::translatable>
        <x-hub::input.text wire:model="name.{{ $this->defaultLanguage->code }}" :error="$errors->first('name.' . $this->defaultLanguage->code)" />
        @foreach($languages->filter(fn ($lang) => !$lang->default) as $language)
          <x-slot :name="$language['code']">
            <x-hub::input.text wire:model="name.{{ $language->code }}" />
          </x-slot>
        @endforeach
      </x-hub::translatable>

    </x-hub::input.group>

    <div class="space-y-4">
      @foreach($values as $key => $value)
        <x-hub::input.group
          :label="'Value '. $loop->index + 1"
          for="name"
          :error="$errors->first('values.'.$loop->index.'.name.'.$this->defaultLanguage->code)"
        >
          <div class="relative">
            <x-hub::translatable>
              <x-hub::input.text
                wire:model="values.{{ $key }}.name.{{ $this->defaultLanguage->code }}"
                :error="$errors->first('values.'.$loop->index.'.name.'.$this->defaultLanguage->code)"
              />
              @foreach($languages->filter(fn ($lang) => !$lang->default) as $language)
                <x-slot :name="$language['code']">
                  <x-hub::input.text
                    wire:model="values.{{ $key }}.name.{{ $language->code }}"
                    :error="$errors->first('values.'.$loop->index.'.name.'.$language->code)"
                  />
                </x-slot>
              @endforeach
            </x-hub::translatable>
          </div>
        </x-hub::input.group>
      @endforeach

      <x-hub::button theme="gray" wire:click="addValue">{{ __('adminhub::components.products.option-creator.add_value_btn') }}</x-hub::button>
    </div>

    @if($errors->first('values'))
      <x-hub::alert level="danger">{{ $errors->first('values') }}</x-hub::alert>
    @endif
  </div>

  <x-hub::button wire:click="save">{{ __('adminhub::components.products.option-creator.create_option_btn') }}</x-hub::button>
