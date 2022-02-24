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

      <div class="p-6 mt-4 space-y-4 bg-white rounded-lg shadow">
        <div class="flow-root">
          <ul class="-my-4 divide-y divide-gray-200">
            @foreach ($this->visibleLines as $line)
              <li
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
                      class="flex gap-2 group"
                      x-on:click="showDetails = !showDetails"
                      type="button"
                    >
                      <x-hub::icon
                        ref="chevron-right"
                        style="solid"
                        class="w-6 text-gray-400 transition group-hover:text-gray-600"
                      />

                      <div class="max-w-sm space-y-2 text-left">
                        <p class="font-bold leading-tight">
                          {{ $line->description }}
                        </p>

                        <p class="text-xs text-gray-700">
                          KB123450ASDB
                        </p>

                        <div class="flex text-xs text-gray-700">
                          <p>CONV-70-1</p>

                          <dl class="flex text-xs before:content-['|'] before:mx-3 before:text-gray-200">
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

                    <article class="pl-8 mt-2">
                      <p class="text-sm text-gray-700">
                        <strong>Notes:</strong>

                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem, amet perferendis
                        distinctio quos harum atque error odio.
                      </p>
                    </article>
                  </div>

                  <div
                    class="relative flex items-center justify-end col-span-2 gap-4"
                    x-data="{ showMenu: false }"
                  >
                    <p class="text-sm">
                      {{ $line->quantity }} @ {{ $line->unit_price->formatted }}

                      <span class="ml-1">
                        {{ $line->sub_total->formatted }}
                      </span>
                    </p>

                    <button
                      class="text-gray-400 hover:text-gray-800"
                      x-on:click="showMenu = !showMenu"
                      type="button"
                    >
                      <x-hub::icon
                        ref="dots-vertical"
                        style="solid"
                      />
                    </button>

                    <div
                      class="absolute right-0 mt-2 text-sm bg-white border rounded-lg shadow-lg top-full"
                      role="menu"
                      x-on:click.away="showMenu = false"
                      x-show="showMenu"
                      x-transition
                    >
                      <div
                        class="py-1"
                        role="none"
                      >
                        <button
                          class="w-full px-4 py-2 text-left transition hover:bg-gray-50"
                          role="menuitem"
                          type="button"
                        >
                          Refund Line
                        </button>

                        <button
                          class="w-full px-4 py-2 text-left transition hover:bg-gray-50"
                          role="menuitem"
                          type="button"
                        >
                          Refund Stock
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div x-show="showDetails">
                  <div class="grid grid-cols-8 mt-4 text-xs">
                    <dl class="flex flex-wrap col-span-7 col-start-2 gap-2 pl-8">
                      @for ($i = 0; $i < 8; $i++)
                        <div class="flex gap-0.5">
                          <dt>Unit Price:</dt>
                          <dd>$150.00</dd>
                        </div>
                      @endfor
                    </dl>
                  </div>
                </div>
              </li>
            @endforeach
          </ul>
        </div>

        @if ($this->physicalLines->count() > $maxLines)
          <div class="flex justify-end">
            <button
              class="flex-shrink-0 px-5 py-3 text-xs font-bold bg-gray-200 rounded"
              wire:click="$set('allLinesVisible', {{ !$allLinesVisible }})"
              type="button"
            >
              @if (!$allLinesVisible)
                Show Remaining Lines
              @else
                Collapse Lines
              @endif
            </button>
          </div>
        @endif

        <ul class="space-y-4 text-sm">
          @foreach ($this->shippingLines as $shippingLine)
            <li class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
              <div class="flex items-center">
                <x-hub::icon
                  ref="truck"
                  class="mr-2"
                />

                {!! $shippingLine->description !!}
              </div>

              {{ $shippingLine->sub_total->formatted }}
            </li>
          @endforeach
        </ul>

        <div class="grid grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
          <div class="col-span-2">
            <article class="text-sm">
              <strong>Notes:</strong>

              <p class="mt-4 {{ !$order->notes ? 'text-gray-500' : '' }}">
                @if ($order->notes)
                  {{ $order->notes }}
                @else
                  No notes on this order
                @endif
              </p>
            </article>
          </div>

          <div>
            <dl class="space-y-2 text-sm text-right">
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

              <div class="flex justify-between font-bold">
                <dt>Total</dt>
                <dd>{{ $order->total->formatted }}</dd>
              </div>
            </dl>
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
      <header class="flex items-center justify-between">
        <p class="font-bold truncate">
          Alec Ritson
        </p>

        <button
          class="flex-shrink-0 px-5 py-3 ml-4 text-sm font-bold bg-gray-200 rounded"
          type="button"
        >
          View User
        </button>
      </header>

      <strong
        class="block px-4 py-3 text-sm font-bold text-green-900 border rounded-lg border-green-900/25 bg-green-50">
        Payment Received
      </strong>

      <dl class="space-y-2 text-sm">
        <div class="grid grid-cols-2 gap-2">
          <dt class="font-bold">
            Reference:
          </dt>

          <dd class="text-right">
            {{ $order->reference }}
          </dd>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <dt class="font-bold">
            Customer Reference:
          </dt>

          <dd class="text-right">
            {{ $order->customer_reference ?: '-' }}
          </dd>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <dt class="font-bold">
            Channel:
          </dt>

          <dd class="text-right">
            {{ $this->channel }}
          </dd>
        </div>
      </dl>

      <section class="p-4 bg-white rounded-lg shadow">
        <header class="flex items-center justify-between">
          <p class="font-bold">
            Shipping Address
          </p>

          <button
            class="px-3 py-1.5 text-xs font-bold bg-gray-200 rounded"
            type="button"
          >
            Edit
          </button>
        </header>

        <address class="mt-4 text-sm not-italic">
          {{ $this->shipping->fullName }} <br>
          {{ $this->shipping->line_one }} <br>

          @if ($this->shipping->line_two)
            {{ $this->shipping->line_two }} <br>
          @endif

          @if ($this->shipping->line_three)
            {{ $this->shipping->line_three }} <br>
          @endif

          @if ($this->shipping->city)
            {{ $this->shipping->city }} <br>
          @endif

          @if ($this->shipping->state)
            {{ $this->shipping->state }} <br>
          @endif

          {{ $this->shipping->postcode }}
        </address>
      </section>

      <section class="p-4 bg-white rounded-lg shadow">
        <header>
          <p class="font-bold">
            Billing Address
          </p>
        </header>

        <p class="mt-4 text-sm {{ !$this->shippingEqualsBilling ? 'text-gray-500' : '' }}">
          @if ($this->shippingEqualsBilling)
            <address class="not-italic">
              {{ $this->billing->fullName }} <br>
              {{ $this->billing->line_one }} <br>

              @if ($this->billing->line_two)
                {{ $this->billing->line_two }} <br>
              @endif

              @if ($this->billing->line_three)
                {{ $this->billing->line_three }} <br>
              @endif

              @if ($this->billing->city)
                {{ $this->billing->city }} <br>
              @endif

              @if ($this->billing->state)
                {{ $this->billing->state }} <br>
              @endif

              {{ $this->billing->postcode }}
            </address>
          @else
            Same as shipping address
          @endif
        </p>
      </section>

      <section class="p-4 bg-white rounded-lg shadow">
        <header>
          <p class="font-bold">
            Additional Details
          </p>
        </header>

        <dl class="mt-4 space-y-2 text-sm">
          <div class="grid grid-cols-3 gap-2">
            <dt class="font-bold">Metafield:</dt>

            <dd class="col-span-2">Lorem ipsum dolor sit amet.</dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="font-bold">Metafield 2:</dt>

            <dd class="col-span-2">Lorem ipsum, dolor sit amet consectetur adipisicing elit.</dd>
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
