<div
     class="p-4 border divide-y divide-gray-100 rounded-lg bg-black/5 dark:bg-white/5 border-black/10 dark:border-white/10">
    @if ($this->shippingLines->count())
        <div class="pb-4 mb-4 border-b border-black/10 dark:border-white/10">
            <ul class="space-y-2 text-sm text-gray-900 dark:text-white">
                @foreach ($this->shippingLines as $shippingLine)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center">
                            <x-hub::icon ref="truck"
                                         class="mr-2" />

                            {!! $shippingLine->description !!}
                        </div>

                        <strong>
                            {{ $shippingLine->sub_total->formatted }}
                        </strong>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2">
            <article class="space-y-2">
                @if ($deliveryInstructions = $this->shippingAddress?->delivery_instructions)
                    <div>
                        <strong>{{ __('adminhub::global.delivery_instructions') }}:</strong>

                        <p class="mt-1 text-sm">{{ $deliveryInstructions }}</p>
                    </div>
                @endif

                <div>
                    <strong class="text-gray-900 dark:text-white">
                        {{ __('adminhub::global.notes') }}:
                    </strong>

                    <p @class([
                        'text-sm mt-1',
                        'text-gray-500 dark:text-gray-400' => !$order->notes,
                    ])>
                        @if ($order->notes)
                            {{ $order->notes }}
                        @else
                            {{ __('adminhub::partials.orders.totals.notes_empty') }}
                        @endif
                    </p>
                </div>
            </article>
        </div>

        <div>
            <dl class="space-y-1 text-sm text-right text-gray-700 dark:text-gray-200">
                <div class="flex justify-between">
                    <dt>{{ __('adminhub::partials.orders.totals.sub_total') }}</dt>
                    <dd>{{ $order->sub_total->formatted }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt>{{ __('adminhub::partials.orders.totals.shipping_total') }}</dt>
                    <dd>{{ $order->shipping_total->formatted }}</dd>
                </div>

                @foreach ($order->tax_breakdown as $tax)
                    <div class="flex justify-between">
                        <dt>{{ $tax->description }}</dt>
                        <dd>{{ $tax->total->formatted }}</dd>
                    </div>
                @endforeach

                <div class="flex justify-between font-bold text-gray-900 dark:text-white">
                    <dt>{{ __('adminhub::partials.orders.totals.total') }}</dt>
                    <dd>{{ $order->total->formatted }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
