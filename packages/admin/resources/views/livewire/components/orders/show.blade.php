<section>
    <header class="flex items-center">
        <h1 class="text-lg font-bold text-gray-900 md:text-2xl">
            <span class="text-gray-500">
                {{ __('adminhub::components.orders.show.title') }} //
            </span>

            #{{ $order->id }}
        </h1>
    </header>

    <div class="grid grid-cols-1 gap-8 mt-8 lg:items-start lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($this->requiresCapture)
                <div class="mb-4">
                    <x-hub::alert level="danger">
                        {{ __('adminhub::components.orders.show.requires_capture') }}
                    </x-hub::alert>
                </div>
            @endif

            <div class="flex items-center space-x-2">
                @include('adminhub::partials.orders.actions')
            </div>

            <div class="mt-4">
                @if ($this->paymentStatus == 'partial-refund')
                    <div class="border border-blue-500 rounded">
                        <x-hub::alert>
                            {{ __('adminhub::components.orders.show.partially_refunded') }}
                        </x-hub::alert>
                    </div>
                @endif

                @if ($this->paymentStatus == 'refunded')
                    <div class="border border-red-500 rounded">
                        <x-hub::alert level="danger">
                            {{ __('adminhub::components.orders.show.refunded') }}
                        </x-hub::alert>
                    </div>
                @endif
            </div>

            <div class="p-6 mt-4 bg-white rounded-lg shadow">
                <div class="flow-root">
                    <ul class="divide-y divide-gray-100">
                        @include('adminhub::partials.orders.lines')
                    </ul>
                </div>

                @if ($this->physicalAndDigitalLines->count() > $maxLines)
                    <div class="mt-4 text-center">
                        @if (!$allLinesVisible)
                            <div class="relative">
                                <hr class="absolute block w-full border-red-200 top-3 border-b-1 transparent" />

                                <div class="relative">
                                    <span class="px-2 text-xs font-medium text-red-600 bg-white">
                                        {{ __('adminhub::components.orders.show.additional_lines_text', [
                                            'count' => $this->physicalAndDigitalLines->count() - $maxLines,
                                        ]) }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <button class="px-3 py-1 mt-1 text-xs text-blue-800 border rounded shadow-sm"
                                wire:click="$set('allLinesVisible', {{ !$allLinesVisible }})"
                                type="button">
                            @if (!$allLinesVisible)
                                {{ __('adminhub::components.orders.show.show_all_lines_btn') }}
                            @else
                                {{ __('adminhub::components.orders.show.collapse_lines_btn') }}
                            @endif
                        </button>
                    </div>
                @endif

                <div class="mt-8">
                    @include('adminhub::partials.orders.totals')
                </div>
            </div>

            <div class="mt-4">
                <header class="sr-only">
                    {{ __('adminhub::components.orders.show.transactions_header') }}
                </header>

                @include('adminhub::partials.orders.transactions')
            </div>

            <div class="mt-4">
                <header class="my-6 font-medium">
                    {{ __('adminhub::components.orders.show.timeline_header') }}
                </header>

                @livewire('hub.components.activity-log-feed', [
                    'subject' => $this->order,
                ])
            </div>
        </div>

        <div class="space-y-4">
        <section class="p-4 bg-white rounded-lg shadow">
            <header>
                <strong class="text-gray-700">
                  {{ __('adminhub::components.orders.show.tags_header') }}
                </strong>
            </header>
            @livewire('hub.components.tags', [
              'taggable' => $order,
              'independant' => true,
            ])
        </section>
            @if ($order->customer)
                <header class="flex items-center justify-between">
                    <strong class="text-gray-700 truncate">
                        {{ $order->customer->first_name }}
                        @if ($order->customer->last_name)
                            {{ $order->customer->last_name }}
                        @endif
                    </strong>

                    <a class="flex-shrink-0 px-4 py-2 ml-4 text-xs font-bold text-gray-700 border rounded bg-gray-50 hover:bg-white"
                       href="{{ route('hub.customers.show', $order->customer) }}">
                        {{ __('adminhub::components.orders.show.view_customer') }}
                    </a>
                </header>
            @endif

            @foreach($this->getSlotsByPosition('top') as $slot)
             <div id="{{ $slot->handle }}">
              <div>@livewire($slot->component, ['slotModel' => $order], key("top-slot-{{ $slot->handle }}"))</div>
             </div>
            @endforeach

            <section class="bg-white rounded-lg shadow">
                @include('adminhub::partials.orders.details')
            </section>

            <section class="p-4 bg-white rounded-lg shadow">
                @include('adminhub::partials.orders.address', [
                    'heading' => __('adminhub::components.orders.show.shipping_header'),
                    'editTrigger' => 'showShippingAddressEdit',
                    'hidden' => false,
                    'address' => $this->shippingAddress,
                ])
            </section>

            <section class="p-4 bg-white rounded-lg shadow">
                @include('adminhub::partials.orders.address', [
                    'heading' => __('adminhub::components.orders.show.billing_header'),
                    'editTrigger' => 'showBillingAddressEdit',
                    'hidden' => $this->shippingEqualsBilling,
                    'message' => __('adminhub::components.orders.show.billing_matches_shipping'),
                    'address' => $this->billingAddress,
                ])
            </section>

            <section class="p-4 bg-white rounded-lg shadow">


                <header>
                    <strong class="text-gray-700">
                        {{ __('adminhub::components.orders.show.additional_fields_header') }}
                    </strong>
                </header>

                <dl class="mt-4 space-y-2 text-sm text-gray-600">
                    @foreach ($this->metaFields as $key => $value)
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="font-medium text-gray-700">
                                {{ $key }}:
                            </dt>

                            <dd class="col-span-2">
                                @if (!is_string($value))
                                    <pre class="font-mono">{{ json_encode($value) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>




            @foreach($this->getSlotsByPosition('bottom') as $slot)
             <div id="{{ $slot->handle }}">
              <div>@livewire($slot->component, ['slotModel' => $order], key("bottom-slot-{{ $slot->handle }}"))</div>
             </div>
            @endforeach
        </div>

        <x-hub::modal.dialog form="updateStatus"
                             wire:model="showUpdateStatus">
            <x-slot name="title">
                {{ __('adminhub::orders.update_status.title') }}
            </x-slot>

            <x-slot name="content">
                <x-hub::input.group :label="__('adminhub::inputs.status.label')"
                                    for="status"
                                    required
                                    :error="$errors->first('status')">
                    <x-hub::input.select wire:model.defer="order.status"
                                         required>
                        @foreach ($this->statuses as $handle => $status)
                            <option value="{{ $handle }}">{{ $status['label'] }}</option>
                        @endforeach
                    </x-hub::input.select>
                </x-hub::input.group>
            </x-slot>

            <x-slot name="footer">
                <x-hub::button type="button"
                               wire:click.prevent="$set('showUpdateStatus', false)"
                               theme="gray">
                    {{ __('adminhub::global.cancel') }}
                </x-hub::button>

                <x-hub::button type="submit">
                    {{ __('adminhub::orders.update_status.btn') }}
                </x-hub::button>
            </x-slot>
        </x-hub::modal.dialog>

        <x-hub::modal wire:model="showRefund">
            <div class="p-4">
                @livewire('hub.components.orders.refund', [
                    'order' => $this->order,
                    'amount' => $this->refundAmount / 100,
                ])
            </div>
        </x-hub::modal>

        <x-hub::modal wire:model="showCapture">
            <div class="p-4">
                @livewire('hub.components.orders.capture', [
                    'order' => $this->order,
                    'amount' => $this->order->total->decimal,
                ])
            </div>
        </x-hub::modal>

        <x-hub::slideover wire:model="showShippingAddressEdit"
                          form="saveShippingAddress">
            @include('adminhub::partials.forms.address', [
                'bind' => 'shippingAddress',
                'states' => $this->shippingStates,
            ])

            <x-slot name="footer">
                <x-hub::button wire:click.prevent="$set('showShippingAddressEdit', false)"
                               theme="gray">
                    {{ __('adminhub::global.cancel') }}
                </x-hub::button>

                <x-hub::button type="submit">
                    {{ __('adminhub::components.orders.show.save_shipping_btn') }}
                </x-hub::button>
            </x-slot>
        </x-hub::slideover>

        <x-hub::slideover wire:model="showBillingAddressEdit"
                          form="saveBillingAddress">
            @include('adminhub::partials.forms.address', [
                'bind' => 'billingAddress',
                'states' => $this->billingStates,
            ])

            <x-slot name="footer">
                <x-hub::button wire:click.prevent="$set('showBillingAddressEdit', false)"
                               theme="gray">
                    {{ __('adminhub::global.cancel') }}
                </x-hub::button>

                <x-hub::button type="submit">
                    {{ __('adminhub::components.orders.show.save_billing_btn') }}
                </x-hub::button>
            </x-slot>
        </x-hub::slideover>
    </div>
</section>
