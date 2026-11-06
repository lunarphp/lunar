<x-mail::table>
| {{ __('lunar::notifications.partials.item') }} | {{ __('lunar::notifications.partials.quantity') }} |
|:--|--:|
@foreach ($lines as $line)
| {{ $line['description'] }}@if (filled($line['option'] ?? null)) ({{ $line['option'] }})@endif | {{ $line['quantity'] }} |
@endforeach
</x-mail::table>
