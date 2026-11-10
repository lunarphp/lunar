<x-mail::table>
| {{ __('lunar::notifications.partials.item') }} | {{ __('lunar::notifications.partials.quantity') }} |
|:--|--:|
@foreach ($lines as $line)
| {{ \Lunar\Core\Notifications\MailTable::cell($line['description']) }}@if (filled($line['option'] ?? null)) ({{ \Lunar\Core\Notifications\MailTable::cell($line['option']) }})@endif | {{ $line['quantity'] }} |
@endforeach
</x-mail::table>
