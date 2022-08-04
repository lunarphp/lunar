<div class="space-y-8">
    <div class="flex justify-between" x-data="{ showDatePicker: false }">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('adminhub::global.dashboard') }}</h1>
        <div class="relative">
            <button @click="showDatePicker = !showDatePicker"
                    class="relative flex items-center gap-2 rounded border border-gray-300 bg-white p-2">
                {!! GetCandy\Hub\GetCandyHub::icon('calendar', 'w-5 h-5') !!}
            </button>
            <div x-cloak x-show="showDatePicker"
                 class="absolute top-full right-0 mt-4 flex flex-col items-center justify-center gap-2 rounded border border-gray-300 bg-white p-4 sm:flex-row sm:gap-4">
                <x-hub::input.datepicker wire:model="range.from" />
                <span class="text-xs font-medium uppercase text-gray-500">{{ __('adminhub::global.to') }}</span>
                <x-hub::input.datepicker wire:model="range.to" wire:change="$emit('apexChartRefresh')" />
                <button @click="showDatePicker = false"
                        class="absolute bottom-full right-0 translate-y-1/2 translate-x-1/2 text-gray-400 hover:text-gray-800">
                    {!! GetCandy\Hub\GetCandyHub::icon('x-circle', 'w-6', 'solid') !!}
                </button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 sm:gap-8 lg:grid-cols-4">
        <div class="flex h-24 items-center rounded-lg bg-white p-4">
            <div class="ml-2 flex h-12 w-12 rounded-full bg-blue-200 p-3">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M15.8498 2.50071C16.4808 2.50071 17.1108 2.58971 17.7098 2.79071C21.4008 3.99071 22.7308 8.04071 21.6198 11.5807C20.9898 13.3897 19.9598 15.0407 18.6108 16.3897C16.6798 18.2597 14.5608 19.9197 12.2798 21.3497L12.0298 21.5007L11.7698 21.3397C9.4808 19.9197 7.3498 18.2597 5.4008 16.3797C4.0608 15.0307 3.0298 13.3897 2.3898 11.5807C1.2598 8.04071 2.5898 3.99071 6.3208 2.76971C6.6108 2.66971 6.9098 2.59971 7.2098 2.56071H7.3298C7.6108 2.51971 7.8898 2.50071 8.1698 2.50071H8.2798C8.9098 2.51971 9.5198 2.62971 10.1108 2.83071H10.1698C10.2098 2.84971 10.2398 2.87071 10.2598 2.88971C10.4808 2.96071 10.6898 3.04071 10.8898 3.15071L11.2698 3.32071C11.3616 3.36968 11.4647 3.44451 11.5538 3.50918C11.6102 3.55015 11.661 3.58705 11.6998 3.61071C11.7161 3.62034 11.7327 3.63002 11.7494 3.63978C11.8352 3.68983 11.9245 3.74197 11.9998 3.79971C13.1108 2.95071 14.4598 2.49071 15.8498 2.50071ZM18.5098 9.70071C18.9198 9.68971 19.2698 9.36071 19.2998 8.93971V8.82071C19.3298 7.41971 18.4808 6.15071 17.1898 5.66071C16.7798 5.51971 16.3298 5.74071 16.1798 6.16071C16.0398 6.58071 16.2598 7.04071 16.6798 7.18971C17.3208 7.42971 17.7498 8.06071 17.7498 8.75971V8.79071C17.7308 9.01971 17.7998 9.24071 17.9398 9.41071C18.0798 9.58071 18.2898 9.67971 18.5098 9.70071Z"
                          fill="#5B93FF" />
                </svg>
            </div>
            <div class="ml-4 flex items-center">
                <div>
                    <strong class="text-lg font-bold">{{ $this->newProductsCount }}</strong>
                    <span class="block text-xs">{{ __('adminhub::global.new_products') }}</span>
                </div>
            </div>
        </div>
        <div class="flex h-24 items-center rounded-lg bg-white p-4 pr-0">
            <div class="ml-2 flex h-12 w-12 rounded-full bg-amber-100 p-3">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 2C6.579 2 2 6.579 2 12C2 17.421 6.579 22 12 22C17.421 22 22 17.421 22 12C22 6.579 17.421 2 12 2ZM12 7C13.727 7 15 8.272 15 10C15 11.728 13.727 13 12 13C10.274 13 9 11.728 9 10C9 8.272 10.274 7 12 7ZM6.894 16.772C7.791 15.452 9.287 14.572 11 14.572H13C14.714 14.572 16.209 15.452 17.106 16.772C15.828 18.14 14.015 19 12 19C9.985 19 8.172 18.14 6.894 16.772Z"
                        fill="#FFC227" />
                </svg>
            </div>
            <div class="ml-4 flex items-center">
                <div>
                    <strong class="text-lg font-bold">{{ $this->returningCustomersPercent }}%</strong>
                    <span
                        class="block text-xs">{{ __('adminhub::catalogue.customer.dashboard.returning_customers') }}</span>
                </div>
            </div>
        </div>
        <div class="flex h-24 items-center rounded-lg bg-white p-4 pr-0">
            <div class="ml-2 flex h-12 w-12 rounded-full bg-red-100 p-3">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M2.04699 14.668C2.08059 14.7949 2.13898 14.9138 2.21879 15.018C2.2986 15.1222 2.39825 15.2095 2.51199 15.275L4.42199 16.379V18.578C4.42199 18.8432 4.52735 19.0976 4.71489 19.2851C4.90242 19.4726 5.15678 19.578 5.42199 19.578H7.62099L8.72499 21.488C8.81353 21.6393 8.93993 21.765 9.09177 21.8527C9.24361 21.9404 9.41566 21.987 9.59099 21.988C9.76499 21.988 9.93799 21.942 10.092 21.853L12 20.75L13.91 21.854C14.1396 21.9864 14.4124 22.0224 14.6685 21.9539C14.9245 21.8855 15.143 21.7183 15.276 21.489L16.379 19.579H18.578C18.8432 19.579 19.0976 19.4736 19.2851 19.2861C19.4726 19.0986 19.578 18.8442 19.578 18.579V16.38L21.488 15.276C21.6018 15.2103 21.7015 15.1227 21.7814 15.0184C21.8613 14.9141 21.9199 14.7951 21.9538 14.6681C21.9877 14.5412 21.9962 14.4088 21.9789 14.2785C21.9616 14.1483 21.9188 14.0227 21.853 13.909L20.75 12L21.854 10.092C21.9867 9.86245 22.0228 9.58959 21.9543 9.33343C21.8859 9.07727 21.7185 8.85878 21.489 8.726L19.579 7.622V5.422C19.579 5.15679 19.4736 4.90243 19.2861 4.7149C19.0986 4.52736 18.8442 4.422 18.579 4.422H16.38L15.277 2.513C15.1438 2.28392 14.9257 2.11651 14.67 2.047C14.5432 2.01262 14.4108 2.00375 14.2805 2.02092C14.1502 2.03808 14.0246 2.08094 13.911 2.147L12 3.25L10.091 2.146C9.86144 2.01332 9.58858 1.97723 9.33242 2.04568C9.07626 2.11412 8.85777 2.2815 8.72499 2.511L7.62099 4.421H5.42199C5.15678 4.421 4.90242 4.52636 4.71489 4.7139C4.52735 4.90143 4.42199 5.15579 4.42199 5.421V7.62L2.51199 8.724C2.28256 8.85735 2.11532 9.0762 2.0469 9.3326C1.97849 9.58901 2.01448 9.86208 2.14699 10.092L3.25099 12L2.14699 13.908C2.01498 14.1383 1.97905 14.4114 2.04699 14.668ZM12 13C8.51999 13 7.99999 11.121 7.99999 10C7.99999 8.713 9.02899 7.417 11 7.085V6.012H13V7.121C14.734 7.531 15.4 8.974 15.4 10H14.4L13.4 10.018C13.386 9.638 13.185 9 12 9C10.701 9 9.99999 9.515 9.99999 10C9.99999 10.374 9.99999 11 12 11C15.48 11 16 12.879 16 14C16 15.287 14.971 16.583 13 16.915V18H11V16.92C8.66099 16.553 7.99999 14.917 7.99999 14H9.99999C10.011 14.143 10.159 15 12 15C13.38 15 14 14.415 14 14C14 13.675 14 13 12 13Z"
                        fill="#FF8F6B" />
                </svg>
            </div>
            <div class="ml-4 flex items-center">
                <div>
                    <strong class="text-lg font-bold">{{ $this->orderTotal->formatted }}</strong>
                    <span class="block text-xs">{{ __('adminhub::catalogue.customer.dashboard.turnover') }}</span>
                </div>
            </div>
        </div>
        <div class="flex h-24 items-center rounded-lg bg-white p-4 pr-0">
            <div class="ml-2 flex h-12 w-12 rounded-full bg-indigo-50 p-3">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M19 2H5C3.346 2 2 3.346 2 5V7.831C2 8.884 2.382 9.841 3 10.577V20C3 20.2652 3.10536 20.5196 3.29289 20.7071C3.48043 20.8946 3.73478 21 4 21H12C12.2652 21 12.5196 20.8946 12.7071 20.7071C12.8946 20.5196 13 20.2652 13 20V15H17V20C17 20.2652 17.1054 20.5196 17.2929 20.7071C17.4804 20.8946 17.7348 21 18 21H20C20.2652 21 20.5196 20.8946 20.7071 20.7071C20.8946 20.5196 21 20.2652 21 20V10.576C21.618 9.841 22 8.884 22 7.83V5C22 3.346 20.654 2 19 2ZM20 5V7.831C20 8.971 19.151 9.943 18.109 9.998L18 10C16.897 10 16 9.103 16 8V4H19C19.552 4 20 4.449 20 5ZM10 8V4H14V8C14 9.103 13.103 10 12 10C10.897 10 10 9.103 10 8ZM4 5C4 4.449 4.448 4 5 4H8V8C8 9.103 7.103 10 6 10L5.891 9.997C4.849 9.943 4 8.971 4 7.831V5ZM10 16H6V13H10V16Z"
                        fill="#605BFF" />
                </svg>
            </div>
            <div class="ml-4 flex items-center">
                <div>
                    <strong class="text-lg font-bold">{{ $this->orderCount }}</strong>
                    <span class="block text-xs">{{ __('adminhub::catalogue.customer.dashboard.no_of_orders') }}</span>
                </div>
            </div>
        </div>
    </div>


    <div class="grid grid-cols-2 gap-4 sm:gap-8 lg:grid-cols-3">
        <div class="col-span-2 h-96 rounded-lg bg-white p-4 lg:col-span-3">
            <h3 class="mt-4 ml-4 text-lg font-semibold text-gray-900">
                {{ __('adminhub::catalogue.customer.dashboard.sales_performance') }}</h3>
            <div class="h-80">
                <livewire:hub.components.reporting.apex-chart wire:key="{{ now() }}"
                                                              :options="$this->salesPerformance" />
            </div>
        </div>

        <div class="h-96 rounded-lg bg-white p-4">
            <h3 class="mt-4 ml-4 text-lg font-semibold text-gray-900">
                {{ __('adminhub::catalogue.customer.dashboard.customer_group_orders') }}</h3>
            <div class="h-80">
                <livewire:hub.components.reporting.apex-chart wire:key="{{ now() }}"
                                                              :options="$this->customerGroupOrders" />
            </div>
        </div>

        <div class="h-96 rounded-lg bg-white p-8 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ __('adminhub::catalogue.customer.dashboard.top_selling_products') }}</h3>

            @foreach ($this->topSellingProducts as $product)
                <div class="relative flex items-center space-x-3 border-b border-slate-100 bg-white py-8">
                    <div class="flex-shrink-0">
                        @if ($thumbnail = $product->purchasable->getThumbnail())
                            <img src="{{ $thumbnail }}" class="h-16 w-16 rounded-lg" />
                        @else
                            <x-hub::icon ref="photograph" class="h-16 w-16 rounded-lg text-gray-200" />
                        @endif

                    </div>
                    <div class="min-w-0 flex-1">
                        <a href="#" class="focus:outline-none">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="truncate text-sm font-medium text-gray-900">
                                {{ $product->purchasable->getDescription() }}
                                <span class="block text-sm">{{ $product->purchasable->getIdentifier() }}</span>
                            </p>
                            <p class="truncate text-sm text-gray-500">
                                {{ $product->count }} orders
                            </p>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-span-2 h-96 rounded-lg bg-white p-8 lg:col-span-3">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ __('adminhub::catalogue.orders.dashboard.recent_orders') }}</h3>
            <div class="overflow-x-scroll">
                <table class="font-sm mt-8 w-full min-w-[40rem] table-auto">
                    <thead>
                    <tr class="border-b">
                        <th class="pb-2 text-left text-sm font-normal">{{ __('adminhub::global.order_ref') }}
                        </th>
                        <th class="pb-2 text-center text-sm font-normal">
                            {{ __('adminhub::global.customer') }}</th>
                        <th class="pb-2 text-center text-sm font-normal">
                            {{ __('adminhub::global.no_items') }}</th>
                        <th class="pb-2 text-center text-sm font-normal">
                            {{ __('adminhub::global.placed_at') }}</th>
                        <th class="pb-2 text-right text-sm font-normal">{{ __('adminhub::global.total') }}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($this->recentOrders as $order)
                        <tr>
                            <td class="pt-4 text-sm">
                                <a href="{{ route('hub.orders.show', $order->id) }}"
                                   class="underline-offset-2 hover:underline hover:decoration-blue-500 hover:decoration-dashed">
                                    {{ $order->reference }}
                                </a>
                            </td>
                            <td class="pt-4 text-center text-sm">{{ $order->billingAddress->full_name }}</td>
                            <td class="pt-4 text-center text-sm">{{ $order->lines_count }}</td>
                            <td class="pt-4 text-center text-sm">
                                {{ optional($order->placed_at)->diffForHumans() }}</td>
                            <td class="pt-4 text-right text-sm">{{ $order->total->formatted }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
