<div class="space-y-4">
  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.firstname')" for="first_name">
      <x-hub::input.text wire:model="{{ $model }}.first_name" id="first_name" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.lastname')" for="last_name">
      <x-hub::input.text wire:model="{{ $model }}.last_name" id="last_name" />
    </x-hub::input.group>
  </div>

  <x-hub::input.group :label="__('adminhub::inputs.company_name.label')" for="company_name">
    <x-hub::input.text wire:model="{{ $model }}.company_name" id="company_name" />
  </x-hub::input.group>

  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.phone.label')" for="contact_phone">
      <x-hub::input.text wire:model="{{ $model }}.contact_phone" id="contact_phone" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.email')" for="contact_email">
      <x-hub::input.text wire:model="{{ $model }}.contact_email" type="email" id="contact_email" />
    </x-hub::input.group>
  </div>

  <x-hub::input.group :label="__('adminhub::inputs.address_line_one.label')" for="line_one">
    <x-hub::input.text wire:model="{{ $model }}.line_one" id="line_one" />
  </x-hub::input.group>

  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.address_line_two.label')" for="line_two">
      <x-hub::input.text wire:model="{{ $model }}.line_two" id="line_two" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.address_line_three.label')" for="line_three">
      <x-hub::input.text wire:model="{{ $model }}.line_three" id="line_three" />
    </x-hub::input.group>
  </div>

  <div class="grid grid-cols-3 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.city.label')" for="city">
      <x-hub::input.text wire:model="{{ $model }}.city" id="city" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.state.label')" for="state">
      <x-hub::input.text wire:model="{{ $model }}.state" id="state" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.postcode.label')" for="postcode">
      <x-hub::input.text wire:model="{{ $model }}.postcode" id="postcode" />
    </x-hub::input.group>
  </div>

  <x-hub::input.group :label="__('adminhub::inputs.country.label')" for="country">
    <x-hub::input.select wire:model="{{ $model }}.country_id" id="country">
      @foreach($this->countries as $country)
        <option value="{{ $country->id }}">{{ $country->name }}</option>
      @endforeach
    </x-hub::input.select>
  </x-hub::input.group>
</div>