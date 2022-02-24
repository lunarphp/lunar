<section class="px-12 mx-auto max-w-7xl">
  <header class="flex items-center">
    <h1 class="text-lg font-bold md:text-2xl">
      <span class="text-gray-500">Orders //</span> #{{ $order->id }}
    </h1>
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
        <header class="sr-only">
          Transactions
        </header>

        <ul class="space-y-4">
          @foreach ($order->transactions as $transaction)
            <li class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg">
              <div class="flex items-center gap-6">
                <strong
                  class="px-4 py-2 text-xs font-bold text-green-700 bg-green-100 border border-current rounded-lg">
                  {{ $transaction->status }}
                </strong>

                <div>
                  <img
                    class="object-contain w-12 h-auto"
                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/1599px-Visa_Inc._logo.svg.png?20170118154621"
                    alt="{{ $transaction->card_type }}"
                  >
                </div>

                <div class="flex items-center gap-2">
                  <span class="block">&ast;&ast;&ast;&ast;</span>
                  <span class="block">&ast;&ast;&ast;&ast;</span>
                  <span class="block">&ast;&ast;&ast;&ast;</span>
                  <span class="block">{{ $transaction->last_four }}</span>
                </div>
              </div>

              <div class="font-bold">
                {{ $transaction->amount->formatted }}
              </div>
            </li>
          @endforeach
        </ul>
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

      <strong class="block p-4 text-xs font-bold text-green-700 bg-green-100 border border-current rounded-lg">
        Payment Received
      </strong>

      <dl class="space-y-2 text-sm">
        <div class="grid grid-cols-2 gap-2">
          <dt class="font-bold">
            Status:
          </dt>

          <dd class="text-right">
            {{ Str::headline($order->status) }}
          </dd>
        </div>

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

        <div class="grid grid-cols-2 gap-2">
          <dt class="font-bold">
            Date:
          </dt>

          <dd class="text-right">
            @if ($order->placed_at)
              {{ $order->placed_at->format('jS M Y') }}
            @else
              {{ $order->created_at->format('jS M Y') }}
            @endif
          </dd>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <dt class="font-bold">
            Time:
          </dt>

          <dd class="text-right">
            @if ($order->placed_at)
              {{ $order->placed_at->format('H:ma') }}
            @else
              {{ $order->created_at->format('H:ma') }}
            @endif
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

    {{-- <x-hub::slideover title="Update Status" wire:model="updatingStatus">
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
