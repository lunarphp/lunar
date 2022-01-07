<div>
<form wire:submit.prevent="create" class="space-y-4">
    <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('name.' . $this->defaultLanguage->code)">
      <x-hub::translatable>
        <x-hub::input.text
          wire:model="name.{{ $this->defaultLanguage->code }}"
          :error="$errors->first('name.' . $this->defaultLanguage->code)"
          :placeholder="__('adminhub::components.products.option-creator.option_placeholder')"
        />
        @foreach($languages->filter(fn ($lang) => !$lang->default) as $language)
          <x-slot :name="$language->code">
            <x-hub::input.text wire:model="name.{{ $language->code }}" />
          </x-slot>
        @endforeach
      </x-hub::translatable>

    </x-hub::input.group>

    <div class="mt-4 space-y-4">
      <header>
        <h3 class="font-medium leading-6 text-gray-900 text-md">
          {{ __('adminhub::components.products.option-creator.values_title') }}
        </h3>
        <p class="text-sm text-gray-500">
          {{ __('adminhub::components.products.option-creator.values_strapline') }}
        </p>
      </header>
      @forelse($values as $key => $value)
        <x-hub::input.group
          :label="'Value '. $loop->index + 1"
          for="name"
          :error="$errors->first('values.'.$loop->index.'.name.'.$this->defaultLanguage->code)"
        >
          <div class="flex space-x-4">
            <div class="relative w-full">
              <x-hub::translatable>
                <x-hub::input.text
                  wire:model="values.{{ $key }}.name.{{ $this->defaultLanguage->code }}"
                  :error="$errors->first('values.'.$loop->index.'.name.'.$this->defaultLanguage->code)"
                  :placeholder="__('adminhub::components.products.option-creator.value_placeholder')"
                />
                @foreach($languages->filter(fn ($lang) => !$lang->default) as $language)
                  <x-slot :name="$language->code">
                    <x-hub::input.text
                      wire:model="values.{{ $key }}.name.{{ $language->code }}"
                      :error="$errors->first('values.'.$loop->index.'.name.'.$language->code)"
                    />
                  </x-slot>
                @endforeach
              </x-hub::translatable>
            </div>
            <div>
              <x-hub::button size="sm" theme="danger" type="button" wire:click.prevent="removeValue('{{ $key }}')">
                <x-hub::icon ref="trash" style="solid" class="w-4" />
              </x-hub::button>
            </div>
          </div>
        </x-hub::input.group>
      @empty
        <x-hub::alert>
          {{ __('adminhub::components.products.option-creator.min_values_notice', [
            'min' => 1,
          ]) }}
        </x-hub::alert>
      @endforelse

      <button type="button" wire:click="addValue" class="w-full py-2 text-sm bg-gray-100 border-none rounded hover:bg-gray-200">
        {{ __('adminhub::components.products.option-creator.add_value_btn') }}
      </button>
    </div>

    @if($errors->first('values'))
      <x-hub::alert level="danger">{{ $errors->first('values') }}</x-hub::alert>
    @endif

    <div class="pt-4 mt-4 text-right border-t">
      <x-hub::button type="submit">{{ __('adminhub::components.products.option-creator.create_option_btn') }}</x-hub::button>
    </div>
  </form>
</div>
