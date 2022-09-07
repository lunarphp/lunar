@foreach ($this->visibleLines as $line)
    <li class="py-3"
        x-data="{ showDetails: false }">
        <div class="flex items-start gap-4">
            <div class="flex gap-2">
                @if ($this->transactions->count())
                    <x-hub::input.checkbox value="{{ $line->id }}"
                                           wire:model="selectedLines" />
                @endif
                <div class="flex-shrink-0 p-1 overflow-hidden border border-gray-100 rounded dark:border-gray-700">
                    <img class="object-contain w-8 h-8"
                         src="{{ $line->purchasable->getThumbnail() }}" />
                </div>
            </div>

            <div class="flex-1">
                <div class="gap-8 xl:justify-between xl:items-start xl:flex">
                    <div class="relative flex items-center justify-between gap-4 pl-8 xl:justify-end xl:pl-0 xl:order-last"
                         x-data="{ showMenu: false }">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $line->quantity }} @ {{ $line->unit_price->formatted }}

                            <span class="ml-1">
                                {{ $line->sub_total->formatted }}
                            </span>
                        </p>

                        {{-- <button class="text-gray-400 hover:text-gray-500"
                                x-on:click="showMenu = !showMenu"
                                type="button">
                            <x-hub::icon ref="dots-vertical"
                                         style="solid" />
                        </button>

                        <div class="absolute right-0 z-50 mt-2 text-sm bg-white border rounded-lg shadow-lg top-full"
                             role="menu"
                             x-on:click.away="showMenu = false"
                             x-show="showMenu"
                             x-transition
                             x-cloak>
                            <div class="py-1"
                                 role="none">
                                <button class="w-full px-4 py-2 text-left transition hover:bg-white"
                                        role="menuitem"
                                        type="button">
                                    Refund Line
                                </button>

                                <button class="w-full px-4 py-2 text-left transition hover:bg-white"
                                        role="menuitem"
                                        type="button">
                                    Refund Stock
                                </button>
                            </div>
                        </div> --}}
                    </div>

                    <button class="flex gap-4"
                            x-on:click="showDetails = !showDetails"
                            type="button">
                        <div class="inline-flex items-center justify-center w-6 h-6 transition rounded bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10"
                             :class="{ '-rotate-90 ': !showDetails }">
                            <x-hub::icon ref="chevron-down"
                                         style="solid"
                                         class="w-4 h-4 text-gray-400 group-hover:text-gray-500 dark:text-gray-300 dark:group-hover:text-gray-200" />
                        </div>

                        <div class="max-w-sm space-y-2 text-left">
                            <strong class="block text-sm text-gray-900 dark:text-white">
                                {{ $line->description }}
                            </strong>

                            <div class="flex items-center gap-3 text-xs font-medium text-gray-600 dark:text-gray-100">
                                <p>{{ $line->identifier }}</p>

                                <span class="opacity-50">|</span>

                                @if ($line->purchasable->getOptions()->count())
                                    <ul class="flex gap-3">
                                        @foreach ($line->purchasable->getOptions() as $option)
                                            <li>
                                                {{ $option }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <div class="pl-24 text-gray-700 dark:text-gray-200"
             x-show="showDetails">
            @if (!is_null($line->purchasable?->stock))
                <span class="block p-2 mt-2 text-xs border border-gray-200 rounded dark:border-gray-700">
                    <span @class([
                        'font-medium',
                        'text-red-500' => $line->purchasable->stock < 50,
                        'text-green-500' => $line->purchasable->stock > 50,
                    ])>
                        {{ __('adminhub::partials.orders.lines.current_stock_level', [
                            'count' => $line->purchasable->stock,
                        ]) }}
                    </span>

                    @if (!is_null($line->meta?->stock_level ?? null))
                        ({{ __('adminhub::partials.orders.lines.purchase_stock_level', [
                            'count' => $line->meta->stock_level,
                        ]) }})
                    @endif
                </span>
            @endif

            <div class="mt-4 space-y-4">
                @if ($line->notes)
                    <article class="text-sm">
                        <p>
                            <strong>
                                {{ __('adminhub::global.notes') }}:
                            </strong>

                            {{ $line->notes }}
                        </p>
                    </article>
                @endif

                <div class="overflow-hidden overflow-x-auto border border-gray-200 rounded dark:border-gray-700">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.unit_price') }}
                                </td>

                                <td class="p-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ $line->unit_price->formatted }} / {{ $line->unit_quantity }}
                                </td>
                            </tr>

                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.quantity') }}
                                </td>

                                <td class="p-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ $line->quantity }}
                                </td>
                            </tr>

                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.sub_total') }}
                                </td>

                                <td class="p-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ $line->sub_total->formatted }}
                                </td>
                            </tr>

                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.discount_total') }}
                                </td>

                                <td class="p-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ $line->discount_total->formatted }}
                                </td>
                            </tr>

                            @foreach ($line->tax_breakdown as $tax)
                                <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                    <td class="p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ $tax->description }}
                                    </td>

                                    <td class="p-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                        {{ $tax->total->formatted }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.total') }}
                                </td>

                                <td class="p-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ $line->total->formatted }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </li>
@endforeach
