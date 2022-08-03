<x-hub::input.group
  label="Richtext"
  for="richtext"
  :error="$errors->first('attribute.configuration.richtext')"
  :disabled="!!$attribute->system"
>
  <x-hub::input.toggle :disabled="!!$attribute->system" :on-value="true" :off-value="false" wire:model="attribute.configuration.richtext" id="fieldType" />
</x-hub::input.group>

@if($attribute->configuration['richtext'] ?? false)
  <div class="space-y-4 mt-4">
    <p class="text-sm">
      {!! __('adminhub::fieldtypes.richtext.config', [
        'url' => '<a href="https://quilljs.com/docs/configuration/" target="_blank" rel="nofollow" class="text-blue-500">Quilljs</a>'
      ]) !!}
    </p>
    @if($errors->count())
      <x-hub::alert level="danger">
        @foreach ($errors->all() as $error)
          {{ $error }}
        @endforeach
      </x-hub::alert>
    @endif
    <textarea wire:model.defer="attribute.configuration.options" class="w-full text-sm bg-gray-50 p-3 rounded font-mono" rows="20">{{ $attribute->configuration['options'] ?? null }}</textarea>
  </div>
@endif
