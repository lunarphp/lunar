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
    <p class="text-sm">RichText fields use <a href="https://quilljs.com/docs/configuration/" class="text-blue-500">Quilljs</a>, you can you enter any available configuration below.</p>
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
