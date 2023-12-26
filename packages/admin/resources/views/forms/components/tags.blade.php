<x-filament::fieldset
      :label="$getLabel()"
>
      @livewire('lunar.admin.livewire.components.tags', [
          'taggable' => $getRecord()
      ])
</x-filament::fieldset>
