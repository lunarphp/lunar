@php
    $fulfilment = $getRecord();
    $stateName = (string) $fulfilment->state;
    $stateColor = match ($stateName) {
        'pending' => 'gray',
        'in-progress' => 'warning',
        'shipped' => 'success',
        'cancelled', 'returned' => 'danger',
        default => 'gray',
    };
@endphp

<div class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-950 dark:text-white">
                {{ $fulfilment->reference }}
            </span>
            <x-filament::badge :color="$stateColor" size="sm">
                {{ $fulfilment->state->label() }}
            </x-filament::badge>
        </div>

        @if ($fulfilment->shipped_at)
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('lunarpanel::order.fulfilments.columns.shipped_at') }}:
                {{ $fulfilment->shipped_at->format('j M Y, H:i') }}
            </span>
        @endif
    </div>

    <div class="divide-y divide-gray-100 dark:divide-white/5 rounded-lg border border-gray-200 dark:border-white/10">
        @foreach ($fulfilment->lines as $line)
            <div class="flex items-center gap-3 p-2">
                @php($thumbnail = $line->orderLine?->purchasable?->getThumbnail()?->getUrl('small'))
                @if ($thumbnail)
                    <img src="{{ $thumbnail }}" alt="" class="h-10 w-10 rounded object-cover" />
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded bg-gray-100 dark:bg-white/5">
                        <x-filament::icon icon="heroicon-o-photo" class="h-5 w-5 text-gray-400" />
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                        {{ $line->orderLine?->description ?? '#'.$line->order_line_id }}
                    </p>
                    @if ($line->orderLine?->identifier)
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $line->orderLine->identifier }}
                        </p>
                    @endif
                </div>

                <div class="text-right">
                    <span class="text-sm font-medium text-gray-950 dark:text-white">
                        &times; {{ $line->quantity }}
                    </span>
                    @if ($line->orderLine)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $line->orderLine->format('unit_price') }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if ($fulfilment->tracking_number)
        <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-truck" class="h-4 w-4" />
            @if ($fulfilment->tracking_url)
                <a href="{{ $fulfilment->tracking_url }}" target="_blank" class="text-primary-600 hover:underline dark:text-primary-400">
                    {{ $fulfilment->tracking_number }}
                </a>
            @else
                <span>{{ $fulfilment->tracking_number }}</span>
            @endif
            @if ($fulfilment->shipping_method)
                <span>&middot; {{ $fulfilment->shipping_method }}</span>
            @endif
        </div>
    @endif
</div>
