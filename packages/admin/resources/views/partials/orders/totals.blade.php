<div class="p-4 border rounded-lg bg-gray-50">
  <ul class="space-y-2 text-sm text-gray-900">
    @foreach ($this->shippingLines as $shippingLine)
      <li class="flex items-center justify-between">
        <div class="flex items-center">
          <x-hub::icon
            ref="truck"
            class="mr-2"
          />

          {!! $shippingLine->description !!}
        </div>

        <strong>
          {{ $shippingLine->sub_total->formatted }}
        </strong>
      </li>
    @endforeach
  </ul>

  <div class="grid grid-cols-3 gap-4 pt-4 mt-4 border-t">
    <div class="col-span-2">
      <article class="space-y-2">
        @if($deliveryInstructions = $this->shippingAddress?->delivery_instructions)
          <div>
            <strong>{{ __('adminhub::global.delivery_instructions') }}:</strong>

            <p class="text-sm mt-1">{{ $deliveryInstructions }}</p>
          </div>
        @endif
        <div>
          <strong>{{ __('adminhub::global.notes') }}:</strong>

          <p class="text-sm mt-1 {{ !$order->notes ? 'text-gray-500' : '' }}">
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
      <dl class="space-y-1 text-sm text-right text-gray-700">
        <div class="flex justify-between">
          <dt>{{ __('adminhub::partials.orders.totals.sub_total') }}</dt>
          <dd>{{ $order->sub_total->formatted }}</dd>
        </div>

        <div class="flex justify-between">
          <dt>{{ __('adminhub::partials.orders.totals.discount_total') }}</dt>
          <dd class="text-red-500">-{{ $order->discount_total->formatted }}</dd>
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

        <div class="flex justify-between font-bold text-gray-900">
          <dt>{{ __('adminhub::partials.orders.totals.total') }}</dt>
          <dd>{{ $order->total->formatted }}</dd>
        </div>
      </dl>
    </div>
  </div>
</div>
