
<div class="flex-col space-y-4">
  <div class="flex justify-between">
    @include('adminhub::partials.navigation.taxes')

    <x-hub::button tag="a" href="{{ route('hub.taxes.create') }}">
      {{ __('adminhub::settings.taxes.tax-zones.create_btn') }}
    </x-hub::button>
  </div>

  @livewire('hub.components.settings.taxes.tax-zones.table')
</div>
