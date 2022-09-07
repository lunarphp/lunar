<div class="space-y-4">
    <h1 class="text-lg font-bold text-gray-900 md:text-2xl dark:text-white">
        {{ $customer->fullName }}
    </h1>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:order-last">
            <div
                 class="overflow-hidden bg-white border border-white rounded shadow dark:bg-gray-800 dark:border-gray-700">
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-hub::input.group for="title"
                                                :label="__('adminhub::inputs.title')">
                                <x-hub::input.text wire:model.defer="customer.title" />
                            </x-hub::input.group>
                        </div>

                        <div>
                            <x-hub::input.group for="first_name"
                                                :label="__('adminhub::inputs.firstname')">
                                <x-hub::input.text wire:model.defer="customer.first_name" />
                            </x-hub::input.group>
                        </div>

                        <div>
                            <x-hub::input.group for="last_name"
                                                :label="__('adminhub::inputs.lastname')">
                                <x-hub::input.text wire:model.defer="customer.last_name" />
                            </x-hub::input.group>
                        </div>
                    </div>

                    <x-hub::input.group for="company_name"
                                        :label="__('adminhub::inputs.company_name.label')">
                        <x-hub::input.text wire:model.defer="customer.company_name" />
                    </x-hub::input.group>

                    <x-hub::input.group for="account_ref"
                                        :label="__('adminhub::inputs.account_ref.label')">
                        <x-hub::input.text wire:model.defer="customer.account_ref" />
                    </x-hub::input.group>

                    <x-hub::input.group for="vat_no"
                                        :label="__('adminhub::inputs.vat_no.label')">
                        <x-hub::input.text wire:model.defer="customer.vat_no" />
                    </x-hub::input.group>

                    <strong class="block text-gray-800 dark:text-gray-100">
                        {{ __('adminhub::components.customers.show.customer_groups') }}
                    </strong>

                    <div class="space-y-2">
                        <div class="space-y-2 overflow-y-auto max-h-48">
                            @foreach ($this->customerGroups as $group)
                                <label class="flex items-center gap-2 p-2 border border-gray-100 rounded cursor-pointer dark:border-gray-700"
                                       wire:key="group_{{ $group->id }}">
                                    <x-hub::input.checkbox wire:model.debounce.500ms="syncedGroups"
                                                           value="{{ $group->id }}" />

                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $group->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="attributes">
                        @include('adminhub::partials.attributes', ['inline' => true])
                    </div>
                </div>

                <div class="p-4 text-right bg-black/5 dark:bg-white/5">
                    <x-hub::button type="button"
                                   wire:click="save">
                        <div wire:loading
                             wire:target="save">
                            <div>
                                <svg class="w-5 h-5 text-white animate-spin"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24">
                                    <circle class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75"
                                          fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <div wire:loading.remove
                             wire:target="save">
                            <span>{{ __('adminhub::components.customers.show.save_customer') }}</span>
                        </div>
                    </x-hub::button>
                </div>
            </div>
        </div>

        <div class="space-y-4 lg:col-span-2">
            <div>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div
                         class="px-4 py-5 overflow-hidden bg-white border border-white rounded-lg shadow dark:bg-gray-800 sm:p-6 dark:border-gray-700">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('adminhub::components.customers.show.metrics.total_orders') }}
                        </dt>

                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ $this->ordersCount }}
                        </dd>
                    </div>

                    <div
                         class="px-4 py-5 overflow-hidden bg-white border border-white rounded-lg shadow dark:bg-gray-800 sm:p-6 dark:border-gray-700">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('adminhub::components.customers.show.metrics.avg_spend') }}
                        </dt>

                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ $this->avgSpend->formatted }}
                        </dd>
                    </div>

                    <div
                         class="px-4 py-5 overflow-hidden bg-white border border-white rounded-lg shadow dark:bg-gray-800 sm:p-6 dark:border-gray-700">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('adminhub::components.customers.show.metrics.total_spend') }}
                        </dt>

                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ $this->totalSpend->formatted }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border border-white rounded shadow dark:bg-gray-800 dark:border-gray-700">
                <header
                        class="p-4 font-bold text-gray-900 border-b border-gray-100 dark:text-white dark:border-gray-700">
                    {{ __('adminhub::components.customers.show.year_spending') }}
                </header>

                <div class="p-4 h-80">
                    @livewire('hub.components.reporting.apex-chart', ['options' => $this->spendingChart])
                </div>
            </div>
        </div>
    </div>

    <div>
        <div x-data="{ activeTab: 'purchases' }"
             x-init="activeTab = window.location.hash ? window.location.hash.replace('#', '') : 'purchases'">
            <div>
                <div class="hidden sm:block">
                    <nav class="flex space-x-4"
                         aria-label="Tabs">
                        <a href="#purchases"
                           x-on:click="activeTab = 'purchases'"
                           class="px-3 py-2 text-sm font-medium border border-white rounded-md dark:border-gray-700"
                           :class="{
                               'bg-white shadow dark:bg-gray-800 text-gray-700 dark:text-white': activeTab ==
                                   'purchases',
                               'hover:text-gray-600 text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 !border-transparent': activeTab !=
                                   'purchases'
                           }">
                            {{ __('adminhub::components.customers.show.purchase_history') }}
                        </a>

                        <a href="#orders"
                           x-on:click="activeTab = 'orders'"
                           class="px-3 py-2 text-sm font-medium border border-white rounded-md dark:border-gray-700"
                           :class="{
                               'bg-white shadow dark:bg-gray-800 text-gray-700 dark:text-white': activeTab ==
                                   'orders',
                               'hover:text-gray-600 text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 !border-transparent': activeTab !=
                                   'orders'
                           }">
                            {{ __('adminhub::components.customers.show.order_history') }}
                        </a>

                        <a href="#users"
                           x-on:click="activeTab = 'users'"
                           class="px-3 py-2 text-sm font-medium border border-white rounded-md dark:border-gray-700"
                           :class="{
                               'bg-white shadow dark:bg-gray-800 text-gray-700 dark:text-white': activeTab ==
                                   'users',
                               'hover:text-gray-600 text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 !border-transparent': activeTab !=
                                   'users'
                           }">
                            {{ __('adminhub::components.customers.show.users') }}
                        </a>

                        <a href="#addresses"
                           x-on:click="activeTab = 'addresses'"
                           class="px-3 py-2 text-sm font-medium border border-white rounded-md dark:border-gray-700"
                           :class="{
                               'bg-white shadow dark:bg-gray-800 text-gray-700 dark:text-white': activeTab ==
                                   'addresses',
                               'hover:text-gray-600 text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 !border-transparent': activeTab !=
                                   'addresses'
                           }">
                            {{ __('adminhub::components.customers.show.addresses') }}
                        </a>
                    </nav>
                </div>
            </div>

            <div x-show="activeTab == 'purchases'"
                 class="mt-4">
                @if (!$this->purchaseHistory->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500 dark:text-gray-400">
                        {{ __('adminhub::components.customers.show.no_purchase_history') }}
                    </div>
                @else
                    @include('adminhub::partials.customers.purchase-history')
                @endif
            </div>

            <div x-show="activeTab == 'orders'"
                 class="mt-4">
                @if (!$this->orders->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500 dark:text-gray-400">
                        {{ __('adminhub::components.customers.show.no_order_history') }}
                    </div>
                @else
                    @include('adminhub::partials.customers.order-history')
                @endif
            </div>

            <div x-show="activeTab == 'users'"
                 class="mt-4">
                @if (!$this->users->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500 dark:text-gray-400">
                        {{ __('adminhub::components.customers.show.no_users') }}
                    </div>
                @else
                    @include('adminhub::partials.customers.users')
                @endif
            </div>

            <div x-show="activeTab == 'addresses'"
                 class="mt-4">
                @if (!$this->addresses->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500 dark:text-gray-400">
                        {{ __('adminhub::components.customers.show.no_addresses') }}
                    </div>
                @else
                    @include('adminhub::partials.customers.addresses')
                @endif
            </div>
        </div>
    </div>

    <x-hub::slideover wire:model="addressIdToEdit"
                      form="saveAddress">
        @include('adminhub::partials.forms.address', [
            'bind' => 'address',
            'states' => $this->states,
        ])

        <div class="flex justify-between mt-4">
            <x-hub::input.group label="Billing Default"
                                for="billing_default">
                <x-hub::input.toggle wire:model.defer="address.billing_default" />
            </x-hub::input.group>

            <x-hub::input.group label="Shipping Default"
                                for="shipping_default">
                <x-hub::input.toggle wire:model.defer="address.shipping_default" />
            </x-hub::input.group>
        </div>

        <x-slot name="footer">
            <x-hub::button wire:click.prevent="$set('addressIdToEdit', null)"
                           theme="gray">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>

            <x-hub::button type="submit">
                {{ __('adminhub::components.orders.show.save_shipping_btn') }}
            </x-hub::button>
        </x-slot>
    </x-hub::slideover>

    <x-hub::modal.dialog form="removeAddress"
                         wire:model="addressToRemove">
        <x-slot name="title">
            {{ __('adminhub::components.customers.show.remove_address.title') }}
        </x-slot>

        <x-slot name="content">
            <x-hub::alert level="warning">
                {{ __('adminhub::components.customers.show.remove_address.confirm') }}
            </x-hub::alert>
        </x-slot>

        <x-slot name="footer">
            <x-hub::button type="button"
                           wire:click.prevent="$set('addressToRemove', null)"
                           theme="gray">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>

            <x-hub::button type="submit">
                {{ __('adminhub::components.customers.show.remove_address_btn') }}
            </x-hub::button>
        </x-slot>
    </x-hub::modal.dialog>
</div>
