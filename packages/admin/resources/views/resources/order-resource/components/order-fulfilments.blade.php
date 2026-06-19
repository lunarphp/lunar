<div>
    @php
        // The state badge derives its colour and icon from the state's fixed
        // category, not a per-state-name map — so a consumer's custom states
        // render sensibly with no blade change.
        $categoryColors = [
            'Outstanding' => 'warning',
            'Fulfilled' => 'success',
            'Returned' => 'danger',
            'Cancelled' => 'gray',
        ];
        $categoryIcons = [
            'Outstanding' => 'heroicon-m-clock',
            'Fulfilled' => 'heroicon-m-check-circle',
            'Returned' => 'heroicon-m-arrow-uturn-left',
            'Cancelled' => 'heroicon-m-x-circle',
        ];
    @endphp

    <div class="space-y-4">
        @forelse ($this->fulfilments as $fulfilment)
            @php
                $category = $fulfilment->state->category()->name;
            @endphp

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                {{-- Header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-white/10">
                    <div style="min-width:0;">
                        {{-- Primary: reference + status + method (+ hold) --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $fulfilment->reference ?: __('lunarpanel::order.fulfilments.unreferenced', ['id' => $fulfilment->id]) }}
                            </span>
                            <x-filament::badge :color="$categoryColors[$category] ?? 'gray'" :icon="$categoryIcons[$category] ?? null" size="sm">
                                {{ $fulfilment->state->label() }}
                            </x-filament::badge>
                            <x-filament::badge color="gray" size="sm">
                                {{ $fulfilment->method()->getLabel() }}
                            </x-filament::badge>
                            @if ($fulfilment->isOnHold())
                                <span @if ($fulfilment->hold_note) title="{{ $fulfilment->hold_note }}" @endif>
                                    <x-filament::badge color="warning" size="sm" icon="heroicon-m-pause-circle">
                                        {{ __('lunarpanel::order.fulfilments.on_hold') }}@if ($fulfilment->holdReasonLabel()): {{ $fulfilment->holdReasonLabel() }}@endif
                                    </x-filament::badge>
                                </span>
                            @endif
                        </div>

                        {{-- Secondary, muted: location / handed-over timestamp --}}
                        @if ($fulfilment->location || $fulfilment->shipped_at)
                            <div class="flex flex-wrap items-center text-xs text-gray-500 dark:text-gray-400" style="margin-top:0.25rem; gap:0.125rem 0.75rem;">
                                @if ($fulfilment->location)
                                    <span class="flex items-center gap-1">
                                        <x-filament::icon icon="heroicon-m-map-pin" class="h-3.5 w-3.5" />
                                        {{ $fulfilment->location->name }}
                                    </span>
                                @endif
                                @if ($fulfilment->shipped_at)
                                    <span>
                                        {{ $this->handedOverLabel($fulfilment) }}
                                        {{ $fulfilment->shipped_at->format('j M Y, H:i') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
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
                        @elseif ($this->mergingId === $fulfilment->id)
                            {{-- Merge mode: confirm / cancel --}}
                            <x-filament::button
                                size="sm"
                                color="primary"
                                icon="heroicon-m-arrows-pointing-in"
                                wire:click="confirmMerge"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.merge.confirm') }}
                            </x-filament::button>
                            <x-filament::button
                                size="sm"
                                color="gray"
                                wire:click="cancelMerge"
                            >
                                {{ __('lunarpanel::order.fulfilments.actions.merge.cancel') }}
                            </x-filament::button>
                        @elseif ($this->splittingId === null && $this->mergingId === null)
                            @php($transitions = $this->statusTransitions($fulfilment))
                            @if ($transitions->isNotEmpty())
                                <x-filament::dropdown placement="bottom-end">
                                    <x-slot name="trigger">
                                        <x-filament::button
                                            size="sm"
                                            color="primary"
                                            icon="heroicon-m-chevron-down"
                                            icon-position="after"
                                        >
                                            {{ __('lunarpanel::order.fulfilments.actions.update_status.label') }}
                                        </x-filament::button>
                                    </x-slot>

                                    <x-filament::dropdown.list>
                                        @foreach ($transitions as $transition)
                                            @php($mountArgs = $transition['action'] === 'transition'
                                                ? "mountAction('transition', { fulfilment: {$fulfilment->id}, state: '{$transition['name']}' })"
                                                : "mountAction('{$transition['action']}', { fulfilment: {$fulfilment->id} })")
                                            <x-filament::dropdown.list.item
                                                :icon="$categoryIcons[$transition['category']] ?? 'heroicon-m-arrow-right'"
                                                wire:click="{{ $mountArgs }}"
                                            >
                                                {{ $transition['label'] }}
                                            </x-filament::dropdown.list.item>
                                        @endforeach
                                    </x-filament::dropdown.list>
                                </x-filament::dropdown>
                            @endif

                            @php($moreActions = $this->moreActions($fulfilment))
                            @if (filled($moreActions))
                                <x-filament::dropdown placement="bottom-end">
                                    <x-slot name="trigger">
                                        <x-filament::icon-button
                                            icon="heroicon-m-ellipsis-vertical"
                                            color="gray"
                                            size="sm"
                                            :label="__('lunarpanel::order.fulfilments.actions.more')"
                                        />
                                    </x-slot>

                                    <x-filament::dropdown.list>
                                        @foreach ($moreActions as $action)
                                            <x-filament::dropdown.list.item
                                                :icon="$action['icon'] ?? 'heroicon-m-arrow-right'"
                                                :color="$action['color'] ?? null"
                                                wire:click="{{ $action['wire'] }}"
                                            >
                                                {{ $action['label'] }}
                                            </x-filament::dropdown.list.item>
                                        @endforeach
                                    </x-filament::dropdown.list>
                                </x-filament::dropdown>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Lines --}}
                <ul role="list">
                    @foreach ($fulfilment->lines as $line)
                        @php($orderLine = $line->orderLine)
                        @php($splitting = $this->splittingId === $fulfilment->id)
                        @php($merging = $this->mergingId === $fulfilment->id)
                        @php($editing = $splitting || $merging)
                        @php($qtyModel = $splitting ? 'splitQuantities' : 'mergeQuantities')
                        <li
                            x-data="{ open: false }"
                            @class(['border-t border-gray-200 dark:border-white/10' => ! $loop->first])
                        >
                            <div
                                @unless ($editing) role="button" @click="open = ! open" @endunless
                                style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; @unless ($editing) cursor:pointer; @endunless"
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

                                @if ($editing)
                                    <div style="flex:none; display:flex; align-items:center; gap:0.5rem;">
                                        <input
                                            type="number"
                                            min="0"
                                            max="{{ $line->quantity }}"
                                            wire:model="{{ $qtyModel }}.{{ $line->order_line_id }}"
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

                                @unless ($editing)
                                    <x-filament::icon
                                        icon="heroicon-m-chevron-down"
                                        class="h-5 w-5 text-gray-400"
                                        style="flex:none; transition:transform .2s ease;"
                                        x-bind:style="open && 'transform: rotate(180deg)'"
                                    />
                                @endunless
                            </div>

                            @php($details = $editing ? [] : $this->lineDetails($line))
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

                {{-- Merge destination picker (only when there's a choice) --}}
                @if ($this->mergingId === $fulfilment->id && $this->mergeTargets($fulfilment)->count() > 1)
                    <div style="display:flex; align-items:center; gap:0.5rem; padding:0.75rem 1rem; border-top:1px solid rgba(0,0,0,0.06);">
                        <span class="text-sm text-gray-700 dark:text-gray-300" style="white-space:nowrap;">
                            {{ __('lunarpanel::order.fulfilments.actions.merge.target') }}
                        </span>
                        <select
                            wire:model="mergeTargetId"
                            style="padding:0.3rem 0.5rem; border-radius:0.5rem; border:1px solid rgba(0,0,0,0.15); background:transparent;"
                            class="text-sm text-gray-950 dark:text-white"
                        >
                            @foreach ($this->mergeTargets($fulfilment) as $target)
                                <option value="{{ $target->id }}">{{ $target->reference }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Tracking references --}}
                @if ($fulfilment->trackings->isNotEmpty())
                    <div class="border-t border-gray-200 dark:border-white/10">
                        @foreach ($fulfilment->trackings as $tracking)
                            @php($carrier = $tracking->carrier())
                            @php($trackingUrl = $tracking->url)
                            <div class="flex items-center px-4 py-2 text-xs text-gray-500 dark:text-gray-400" style="gap:0.375rem;">
                                <x-filament::icon icon="heroicon-m-truck" class="h-4 w-4 shrink-0" />
                                @if ($carrier)
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $carrier->getName() }}</span>
                                @endif
                                @if ($trackingUrl)
                                    <a href="{{ $trackingUrl }}" target="_blank" class="text-primary-600 hover:underline dark:text-primary-400">
                                        {{ $tracking->tracking_number ?: $trackingUrl }}
                                    </a>
                                @elseif ($tracking->tracking_number)
                                    <span>{{ $tracking->tracking_number }}</span>
                                @endif
                                @if ($tracking->shippingMethodLabel())
                                    <span>{{ $tracking->shippingMethodLabel() }}</span>
                                @endif
                                <div class="-my-1 ml-auto shrink-0">
                                    <x-filament::icon-button
                                        icon="heroicon-m-trash"
                                        color="gray"
                                        size="xs"
                                        :label="__('lunarpanel::order.fulfilments.actions.remove_tracking.label')"
                                        wire:click="mountAction('removeTracking', { tracking: {{ $tracking->id }} })"
                                    />
                                </div>
                            </div>
                        @endforeach
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
