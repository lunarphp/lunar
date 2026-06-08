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
                        @if ($this->splittingId === $fulfilment->id)
                            {{-- Split mode: confirm / cancel --}}
                            <x-filament::button
                                size="sm"
                                color="primary"
                                icon="heroicon-m-scissors"
                                wire:click="confirmSplit"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.split.confirm') }}
                            </x-filament::button>
                            <x-filament::button
                                size="sm"
                                color="gray"
                                wire:click="cancelSplit"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.split.cancel') }}
                            </x-filament::button>
                        @elseif ($this->splittingId === null)
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
                                    wire:click="startSplit({{ $fulfilment->id }})"
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
                        @endif
                    </div>
                </div>

                {{-- Lines --}}
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($fulfilment->lines as $line)
                        @php($orderLine = $line->orderLine)
                        @php($splitting = $this->splittingId === $fulfilment->id)
                        <li x-data="{ open: false }">
                            <div
                                @unless ($splitting) role="button" @click="open = ! open" @endunless
                                style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; @unless ($splitting) cursor:pointer; @endunless"
                            >
                                @php($thumbnail = $orderLine?->purchasable?->getThumbnail()?->getUrl('small'))
                                @if ($thumbnail)
                                    <img src="{{ $thumbnail }}" alt="" style="height:2.5rem; width:2.5rem; flex:none; border-radius:0.375rem; object-fit:cover;" />
                                @else
                                    <div style="display:flex; height:2.5rem; width:2.5rem; flex:none; align-items:center; justify-content:center; border-radius:0.375rem;" class="bg-gray-100 dark:bg-white/5">
                                        <x-filament::icon icon="heroicon-o-photo" class="h-5 w-5 text-gray-400" />
                                    </div>
                                @endif

                                <div style="flex:1 1 0%; min-width:0;">
                                    <p class="text-sm font-medium text-gray-950 dark:text-white" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        {{ $orderLine?->description ?? '#'.$line->order_line_id }}
                                    </p>
                                    @if ($orderLine?->identifier)
                                        <p class="text-xs text-gray-500 dark:text-gray-400" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                            {{ $orderLine->identifier }}
                                        </p>
                                    @endif
                                    @php($options = $orderLine?->purchasable?->getOptions())
                                    @if (filled($options))
                                        <div style="display:flex; flex-wrap:wrap; gap:0.25rem; margin-top:0.25rem;">
                                            @foreach ($options as $option)
                                                <x-filament::badge color="info" size="sm">{{ $option }}</x-filament::badge>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if ($splitting)
                                    <div style="flex:none; display:flex; align-items:center; gap:0.5rem;">
                                        <input
                                            type="number"
                                            min="0"
                                            max="{{ $line->quantity }}"
                                            wire:model="splitQuantities.{{ $line->order_line_id }}"
                                            style="width:4.5rem; text-align:center; padding:0.3rem 0.5rem; border-radius:0.5rem; border:1px solid rgba(0,0,0,0.15); background:transparent;"
                                            class="text-gray-950 dark:text-white"
                                        />
                                        <span class="text-xs text-gray-500 dark:text-gray-400" style="white-space:nowrap;">
                                            {{ __('lunarpanel::order.fulfilments.fields.of', ['count' => $line->quantity]) }}
                                        </span>
                                    </div>
                                @elseif ($orderLine)
                                    <div style="flex:none; text-align:right; white-space:nowrap;" class="text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $line->quantity }} @ {{ $orderLine->format('sub_total') }}
                                    </div>
                                @endif

                                @unless ($splitting)
                                    <x-filament::icon
                                        icon="heroicon-m-chevron-down"
                                        class="h-5 w-5 text-gray-400"
                                        style="flex:none; transition:transform .2s ease;"
                                        x-bind:style="open && 'transform: rotate(180deg)'"
                                    />
                                @endunless
                            </div>

                            @php($details = $splitting ? [] : $this->lineDetails($line))
                            @if ($details)
                                <div x-show="open" x-collapse style="display:none;">
                                    <div style="margin:0 1rem 1rem 4rem; padding:1rem; border-radius:0.75rem; background:rgba(0,0,0,0.025); border:1px solid rgba(0,0,0,0.08);">
                                        @php($stock = $orderLine?->purchasable?->stock)
                                        @if (! is_null($stock))
                                            <p style="margin-bottom:0.75rem; color:#16a34a; font-weight:600;">
                                                {{ __('lunarpanel::order.fulfilments.fields.stock_level', ['count' => $stock]) }}
                                            </p>
                                        @endif

                                        <div style="border-radius:0.5rem; overflow:hidden; border:1px solid rgba(0,0,0,0.08);">
                                            @foreach ($details as $row)
                                                <div style="display:flex; justify-content:space-between; padding:0.5rem 0.75rem; @unless ($loop->last) border-bottom:1px solid rgba(0,0,0,0.06); @endunless">
                                                    <span @class(['font-semibold' => $row['highlight'] ?? false]) class="text-gray-700 dark:text-gray-300">{{ $row['label'] }}</span>
                                                    <span @class(['font-semibold' => $row['highlight'] ?? false, 'font-medium' => ! ($row['highlight'] ?? false)]) class="text-gray-950 dark:text-white">{{ $row['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if (filled($orderLine?->notes))
                                            <p style="margin-top:0.75rem;" class="text-xs italic text-gray-500 dark:text-gray-400">{{ $orderLine->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
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
