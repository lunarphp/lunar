<x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
>
  <div class="grid grid-cols-3 gap-4">
    @foreach($getOptions() as $value => $label)
      <label
        @class([
            'border p-2 rounded hover:cursor-pointer',
            'border-primary-500 border-2' => $value == $getState()
        ])
      >
        <img src="{{ $label }}" class="rounded">
        <input type="radio" class="hidden" {{ $applyStateBindingModifiers('wire:model') }}.live="{{ $getStatePath() }}" value="{{ $value }}" />
      </label>
    @endforeach
  </div>
</x-dynamic-component>