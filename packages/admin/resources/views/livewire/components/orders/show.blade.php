<div class="flex-col px-12 mx-auto space-y-4 max-w-7xl">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">Orders // #{{ $order->id }}</strong>
    {{-- <div>
      <x-hub::button type="button" wire:click="$set('updatingStatus', true)">Update Status</x-hub::button>
    </div> --}}
  </div>

  <div class="flex">
    <div class="w-2/3">
      <div class="flex w-full my-4 text-xs text-gray-600">
        <div class="px-3 pl-0 border-r">
          <button class="inline-flex items-center">
            <x-hub::icon ref="printer" style="solid" class="w-3 mr-1" />
            Print
          </button>
        </div>

        <div class="px-3 border-r">
          <button class="inline-flex items-center">
            <x-hub::icon ref="rewind" style="solid" class="w-3 mr-1" />
            Refund
          </button>
        </div>

        <div class="px-3 border-r">
          <button class="inline-flex items-center">
            <x-hub::icon ref="credit-card" style="solid" class="w-3 mr-1" />
            Add Payment
          </button>
        </div>

        <div class="px-3 border-r">
          <button class="inline-flex items-center">
            <x-hub::icon ref="flag" style="solid" class="w-3 mr-1" />
            Update Status
          </button>
        </div>

        <div class="px-3">
          <button class="inline-flex items-center">
            More Actions
            <x-hub::icon ref="chevron-down" style="solid" class="w-3 ml-1" />
          </button>
        </div>
      </div>

      <div>
        <header>
          <h3>Order Lines ({{ $this->physicalLines->count() }})</h3>
        </header>
        <div class="mt-4 space-y-2">
          <div class="bg-white rounded shadow ">
            @foreach($this->visibleLines as $line)
              <div class="flex w-full p-4 space-x-2 border-b">
                <x-hub::input.checkbox />
                <button>
                  <x-hub::icon ref="chevron-right" style="solid" class="w-6 text-gray-400 hover:text-gray-600" />
                </button>

                <div class="w-8">
                  <img src="{{ $line->purchasable->getThumbnail() }}" class="w-6 rounded" />
                </div>

                <div>
                  <p class="text-sm leading-tight text-gray-800">{{ $line->description }}</p>
                </div>

                <div class="w-1/3 text-sm text-right grow">
                  <span class="mr-2">{{ $line->quantity }} @ {{ $line->unit_price->formatted }}</span>
                  {{ $line->sub_total->formatted }}
                </div>

                <div>
                  <button class="text-gray-400 hover:text-gray-800">
                    <x-hub::icon ref="dots-vertical" style="solid" />
                  </button>
                </div>
              </div>
            @endforeach
            @if($this->physicalLines->count() > $maxLines)
              <button
                type="button"
                wire:click="$set('allLinesVisible', {{ !$allLinesVisible }})"
                class="w-full py-1 text-sm text-center text-gray-600 bg-gray-200 hover:bg-gray-300"
              >
                @if(!$allLinesVisible)
                  Show remaining lines
                @else
                  Collapse lines
                @endif
              </button>
            @endif

            <div class="p-3 space-y-2 text-sm">
              @foreach($this->shippingLines as $shippingLine)
                <div class="flex justify-between w-full p-4 border rounded">
                  <div class="flex items-center">
                    <x-hub::icon ref="truck" />
                    <span class="block ml-2">{!! $shippingLine->description !!}</span>
                  </div>
                  <span>{{ $shippingLine->sub_total->formatted }}</span>
                </div>
              @endforeach
            </div>

            <div class="p-3">
              <div class="flex p-3 bg-gray-50">
                <div class="grow">
                  <strong class="text-sm">Notes</strong>
                  <p class="text-sm">
                    @if($order->notes)
                      {{ $order->notes }}
                    @else
                      <span class="text-gray-500">No notes on this order</span>
                    @endif
                  </p>
                </div>

                <div class="w-1/3 text-sm text-right">
                  <dl class="space-y-2">
                    <div class="flex items-center justify-between">
                      <dt>Sub Total</dt>
                      <dd>{{ $order->sub_total->formatted }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                      <dt>Shipping Total</dt>
                      <dd>{{ $order->shipping_total->formatted }}</dd>
                    </div>
                    @foreach($order->tax_breakdown as $tax)
                    <div class="flex items-center justify-between">
                      <dt>{{ $tax->description }}</dt>
                      <dd>{{ $tax->total->formatted }}</dd>
                    </div>
                    @endforeach
                    <div class="flex items-center justify-between font-bold">
                      <dt>Total</dt>
                      <dd>{{ $order->total->formatted }}</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </div>


        </div>

        <div class="mt-4">
          <header>
            <h3>Transactions</h3>
          </header>

          @foreach($order->transactions as $transaction)
            <div class="flex p-3 bg-white rounded">
              <div>
                {{ $transaction->status }}
              </div>
              <div>
                {{ $transaction->card_type }}
              </div>

              <div class="flex items-center space-x-2">
                <span class="block">&ast;&ast;&ast;&ast;</span>
                <span class="block">&ast;&ast;&ast;&ast;</span>
                <span class="block">&ast;&ast;&ast;&ast;</span>
                <span class="block">{{ $transaction->last_four }}</span>
              </div>

              <div>
                {{ $transaction->amount->formatted }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- <div class="grid grid-cols-6 gap-4">
    <div>
      <div class="flex items-center px-4 py-4 bg-white rounded-lg">
        <div class="flex items-center">
          <div>
            <span class="block text-xs">Status</span>
            <strong class="text-sm font-bold">{{ $this->status }}</strong>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex items-center px-4 py-4 bg-white rounded-lg">
        <div class="flex items-center">
          <div>
            <span class="block text-xs">Reference</span>
            <strong class="text-sm font-bold">{{ $order->reference }}</strong>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex items-center px-4 py-4 bg-white rounded-lg">
        <div class="flex items-center">
          <div>
            <span class="block text-xs">Customer Reference</span>
            <strong class="text-sm font-bold">{{ $order->customer_reference ?: '-' }}</strong>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex items-center px-4 py-4 bg-white rounded-lg">
        <div class="flex items-center">
          <div>
            <span class="block text-xs">Date</span>
            <strong class="text-sm font-bold">
              @if($order->placed_at)
                {{ $order->placed_at->format('jS M Y') }}
              @else
                {{ $order->created_at->format('jS M Y') }}
              @endif
            </strong>
          </div>
        </div>
      </div>
    </div>

    <div >
      <div class="flex items-center px-4 py-4 bg-white rounded-lg">
        <div class="flex items-center">
          <div>
            <span class="block text-xs">Time</span>
            <strong class="text-sm font-bold">
              @if($order->placed_at)
                {{ $order->placed_at->format('h:ma') }}
              @else
                {{ $order->created_at->format('h:ma') }}
              @endif
            </strong>
          </div>
        </div>
      </div>
    </div>

    <!-- End Top Stats -->


  </div>

  <div class="grid grid-cols-3 gap-4">
    @if($this->shipping)
      <div class="p-4 bg-white rounded-lg">
        <h3 class="font-semibold text-gray-900">Shipping Option{{ $this->shippingLines->count() > 1 ? 's' : null }}</h3>
        @foreach($this->shippingLines as $line)
          {{ $line->description }}
        @endforeach
      </div>
      <div class="p-4 bg-white rounded-lg">
        <h3 class="font-semibold text-gray-900">Shipping Address</h3>
        <div class="mt-2">
          <span class="adr">
            <span class="block">{{ $this->shipping->fullName }}</span>
            <span class="block">{{ $this->shipping->line_one }}</span>
            @if($this->shipping->line_two)
              <span class="block">{{ $this->shipping->line_two }}</span>
            @endif
            @if($this->shipping->line_three)
              <span class="block">{{ $this->shipping->line_three }}</span>
            @endif
            @if($this->shipping->city)
              <span class="block">{{ $this->shipping->city }}</span>
            @endif
            @if($this->shipping->state)
              <span class="block">{{ $this->shipping->state }}</span>
            @endif
            <span class="block">{{ $this->shipping->postcode }}</span>
          </span>
        </div>
      </div>
      @endif
      <div class="p-4 bg-white rounded-lg">
        <h3 class="font-semibold text-gray-900">Billing Address</h3>
        <div class="mt-2">
          <span class="adr">
            <span class="block">{{ $this->billing->fullName }}</span>
            <span class="block">{{ $this->billing->line_one }}</span>
            @if($this->billing->line_two)
              <span class="block">{{ $this->billing->line_two }}</span>
            @endif
            @if($this->billing->line_three)
              <span class="block">{{ $this->billing->line_three }}</span>
            @endif
            @if($this->billing->city)
              <span class="block">{{ $this->billing->city }}</span>
            @endif
            @if($this->billing->state)
              <span class="block">{{ $this->billing->state }}</span>
            @endif
            <span class="block">{{ $this->billing->postcode }}</span>
          </span>
        </div>
      </div>
  </div>
  <div class="mt-8">
   <div class="p-4 bg-white rounded-lg">
      <h3 class="text-lg font-semibold text-gray-900">Order Lines</h3>
      <div>
        <table class="w-full mt-4">
          <thead class="font-normal">
            <tr class="text-sm text-left text-gray-600 border-b">
              <th class="pb-2 font-normal">Identifier</th>
              <th class="pb-2 font-normal">Description</th>
              <th class="pb-2 font-normal">Option</th>
              <th class="pb-2 font-normal">Quantity</th>
              <th class="pb-2 font-normal">Sub Total</th>
              <th class="pb-2 font-normal">Tax</th>
              <th class="pb-2 font-normal">Discount</th>
              <th class="pb-2 font-normal">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($this->order->lines as $line)
            <tr class="text-sm bg-white even:bg-gray-50">
              <td class="p-2">{{ $line->identifier }}</td>
              <td class="p-2">{{ $line->description }}</td>
              <td class="p-2">{{ $line->option }}</td>
              <td class="p-2">{{ $line->quantity }}</td>
              <td class="p-2">{{ $line->sub_total->formatted }}</td>
              <td class="p-2">{{ $line->tax_total->formatted }}</td>
              <td class="p-2">{{ $line->discount_total->formatted }}</td>
              <td class="p-2">{{ $line->total->formatted }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="text-sm bg-gray-50">
            <tr>
              <td colspan="6"></td>
              <td class="p-2 text-sm">Sub Total</td>
              <td>{{ $order->sub_total->formatted }}</td>
            </tr>
            @foreach($order->tax_breakdown as $tax)
              <tr>
                <td colspan="6"></td>
                <td class="p-2 text-sm">{{ $tax->description }}</td>
                <td>{{ $tax->total->formatted }}</td>
              </tr>
            @endforeach
            <tr>
              <td colspan="6"></td>
              <td class="p-2 text-sm">Total</td>
              <td>{{ $order->total->formatted }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-8">
    <div class="p-4 bg-white rounded-lg">
      <h3 class="text-lg font-semibold text-gray-900">Transactions</h3>
      <div>
        <table class="w-full mt-4">
          <thead class="font-normal">
            <tr class="text-sm text-left text-gray-600 border-b">
              <th class="pb-2 font-normal">Status</th>
              <th class="pb-2 font-normal">Success</th>
              <th class="pb-2 font-normal">Refund</th>
              <th class="pb-2 font-normal">Amount</th>
              <th class="pb-2 font-normal">Card Type</th>
              <th class="pb-2 font-normal">Last four</th>
              <th class="pb-2 font-normal">Date</th>
              <th class="pb-2 font-normal">Notes</th>
            </tr>
          </thead>
          <tbody>
            @foreach($order->transactions as $transaction)
              <tr>
                <td class="p-2">{{ $transaction->status }}</td>
                <td class="p-2">{{ $transaction->success ? 'Yes' : 'No' }}</td>
                <td class="p-2">{{ $transaction->refund ? 'Yes' : 'No' }}</td>
                <td class="p-2">{{ $transaction->amount->formatted }}</td>
                <td class="p-2">{{ $transaction->card_type }}</td>
                <td class="p-2">{{ $transaction->last_four }}</td>
                <td class="p-2">{{ $transaction->created_at->format('jS M Y h:ma') }}</td>
                <td class="p-2">{{ $transaction->notes }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <x-hub::slideover title="Update Status" wire:model="updatingStatus">
    <x-hub::input.select wire:model="order.status">
      @foreach($this->statuses as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
      @endforeach
    </x-hub::input.select>

    <x-slot name="footer">
      <x-hub::button type="button" wire:click.prevent="$set('updatingStatus', false)" theme="gray">{{ __('adminhub::global.cancel') }}</x-hub::button>
      <x-hub::button type="button" wire:click="saveStatus">Save</x-hub::button>
    </x-slot>
  </x-hub::modal.dialog> --}}
</div>
