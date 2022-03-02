<div class="space-y-4">
  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.firstname')" :error="$errors->first($model.'.first_name')" for="first_name">
      <x-hub::input.text wire:model="{{ $model }}.first_name" id="first_name" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.lastname')" :error="$errors->first($model.'.last_name')" for="last_name">
      <x-hub::input.text wire:model="{{ $model }}.last_name" id="last_name" />
    </x-hub::input.group>
  </div>

  <x-hub::input.group :label="__('adminhub::inputs.company_name.label')" :error="$errors->first($model.'.company_name')" for="company_name">
    <x-hub::input.text wire:model="{{ $model }}.company_name" id="company_name" />
  </x-hub::input.group>

  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.phone.label')" :error="$errors->first($model.'.contact_phone')" for="contact_phone">
      <x-hub::input.text wire:model="{{ $model }}.contact_phone" id="contact_phone" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.email')" :error="$errors->first($model.'.contact_email')" for="contact_email">
      <x-hub::input.text wire:model="{{ $model }}.contact_email" type="email" id="contact_email" />
    </x-hub::input.group>
  </div>

  <x-hub::input.group :label="__('adminhub::inputs.address_line_one.label')" :error="$errors->first($model.'.line_one')" for="line_one">
    <x-hub::input.text wire:model="{{ $model }}.line_one" id="line_one" />
  </x-hub::input.group>

  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.address_line_two.label')" :error="$errors->first($model.'.line_two')" for="line_two">
      <x-hub::input.text wire:model="{{ $model }}.line_two" id="line_two" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.address_line_three.label')" :error="$errors->first($model.'.line_three')" for="line_three">
      <x-hub::input.text wire:model="{{ $model }}.line_three" id="line_three" />
    </x-hub::input.group>
  </div>

  <div class="grid grid-cols-3 gap-4">
    <x-hub::input.group :label="__('adminhub::inputs.city.label')" :error="$errors->first($model.'.city')" for="city">
      <x-hub::input.text wire:model="{{ $model }}.city" id="city" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.state.label')" :error="$errors->first($model.'.state')" for="state">
      <x-hub::input.text wire:model="{{ $model }}.state" id="state" />
    </x-hub::input.group>

    <x-hub::input.group :label="__('adminhub::inputs.postcode.label')" :error="$errors->first($model.'.postcode')" for="postcode" required>
      <x-hub::input.text wire:model="{{ $model }}.postcode" id="postcode" required />
    </x-hub::input.group>
  </div>

  <x-hub::input.group :label="__('adminhub::inputs.country.label')" :error="$errors->first($model.'.country_id')" for="country" required>
    <x-hub::input.select wire:model="{{ $model }}.country_id" id="country" required>
      @foreach($this->countries as $country)
        <option value="{{ $country->id }}">{{ $country->name }}</option>
      @endforeach
    </x-hub::input.select>
  </x-hub::input.group>
</div>