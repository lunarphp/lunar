<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-lg font-bold md:text-2xl">
            {{ $customer->fullName }}
        </strong>
    </div>

    <div class="space-y-4 xl:space-y-0 xl:flex xl:flex-row-reverse gap-x-4">
        <div class="xl:w-1/3">
            <div class="bg-white rounded shadow">
                <div class="p-4 space-y-4">
                
                    @foreach ($this->getSlotsByPosition('top') as $slot)
                        <div id="{{ $slot->handle }}">
                            <div>
                                @livewire($slot->component, ['slotModel' => $customer], key('top-slot-' . $slot->handle))
                            </div>
                        </div>
                    @endforeach                
                
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

                    <header>
                        {{ __('adminhub::components.customers.show.customer_groups') }}
                    </header>

                    <div class="space-y-2">
                        <div class="space-y-2 overflow-y-auto max-h-48">
                            @foreach ($this->customerGroups as $group)
                                <label class="flex items-center p-2 text-sm border rounded cursor-pointer"
                                       wire:key="group_{{ $group->id }}">
                                    <x-hub::input.checkbox wire:model.debounce.500ms="syncedGroups"
                                                           value="{{ $group->id }}" /> <span
                                          class="ml-2">{{ $group->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="attributes">
                        @include('adminhub::partials.attributes', ['inline' => true])
                    </div>
                </div>
                
                @foreach ($this->getSlotsByPosition('bottom') as $slot)
                    <div id="{{ $slot->handle }}">
                        <div>
                            @livewire($slot->component, ['slotModel' => $customer], key('top-slot-' . $slot->handle))
                        </div>
                    </div>
                @endforeach  

                <div class="p-4 text-right rounded-b bg-gray-50">
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

        <div class="space-y-4 xl:w-2/3">
            <div>
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            {{ __('adminhub::components.customers.show.metrics.total_orders') }}
                        </dt>

                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->ordersCount }}</dd>
                    </div>

                    <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            {{ __('adminhub::components.customers.show.metrics.avg_spend') }}
                        </dt>

                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->avgSpend->formatted }}</dd>
                    </div>

                    <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            {{ __('adminhub::components.customers.show.metrics.total_spend') }}
                        </dt>

                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->totalSpend->formatted }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded shadow">
                <header class="px-4 py-4 font-bold border-b">
                    {{ __('adminhub::components.customers.show.year_spending') }}
                </header>

                <div class="p-4 h-80">
                    @livewire('hub.components.reporting.apex-chart', ['options' => $this->spendingChart])
                </div>
            </div>
        </div>
    </div>

    <div>
        <div x-data="{ tab: 'order_history' }">
            <div>
                <div class="hidden sm:block">
                    <nav class="flex space-x-4"
                         aria-label="Tabs">

                        <button type="button"
                                x-on:click.prevent="tab = 'order_history'"
                                class="px-3 py-2 text-sm font-medium rounded-md "
                                :class="{
                                    'bg-white shadow': tab == 'order_history',
                                    'hover:text-gray-700 text-gray-500': tab != 'order_history'
                                }">
                            {{ __('adminhub::components.customers.show.order_history') }}
                        </button>

                        <button type="button"
                                x-on:click.prevent="tab = 'purchase_history'"
                                class="px-3 py-2 text-sm font-medium rounded-md "
                                :class="{
                                    'bg-white shadow': tab == 'purchase_history',
                                    'hover:text-gray-700 text-gray-500': tab != 'purchase_history'
                                }">
                            {{ __('adminhub::components.customers.show.purchase_history') }}
                        </button>

                        <button type="button"
                                x-on:click.prevent="tab = 'users'"
                                class="px-3 py-2 text-sm font-medium rounded-md "
                                :class="{
                                    'bg-white shadow': tab == 'users',
                                    'hover:text-gray-700 text-gray-500': tab != 'users'
                                }">
                            {{ __('adminhub::components.customers.show.users') }}
                        </button>

                        <a href="#"
                           x-on:click.prevent="tab = 'addresses'"
                           class="px-3 py-2 text-sm font-medium rounded-md "
                           :class="{
                               'bg-white shadow': tab == 'addresses',
                               'hover:text-gray-700 text-gray-500': tab != 'addresses'
                           }">
                            {{ __('adminhub::components.customers.show.addresses') }}
                        </a>
                    </nav>
                </div>
            </div>

            <div x-show="tab == 'order_history'"
                 class="mt-4">
                @if (!$this->orders->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500">
                        {{ __('adminhub::components.customers.show.no_order_history') }}
                    </div>
                @else
                    @livewire('hub.components.orders.table', [
                        'searchable' => false,
                        'canSaveSearches' => false,
                        'filterable' => false,
                        'customerId' => $this->customer->id,
                    ])
                @endif
            </div>

            <div x-show="tab == 'purchase_history'"
                 class="mt-4">
                @if (!$this->purchaseHistory->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500">
                        {{ __('adminhub::components.customers.show.no_purchase_history') }}
                    </div>
                @else
                    @include('adminhub::partials.customers.purchase-history')
                @endif
            </div>

            <div x-show="tab == 'users'"
                 class="mt-4">
                @if (!$this->users->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500">
                        {{ __('adminhub::components.customers.show.no_users') }}
                    </div>
                @else
                    @include('adminhub::partials.customers.users')
                @endif
            </div>

            <div x-show="tab == 'addresses'"
                 class="mt-4">
                @if (!$this->addresses->count())
                    <div class="w-full mt-12 text-sm text-center text-gray-500">
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
