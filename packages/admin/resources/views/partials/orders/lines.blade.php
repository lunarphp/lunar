@foreach ($this->visibleLines as $line)
    <li class="py-3"
        x-data="{ showDetails: false }">
        <div class="flex items-start">
            <div class="flex gap-2">
                @if ($this->transactions->count())
                    <x-hub::input.checkbox value="{{ $line->id }}"
                                           wire:model="selectedLines" />
                @endif

                <div class="flex-shrink-0">
                    @if ($thumbnail = $line->purchasable?->getThumbnail())
                        <x-hub::thumbnail :src="$thumbnail->getUrl('small')" />
                    @else
                        <x-hub::icon ref="photograph"
                                     class="w-16 h-16 text-gray-300" />
                    @endif
                </div>
            </div>

            <div class="flex-1">
                <div class="gap-8 xl:justify-between xl:items-start xl:flex">
                    <div class="relative flex items-center justify-between gap-4 pl-8 xl:justify-end xl:pl-0 xl:order-last"
                         x-data="{ showMenu: false }">
                        <p class="text-sm font-medium text-gray-700">
                            {{ $line->quantity }} @ {{ $line->unit_price->formatted }}

                            <span class="ml-1">
                                {{ $line->sub_total->formatted }}
                            </span>
                        </p>

                        {{-- <button
              class="text-gray-400 hover:text-gray-500"
              x-on:click="showMenu = !showMenu"
              type="button"
            >
              <x-hub::icon
                ref="dots-vertical"
                style="solid"
              />
            </button> --}}

                        {{-- <div
              class="absolute right-0 z-50 mt-2 text-sm bg-white border rounded-lg shadow-lg top-full"
              role="menu"
              x-on:click.away="showMenu = false"
              x-show="showMenu"
              x-transition
              x-cloak
            >
              <div
                class="py-1"
                role="none"
              >
                <button
                  class="w-full px-4 py-2 text-left transition hover:bg-white"
                  role="menuitem"
                  type="button"
                >
                  Refund Line
                </button>

                <button
                  class="w-full px-4 py-2 text-left transition hover:bg-white"
                  role="menuitem"
                  type="button"
                >
                  Refund Stock
                </button>
              </div>
            </div> --}}
                    </div>

                    <button class="flex mt-2 group xl:mt-0"
                            x-on:click="showDetails = !showDetails"
                            type="button">
                        <div class="transition-transform "
                             :class="{
                                 '-rotate-90 ': !showDetails
                             }">
                            <x-hub::icon ref="chevron-down"
                                         style="solid"
                                         class="w-6 mx-1 text-gray-400 -mt-7 group-hover:text-gray-500 xl:mt-0" />
                        </div>
                        <div class="max-w-sm space-y-2 text-left">
                            <x-hub::tooltip :text="$line->description"
                                            :left="true">
                                <p class="text-sm font-bold leading-tight text-gray-800 truncate">
                                    {{ $line->description }}
                                </p>
                            </x-hub::tooltip>

                            <div class="flex text-xs font-medium text-gray-600">
                                <p>{{ $line->identifier }}</p>

                                @if ($line->purchasable?->getOptions()?->count())
                                    <dl class="flex before:content-['|'] before:mx-3 before:text-gray-200 space-x-3">
                                        @foreach ($line->purchasable->getOptions() as $option)
                                            <div class="flex gap-0.5">
                                                <dt>{{ $option }}</dt>
                                            </div>
                                        @endforeach
                                    </dl>
                                @endif
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <div class="pl-24 text-gray-700"
             x-show="showDetails">
            @if (!is_null($line->purchasable?->stock))
                <span class="block p-2 mt-2 text-xs border rounded">
                    <span @class([
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
                <article class="text-sm">
                    <p>
                        <strong>{{ __('adminhub::global.notes') }}:</strong>

                        {{ $line->notes }}
                    </p>
                </article>

                <div class="overflow-hidden overflow-x-auto border border-gray-200 rounded">
                    <table class="min-w-full text-xs divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                            <tr class="divide-x divide-gray-200">
                                <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.unit_price') }}
                                </td>

                                <td class="p-2 text-gray-700 whitespace-nowrap">
                                    {{ $line->unit_price->formatted }} / {{ $line->unit_quantity }}
                                </td>
                            </tr>

                            <tr class="divide-x divide-gray-200">
                                <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.quantity') }}
                                </td>

                                <td class="p-2 text-gray-700 whitespace-nowrap">
                                    {{ $line->quantity }}
                                </td>
                            </tr>

                            <tr class="divide-x divide-gray-200">
                                <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.sub_total') }}
                                </td>

                                <td class="p-2 text-gray-700 whitespace-nowrap">
                                    {{ $line->sub_total->formatted }}
                                </td>
                            </tr>

                            <tr class="divide-x divide-gray-200">
                                <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.discount_total') }}
                                </td>

                                <td class="p-2 text-gray-700 whitespace-nowrap">
                                    {{ $line->discount_total->formatted }}
                                </td>
                            </tr>

                            @foreach ($line->tax_breakdown as $tax)
                                <tr class="divide-x divide-gray-200">
                                    <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $tax->description }}
                                    </td>

                                    <td class="p-2 text-gray-700 whitespace-nowrap">
                                        {{ $tax->total->formatted }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="divide-x divide-gray-200">
                                <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                                    {{ __('adminhub::partials.orders.lines.total') }}
                                </td>

                                <td class="p-2 text-gray-700 whitespace-nowrap">
                                    {{ $line->total->formatted }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </li>
@endforeach
