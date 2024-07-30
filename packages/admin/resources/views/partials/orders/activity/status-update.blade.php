<div class="flex items-center">
    {{ __('lunarpanel::components.activity-log.partials.orders.status_change') }}
    <div class="flex items-center ml-2">
      <x-filament::badge :color="$previousStatusColor">
        {{ $previousStatusLabel }}
      </x-filament::badge>

      @svg('heroicon-m-chevron-right', [
        'class' => 'w-4 mx-1'
      ])
      
      <x-filament::badge :color="$newStatusColor">
          {{ $newStatusLabel }}
      </x-filament::badge>
    </div>
</div>
