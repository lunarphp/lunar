<div class="flex-col space-y-4">
  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>Event</x-hub::table.heading>
      <x-hub::table.heading>Subject ID</x-hub::table.heading>
      <x-hub::table.heading>Subject Type</x-hub::table.heading>
      <x-hub::table.heading>Performed By</x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($logs as $log)
      <x-hub::table.row>
        <x-hub::table.cell>{{ $log->event }}</x-hub::table.cell>
        <x-hub::table.cell>{{ $log->subject_id }}</x-hub::table.cell>
        <x-hub::table.cell>{{ class_basename($log->subject_type) }}</x-hub::table.cell>
        <x-hub::table.cell>
          @if($log->causer)
            {{ $log->causer->email }}
          @else
            <span class="px-3 py-1 text-xs font-medium text-gray-600 uppercase rounded bg-gray-50">System</span>
          @endif
        </x-hub::table.cell>
      </x-hub::table.row>
      @endforeach
    </x-slot>
  </x-hub::table>
  <div>
    {{ $logs->links() }}
  </div>
</div>
