<x-filament-widgets::widget>
  @if(!$this->configuringOptions)
    {{ $this->table }}

    <pre>
        {{ json_encode($this->variants) }}
    </pre>
  @else
      <x-filament::button color="gray" wire:click="cancelOptionConfiguring">Cancel</x-filament::button>
      <x-filament::button>Save Options</x-filament::button>


  @endif


</x-filament-widgets::widget>
