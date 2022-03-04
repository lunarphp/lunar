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
              <article>
                <strong>Notes:</strong>

                <p class="text-sm mt-1 {{ !$order->notes ? 'text-gray-500' : '' }}">
                  @if ($order->notes)
                    {{ $order->notes }}
                  @else
                    No notes on this order
                  @endif
                </p>
              </article>
            </div>

            <div>
              <dl class="space-y-1 text-sm text-right text-gray-700">
                <div class="flex justify-between">
                  <dt>Sub Total</dt>
                  <dd>{{ $order->sub_total->formatted }}</dd>
                </div>

                <div class="flex justify-between">
                  <dt>Shipping Total</dt>
                  <dd>{{ $order->shipping_total->formatted }}</dd>
                </div>

                @foreach ($order->tax_breakdown as $tax)
                  <div class="flex justify-between">
                    <dt>{{ $tax->description }}</dt>
                    <dd>{{ $tax->total->formatted }}</dd>
                  </div>
                @endforeach

                <div class="flex justify-between font-bold text-gray-900">
                  <dt>Total</dt>
                  <dd>{{ $order->total->formatted }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>