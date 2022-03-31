<tfoot>
  <tr>
    <td colspan="50" class="flex-col p-12 space-y-4 text-sm text-center bg-white">
      <div class="text-gray-700">
        @if($slot->isEmpty())
          <strong>{{ __('adminhub::notifications.sorry') }}</strong> 
          {{ __('adminhub::notifications.search-results.none') }}
        @else
          {{ $slot }}
        @endif
      </div>
    </td>
  </tr>
</tfoot>
