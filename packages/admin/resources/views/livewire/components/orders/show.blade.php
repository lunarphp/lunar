<section class="px-12 mx-auto max-w-7xl">
  <header class="flex items-center">
    <h1 class="text-lg font-bold md:text-2xl">
      <span class="text-gray-500">Orders //</span> #{{ $order->id }}
    </h1>

    <time class="ml-8 text-sm font-medium text-gray-500">
      Today at 08:00pm GMT
    </time>
  </header>

  <div class="grid items-start grid-cols-3 gap-8 mt-8">
    <div class="col-span-2">
      <div class="flex items-center space-x-2 text-sm">
        <button class="inline-flex items-center p-2 font-medium rounded hover:bg-gray-200">
          <x-hub::icon
            ref="printer"
            style="solid"
            class="w-4 mr-2"
          />

          Print
        </button>

        <button class="inline-flex items-center p-2 font-medium rounded hover:bg-gray-200">
          <x-hub::icon
            ref="rewind"
            style="solid"
            class="w-4 mr-2"
          />

          Refund
        </button>

        <button class="inline-flex items-center p-2 font-medium rounded hover:bg-gray-200">
          <x-hub::icon
            ref="credit-card"
            style="solid"
            class="w-4 mr-2"
          />

          Add Payment
        </button>

        <button class="inline-flex items-center p-2 font-medium rounded hover:bg-gray-200">
          <x-hub::icon
            ref="flag"
            style="solid"
            class="w-4 mr-2"
          />

          Update Status
        </button>

        <button class="inline-flex items-center p-2 font-medium rounded hover:bg-gray-200">
          More Actions

          <x-hub::icon
            ref="chevron-down"
            style="solid"
            class="w-4 ml-2"
          />
        </button>
      </div>

      <div class="p-4 mt-4 bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="flow-root">
          <div class="-my-4 divide-y divide-gray-200">
            @foreach ($this->visibleLines as $line)
              <div
                class="py-4"
                x-data="{ showDetails: false }"
              >
                <div class="grid items-start grid-cols-8 gap-2">
                  <div class="flex gap-2">
                    <input
                      class="w-5 h-5 text-blue-500 border-gray-200 rounded cursor-pointer form-checkbox"
                      aria-label="{{ $line->id }}"
                      type="checkbox"
                    >

                    <div class="p-1 overflow-hidden border border-gray-200 rounded aspect-square">
                      <img
                        class="object-contain w-full h-full"
                        src="{{ $line->purchasable->getThumbnail() }}"
                      />
                    </div>
                  </div>

                  <div class="col-span-5">
                    <button
                      class="flex gap-2"
                      x-on:click="showDetails = !showDetails"
                      type="button"
                    >
                      <x-hub::icon
                        ref="chevron-right"
                        style="solid"
                        class="w-6 text-gray-400 transition group-hover:text-gray-600"
                      />

                      <div class="max-w-sm">
                        <div class="flex gap-4 text-left">
                          <p class="text-sm font-bold leading-tight">
                            {{ $line->description }}
                          </p>

                          <p class="flex-shrink-0 text-xs text-gray-500">
                            KB123450ASDB
                          </p>
                        </div>

                        <div class="flex mt-1 text-xs text-gray-500">
                          <p>CONV-70-1</p>

                          <dl class="flex pl-3 ml-3 text-xs border-l border-gray-200">
                            <div class="flex gap-0.5">
                              <dt>Size:</dt>
                              <dd>UK 5</dd>
                            </div>

                            <div class="flex gap-0.5 before:content-['/'] before:mx-1.5 before:text-gray-200">
                              <dt>Color:</dt>
                              <dd>Black</dd>
                            </div>
                          </dl>
                        </div>
                      </div>
                    </button>

                    <article class="pl-8 mt-4">
                      <p class="text-sm">
                        <strong>Notes:</strong>

                        <span class="text-gray-500">
                          Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem, amet perferendis
                          distinctio quos harum atque error odio.
                        </span>
                      </p>
                    </article>
                  </div>

                  <div class="flex items-center justify-end col-span-2 gap-4">
                    <p class="text-sm">
                      {{ $line->quantity }} @ {{ $line->unit_price->formatted }}

                      <span class="ml-1">
                        {{ $line->sub_total->formatted }}
                      </span>
                    </p>

                    <button class="text-gray-400 hover:text-gray-800">
                      <x-hub::icon
                        ref="dots-vertical"
                        style="solid"
                      />
                    </button>
                  </div>
                </div>

                <div x-show="showDetails">
                  <div class="grid grid-cols-8 mt-4 text-xs text-gray-500">
                    <dl class="flex flex-wrap col-span-7 col-start-2 gap-2 pl-8">
                      <div class="flex gap-0.5">
                        <dt>Unit Price:</dt>
                        <dd>$150.00</dd>
                      </div>

                      <div class="flex gap-0.5">
                        <dt>Quantity:</dt>
                        <dd>1</dd>
                      </div>

                      <div class="flex gap-0.5">
                        <dt>Sub Total:</dt>
                        <dd>$150.00</dd>
                      </div>

                      <div class="flex gap-0.5">
                        <dt>Discount Total:</dt>
                        <dd>$50.00</dd>
                      </div>

                      <div class="flex gap-0.5">
                        <dt>Tax Total:</dt>
                        <dd>$20.00</dd>
                      </div>

                      <div class="flex gap-0.5">
                        <dt>Total:</dt>
                        <dd>$120.00</dd>
                      </div>

                      <div class="flex gap-0.5">
                        <dt>Total:</dt>
                        <dd>$120.00</dd>
                      </div>

                      <div class="flex gap-0.5">
                        <dt>Total:</dt>
                        <dd>$120.00</dd>
                      </div>
                    </dl>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        @if ($this->physicalLines->count() > $maxLines)
          <button
            type="button"
            wire:click="$set('allLinesVisible', {{ !$allLinesVisible }})"
            class="w-full py-1 text-sm text-center text-gray-600 bg-gray-200 hover:bg-gray-300"
          >
            @if (!$allLinesVisible)
              Show remaining lines
            @else
              Collapse lines
            @endif
          </button>
        @endif

        <div class="p-3 space-y-2 text-sm">
          @foreach ($this->shippingLines as $shippingLine)
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
                @if ($order->notes)
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
                @foreach ($order->tax_breakdown as $tax)
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

      <div class="mt-4">
        <header>
          <h3>Transactions</h3>
        </header>

        @foreach ($order->transactions as $transaction)
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

    <div class="sticky space-y-4 top-4">
      <div class="flex items-center justify-between">
        <p class="font-bold truncate">
          Alec Ritson
        </p>

        <button
          class="flex-shrink-0 px-5 py-3 ml-4 text-xs font-bold bg-gray-200 rounded"
          type="button"
        >
          View User
        </button>
      </div>

      <div
        class="flex items-center px-4 py-3 text-green-700 border border-current rounded bg-green-50"
        role="alert"
      >
        <x-hub::icon
          ref="check-circle"
          style="outline"
          class="w-5"
        />

        <p class="ml-3 text-sm font-bold">
          Payment Received
        </p>
      </div>

      <dl class="space-y-4 text-sm">
        <div class="grid grid-cols-2 gap-4">
          <dt class="font-bold">
            Reference:
          </dt>

          <dd class="text-right">
            2022-02-0001
          </dd>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <dt class="font-bold">
            Channel:
          </dt>

          <dd class="text-right">
            Webstore
          </dd>
        </div>
      </dl>

      <section class="p-4 bg-white border border-gray-200 rounded-lg">
        <div class="flex items-center justify-between">
          <p class="font-bold">
            Shipping Address
          </p>

          <button
            class="px-3 py-1.5 text-xs font-bold bg-gray-200 rounded"
            type="button"
          >
            Edit
          </button>
        </div>

        <address class="mt-4 text-sm not-italic text-gray-700">
          Alec Ritson <br>
          16 Mons Way <br>
          Maldon <br>
          CM9 6FU <br>
          United Kingdom
        </address>
      </section>

      <section class="p-4 bg-white border border-gray-200 rounded-lg">
        <p class="font-bold">
          Billing Address
        </p>

        <p class="mt-4 text-sm text-gray-700">
          Same as shipping address
        </p>
      </section>

      <section class="p-4 bg-white border border-gray-200 rounded-lg">
        <p class="font-bold">
          Additional Details
        </p>

        <dl class="mt-4 space-y-4 text-sm">
          <div class="grid grid-cols-2 gap-4">
            <dt class="font-bold">
              Metafield:
            </dt>

            <dd>
              Lorem ipsum dolor sit amet.
            </dd>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <dt class="font-bold">
              Metafield 2:
            </dt>

            <dd>
              Lorem ipsum, dolor sit amet consectetur adipisicing elit.
            </dd>
          </div>
        </dl>
      </section>
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
              @if ($order->placed_at)
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
              @if ($order->placed_at)
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
    @if ($this->shipping)
      <div class="p-4 bg-white rounded-lg">
        <h3 class="font-semibold text-gray-900">Shipping Option{{ $this->shippingLines->count() > 1 ? 's' : null }}</h3>
        @foreach ($this->shippingLines as $line)
          {{ $line->description }}
        @endforeach
      </div>
      <div class="p-4 bg-white rounded-lg">
        <h3 class="font-semibold text-gray-900">Shipping Address</h3>
        <div class="mt-2">
          <span class="adr">
            <span class="block">{{ $this->shipping->fullName }}</span>
            <span class="block">{{ $this->shipping->line_one }}</span>
            @if ($this->shipping->line_two)
              <span class="block">{{ $this->shipping->line_two }}</span>
            @endif
            @if ($this->shipping->line_three)
              <span class="block">{{ $this->shipping->line_three }}</span>
            @endif
            @if ($this->shipping->city)
              <span class="block">{{ $this->shipping->city }}</span>
            @endif
            @if ($this->shipping->state)
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
            @if ($this->billing->line_two)
              <span class="block">{{ $this->billing->line_two }}</span>
            @endif
            @if ($this->billing->line_three)
              <span class="block">{{ $this->billing->line_three }}</span>
            @endif
            @if ($this->billing->city)
              <span class="block">{{ $this->billing->city }}</span>
            @endif
            @if ($this->billing->state)
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
            @foreach ($this->order->lines as $line)
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
            @foreach ($order->tax_breakdown as $tax)
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
            @foreach ($order->transactions as $transaction)
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
      @foreach ($this->statuses as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
      @endforeach
    </x-hub::input.select>

    <x-slot name="footer">
      <x-hub::button type="button" wire:click.prevent="$set('updatingStatus', false)" theme="gray">{{ __('adminhub::global.cancel') }}</x-hub::button>
      <x-hub::button type="button" wire:click="saveStatus">Save</x-hub::button>
    </x-slot>
  </x-hub::modal.dialog> --}}

    {{-- <div>
      <x-hub::button type="button" wire:click="$set('updatingStatus', true)">Update Status</x-hub::button>
    </div> --}}

    {{-- <header>
          <h3>Order Lines ({{ $this->physicalLines->count() }})</h3>
        </header> --}}
    {{-- <x-hub::input.checkbox /> --}}
</section>
