<tfoot>
  <tr>
    <td colspan="50" class="flex-col p-12 space-y-4 text-sm text-center bg-white">
      <div class="text-gray-700">
        @if($slot->isEmpty())
          <strong>Sorry!</strong> We were unable to find any results based on your search.
        @else
          {{ $slot }}
        @endif
      </div>
    </td>
  </tr>
</tfoot>
