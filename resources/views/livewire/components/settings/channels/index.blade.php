<div class="flex-col space-y-4">
  <div class="text-right">
    <x-hub::button tag="a" href="{{ route('hub.channels.create') }}">
      {{ __('adminhub::settings.channels.index.create_btn') }}
    </x-hub::button>
  </div>
  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>
        {{ __('adminhub::global.default') }}
      </x-hub::table.heading>
      <x-hub::table.heading sortable>
        {{ __('adminhub::global.name') }}
      </x-hub::table.heading>
      <x-hub::table.heading>
        {{ __('adminhub::global.handle') }}
      </x-hub::table.heading>
      <x-hub::table.heading>
        {{ __('adminhub::global.url') }}
      </x-hub::table.heading>
      <x-hub::table.heading></x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($channels as $channel)
      <x-hub::table.row>
        <x-hub::table.cell>
          <span class="block w-3 h-3 border rounded-full @if($channel->default) border-green-500 bg-green-400 @endif"></span>
        </x-hub::table.cell>
        <x-hub::table.cell>
            {{ $channel->name }}
        </x-hub::table.cell>
        <x-hub::table.cell>{{ $channel->handle }}</x-hub::table.cell>
        <x-hub::table.cell>
          <div class="w-32 truncate">{{ $channel->url }}</div>
        </x-hub::table.cell>
        <x-hub::table.cell>
          <a href="{{ route('hub.channels.show', $channel->id) }}" class="text-indigo-500 hover:underline">
            {{ __('adminhub::settings.channels.index.table_row_action_text') }}
          </a>
        </x-hub::table.cell>
      </x-hub::table.row>
      @endforeach
    </x-slot>
  </x-hub::table>
  <div>
    {{ $channels->links() }}
  </div>
</div>
