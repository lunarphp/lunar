<div>
    @php
        $stateColors = [
            'pending' => 'gray',
            'in-progress' => 'warning',
            'shipped' => 'success',
            'cancelled' => 'danger',
            'returned' => 'danger',
        ];
    @endphp

    <div class="space-y-4">
        @forelse ($this->fulfilments as $fulfilment)
            @php
                $stateName = (string) $fulfilment->state;
                $isPreShip = in_array($stateName, ['pending', 'in-progress'], true);
            @endphp

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                {{-- Header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $fulfilment->reference }}
                        </span>
                        <x-filament::badge :color="$stateColors[$stateName] ?? 'gray'" size="sm">
                            {{ $fulfilment->state->label() }}
                        </x-filament::badge>
                        @if ($fulfilment->shipped_at)
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('lunarpanel::order.fulfilments.columns.shipped_at') }}
                                {{ $fulfilment->shipped_at->format('j M Y, H:i') }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if (\Lunar\Core\Actions\Fulfilment\ShipFulfilment::canRun($fulfilment))
                            <x-filament::button
                                size="sm"
                                color="success"
                                icon="heroicon-m-truck"
                                wire:click="mountAction('ship', { fulfilment: {{ $fulfilment->id }} })"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.ship.label') }}
                            </x-filament::button>
                        @endif

                        @if (\Lunar\Core\Actions\Fulfilment\SplitFulfilment::canRun($fulfilment) && $fulfilment->lines->sum('quantity') > 1)
                            <x-filament::button
                                size="sm"
                                color="gray"
                                icon="heroicon-m-scissors"
                                wire:click="mountAction('split', { fulfilment: {{ $fulfilment->id }} })"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.split.label') }}
                            </x-filament::button>
                        @endif

                        @if ($isPreShip && $this->mergeableCount > 1)
                            <x-filament::button
                                size="sm"
                                color="gray"
                                icon="heroicon-m-arrows-pointing-in"
                                wire:click="mountAction('merge', { fulfilment: {{ $fulfilment->id }} })"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.merge.label') }}
                            </x-filament::button>
                        @endif

                        @if (\Lunar\Core\Actions\Fulfilment\ReturnFulfilment::canRun($fulfilment))
                            <x-filament::button
                                size="sm"
                                color="warning"
                                icon="heroicon-m-arrow-uturn-left"
                                wire:click="mountAction('return', { fulfilment: {{ $fulfilment->id }} })"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.return.label') }}
                            </x-filament::button>
                        @endif
                    </div>
                </div>

                {{-- Lines --}}
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($fulfilment->lines as $line)
                        <li class="flex items-center gap-3 px-4 py-3">
                            @php($thumbnail = $line->orderLine?->purchasable?->getThumbnail()?->getUrl('small'))
                            @if ($thumbnail)
                                <img src="{{ $thumbnail }}" alt="" class="h-10 w-10 shrink-0 rounded-md object-cover" />
                            @else
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-gray-100 dark:bg-white/5">
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

                            <div class="shrink-0 text-right">
                                <span class="text-sm text-gray-500 dark:text-gray-400">&times; {{ $line->quantity }}</span>
                                @if ($line->orderLine)
                                    <p class="text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $line->orderLine->format('unit_price') }}
                                    </p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{-- Tracking --}}
                @if ($fulfilment->tracking_number)
                    <div class="flex items-center gap-1.5 border-t border-gray-100 px-4 py-2 text-xs text-gray-500 dark:border-white/5 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-m-truck" class="h-4 w-4" />
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
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('lunarpanel::order.fulfilments.empty') }}
            </p>
        @endforelse
    </div>

    <x-filament-actions::modals />
</div>
