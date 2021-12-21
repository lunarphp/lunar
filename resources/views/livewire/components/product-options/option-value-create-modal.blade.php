<div>
  <x-hub::modal.dialog wire:model="formVisible">
    <x-slot name="title">
      {{ __('adminhub::components.ovcm.title', [
        'name' => $option ? $option->translate('name') : null
      ])}}
    </x-slot>
    <x-slot name="content">
      <x-hub::input.group label="Option Value" for="optionValue" :error="$errors->first('name.'.$this->defaultLanguage->code)">
        <div class="flex space-x-4">
          <div class="relative w-full">
            <x-hub::translatable :expanded="true">
              <x-hub::input.text
                wire:model="name.{{ $this->defaultLanguage->code }}"
                :error="$errors->first('name.'.$this->defaultLanguage->code)"
                placeholder="Blue, Small, Plastic, Metal etc..."
              />
              @foreach($this->languages->filter(fn ($lang) => !$lang->default) as $language)
                <x-slot :name="$language->code">
                  <x-hub::input.text
                    wire:model="name.{{ $language->code }}"
                    :error="$errors->first('name.'.$language->code)"
                  />
                </x-slot>
              @endforeach
            </x-hub::translatable>
          </div>
        </div>
      </x-hub::input.group>
    </x-slot>
    <x-slot name="footer">
      <x-hub::button type="button" theme="gray" wire:click.prevent="cancel">Cancel</x-hub::button>
      <x-hub::button type="button" wire:click.prevent="addNewValue">Save &amp; Continue</x-hub::button>
      @if($canPersist)
        <x-hub::button type="button" wire:click.prevent="addNewValue(true)" theme="green">Save and add another</x-hub::button>
      @endif
    </x-slot>
  </x-hub::modal.dialog>
</div>
