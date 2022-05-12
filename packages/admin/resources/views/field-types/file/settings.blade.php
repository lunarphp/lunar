<div class="space-y-4">

  <x-hub::input.group
    label="Disk"
    for="disk"
    :error="$errors->first('attribute.configuration.disk')"
    :disabled="!!$attribute->system"
    required
  >
   <x-hub::input.select wire:model.defer="attribute.configuration.disk" id="disk">
    @foreach(config('filesystems.disks', []) as $disk => $config)
      <option value="{{ $disk }}" @if(config('filesystems.default') == $disk) selected @endif>{{ $disk }}</option>
    @endforeach
   </x-hub::input.select>
  </x-hub::input.group>

  <x-hub::input.group
    label="Path"
    for="path"
    :error="$errors->first('attribute.configuration.path')"
    :disabled="!!$attribute->system"
  >
   <x-hub::input.text wire:model.defer="attribute.configuration.path" id="path" />
  </x-hub::input.group>

</div>
