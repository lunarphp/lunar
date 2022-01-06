<div class="flex-col space-y-4">
    <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>{{ __('adminhub::global.name') }}</x-hub::table.heading>
      <x-hub::table.heading>{{ __('adminhub::global.verified') }}</x-hub::table.heading>
      <x-hub::table.heading>{{ __('adminhub::global.licensed') }}</x-hub::table.heading>
      <x-hub::table.heading>{{ __('adminhub::global.current_version') }}</x-hub::table.heading>
      <x-hub::table.heading>{{ __('adminhub::global.latest_version') }}</x-hub::table.heading>
      <x-hub::table.heading>{{ __('adminhub::global.author') }}</x-hub::table.heading>
      <x-hub::table.heading></x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($addons as $addon)
      <x-hub::table.row wire:loading.class.delay="opacity-50">
        <x-hub::table.cell>{{ $addon['name'] }}</x-hub::table.cell>
        <x-hub::table.cell :class="$addon['marketplaceId'] ? 'text-green-500' : 'text-red-500'">
          <x-hub::icon :ref="$addon['marketplaceId'] ? 'check' : 'x'" class="w-4" style="solid" />
        </x-hub::table.cell>
        <x-hub::table.cell :class="$addon['licensed'] ? 'text-green-500' : 'text-red-500'">
          <x-hub::icon :ref="$addon['licensed'] ? 'check' : 'x'" class="w-4" style="solid" />
        </x-hub::table.cell>
        <x-hub::table.cell>
          {{ $addon['version'] }}
        </x-hub::table.cell>
        <x-hub::table.cell>
          {{ $addon['latestVersion'] }}
        </x-hub::table.cell>
        <x-hub::table.cell>
          {{ $addon['author'] }}
        </x-hub::table.cell>
        <x-hub::table.cell>
          <a href="{{ route('hub.addons.show', $addon['marketplaceId']) }}" class="text-indigo-500 hover:underline">
            {{ __('adminhub::settings.addons.index.table_row_action_text') }}
          </a>
        </x-hub::table.cell>
      </x-hub::table.row>
      @endforeach
    </x-slot>
    </x-hub::table>
  {{-- @foreach($addons as $addon)
    {{ dd($addon) }}
  @endforeach --}}
</div>
