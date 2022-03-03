<section class="px-12 mx-auto max-w-7xl">
  <header class="flex items-center">
    <h1 class="text-lg font-bold text-gray-900 md:text-2xl">
      <span class="text-gray-500">{{ __('adminhub::components.orders.show.title') }} //</span> #{{ $order->id }}
    </h1>
  </header>

  <div class="grid grid-cols-1 gap-8 mt-8 lg:items-start lg:grid-cols-3">
    <div class="lg:col-span-2">
      <div class="flex items-center text-xs text-gray-700">
        <button
          class="inline-flex items-center px-4 py-2 font-bold transition border border-transparent rounded hover:bg-white hover:border-gray-200"
          type="button"
        >
          <x-hub::icon
            ref="printer"
            style="solid"
            class="w-4 mr-2"
          />

          {{ __('adminhub::components.orders.show.print_btn') }}
        </button>

        <button
          class="inline-flex items-center px-4 py-2 font-bold transition border border-transparent rounded hover:bg-white hover:border-gray-200"
          type="button"
          wire:click.prevent="$set('showRefund', true)"
        >
          <x-hub::icon
            ref="rewind"
            style="solid"
            class="w-4 mr-2"
          />

          @if(count($this->selectedLines))
            {{ __('adminhub::components.orders.show.refund_lines_btn') }}
          @else
            {{ __('adminhub::components.orders.show.refund_btn') }}
          @endif

        </button>

        <button
          class="inline-flex items-center px-4 py-2 font-bold transition border border-transparent rounded hover:bg-white hover:border-gray-200"
          type="button"
            wire:click.prevent="$set('showUpdateStatus', true)"
        >
          <x-hub::icon
            ref="flag"
            style="solid"
            class="w-4 mr-2"

          />

          {{ __('adminhub::components.orders.show.update_status_btn') }}
        </button>

        <div
          class="relative flex justify-end flex-1"
          x-data="{ showMenu: false }"
        >
          <button
            class="inline-flex items-center px-4 py-2 font-bold transition border rounded hover:bg-white bg-gray-50"
            type="button"
            x-on:click="showMenu = !showMenu"
          >
            {{ __('adminhub::components.orders.show.more_actions_btn') }}

            <x-hub::icon
              ref="chevron-down"
              style="solid"
              class="w-4 ml-2"
            />
          </button>

          <div
            class="absolute right-0 z-50 w-screen max-w-[200px] mt-2 text-sm bg-white border rounded-lg shadow-lg top-full"
            role="menu"
            x-on:click.away="showMenu = false"
            x-show="showMenu"
            x-transition
            x-cloak
          >
            <div
              class="py-1"
              role="none"
            >
              <button
                class="flex items-center w-full px-4 py-2 text-left transition hover:bg-white"
                role="menuitem"
                type="button"
              >
                <x-hub::icon
                  ref="credit-card"
                  style="solid"
                  class="w-4 mr-2"
                />

                Add Payment
              </button>

              <button
                class="flex items-center w-full px-4 py-2 text-left transition hover:bg-white"
                role="menuitem"
                type="button"
              >
                <x-hub::icon
                  ref="flag"
                  style="solid"
                  class="w-4 mr-2"
                />

                Update Status
              </button>
            </div>
          </div>
        </div>
      </div>

      <x-hub::modal.dialog form="updateStatus" wire:model="showUpdateStatus">
        <x-slot name="title">
          {{ __('adminhub::orders.update_status.title') }}
        </x-slot>
        <x-slot name="content">
          <x-hub::input.group :label="__('adminhub::inputs.status.label')" for="status" required :error="$errors->first('status')">
            <x-hub::input.select wire:model.defer="order.status" required>
              @foreach($this->statuses as $handle => $status)
                <option value="{{ $handle }}">{{ $status['label'] }}</option>
              @endforeach
            </x-hub::input.select>
          </x-hub::input.group>
        </x-slot>
        <x-slot name="footer">
          <x-hub::button type="button" wire:click.prevent="$set('showUpdateStatus', false)" theme="gray">{{ __('adminhub::global.cancel') }}</x-hub::button>
          <x-hub::button type="submit">
            {{ __('adminhub::orders.update_status.btn') }}
          </x-hub::button>
        </x-slot>
      </x-hub::modal.dialog>

      <div class="p-6 mt-4 space-y-8 bg-white rounded-lg shadow">
        <div class="flow-root">
          <ul class="divide-y divide-gray-100">
            @foreach ($this->visibleLines as $line)
              <li
                class="py-3"
                x-data="{ showDetails: false }"
              >
                <div class="flex items-start">
                  <div class="flex gap-2">
                    <x-hub::input.checkbox value="{{ $line->id }}" wire:model="selectedLines" />
                    <div class="flex-shrink-0 p-1 overflow-hidden border border-gray-100 rounded">
                      <img
                        class="object-contain w-8 h-8"
                        src="{{ $line->purchasable->getThumbnail() }}"
                      />
                    </div>
                  </div>

                  <div class="flex-1">
                    <div class="gap-8 xl:justify-between xl:items-start xl:flex">
                      <div
                        class="relative flex items-center justify-between gap-4 pl-8 xl:justify-end xl:pl-0 xl:order-last"
                        x-data="{ showMenu: false }"
                      >
                        <p class="text-sm font-medium text-gray-700">
                          {{ $line->quantity }} @ {{ $line->unit_price->formatted }}

                          <span class="ml-1">
                            {{ $line->sub_total->formatted }}
                          </span>
                        </p>

                        <button
                          class="text-gray-400 hover:text-gray-500"
                          x-on:click="showMenu = !showMenu"
                          type="button"
                        >
                          <x-hub::icon
                            ref="dots-vertical"
                            style="solid"
                          />
                        </button>

                        <div
                          class="absolute right-0 z-50 mt-2 text-sm bg-white border rounded-lg shadow-lg top-full"
                          role="menu"
                          x-on:click.away="showMenu = false"
                          x-show="showMenu"
                          x-transition
                          x-cloak
                        >
                          <div
                            class="py-1"
                            role="none"
                          >
                            <button
                              class="w-full px-4 py-2 text-left transition hover:bg-white"
                              role="menuitem"
                              type="button"
                            >
                              Refund Line
                            </button>

                            <button
                              class="w-full px-4 py-2 text-left transition hover:bg-white"
                              role="menuitem"
                              type="button"
                            >
                              Refund Stock
                            </button>
                          </div>
                        </div>
                      </div>

                      <button
                        class="flex mt-2 group xl:mt-0"
                        x-on:click="showDetails = !showDetails"
                        type="button"
                      >
                        <div
                          class="transition-transform "
                          :class="{
                            '-rotate-90 ': !showDetails
                          }"
                        >
                        <x-hub::icon
                          ref="chevron-down"
                          style="solid"
                          class="w-6 mx-1 text-gray-400 -mt-7 group-hover:text-gray-500 xl:mt-0"

                        />
                        </div>
                        <div class="max-w-sm space-y-2 text-left">
                          <x-hub::tooltip :text="$line->description" :left="true">
                            <p class="text-sm font-bold leading-tight text-gray-800 truncate">
                              {{ $line->description }}
                            </p>
                          </x-hub::tooltip>


                          <div class="flex text-xs font-medium text-gray-600">
                            <p>{{ $line->identifier }}</p>

                            @if($line->purchasable->getOptions()->count())
                              <dl class="flex before:content-['|'] before:mx-3 before:text-gray-200 space-x-3">
                                @foreach($line->purchasable->getOptions() as $option)
                                <div class="flex gap-0.5">
                                  <dt>{{ $option }}</dt>
                                </div>
                                @endforeach

                                {{-- <div class="flex gap-0.5 before:content-['/'] before:mx-1.5 before:text-gray-200">
                                  <dt>Color:</dt>
                                  <dd>Black</dd>
                                </div> --}}
                              </dl>
                            @endif
                          </div>
                        </div>
                      </button>
                    </div>
                  </div>
                </div>

                <div
                  class="pl-[calc(8rem_-_10px)] text-gray-700"
                  x-show="showDetails"
                >
                  <div class="pt-4 mt-4 space-y-4 border-t border-gray-200">
                    <article class="text-sm">
                      <p>
                        <strong>Notes:</strong>

                        {{ $line->notes }}
                      </p>
                    </article>

                    <div class="overflow-hidden overflow-x-auto border border-gray-200 rounded">
                      <table class="min-w-full text-xs divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                          <tr class="divide-x divide-gray-200">
                            <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                              Unit Price
                            </td>

                            <td class="p-2 text-gray-700 whitespace-nowrap">
                              {{ $line->unit_price->formatted }} / {{ $line->unit_quantity }}
                            </td>
                          </tr>

                          <tr class="divide-x divide-gray-200">
                            <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                              Quantity
                            </td>

                            <td class="p-2 text-gray-700 whitespace-nowrap">
                              {{ $line->quantity }}
                            </td>
                          </tr>

                          <tr class="divide-x divide-gray-200">
                            <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                              Sub Total
                            </td>

                            <td class="p-2 text-gray-700 whitespace-nowrap">
                              {{ $line->sub_total->formatted }}
                            </td>
                          </tr>

                          <tr class="divide-x divide-gray-200">
                            <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                              Discount
                            </td>

                            <td class="p-2 text-gray-700 whitespace-nowrap">
                              {{ $line->discount_total->formatted }}
                            </td>
                          </tr>

                          @foreach($line->tax_breakdown as $tax)
                            <tr class="divide-x divide-gray-200">
                              <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                                {{ $tax->description }}
                              </td>

                              <td class="p-2 text-gray-700 whitespace-nowrap">
                                {{ $tax->total->formatted }}
                              </td>
                            </tr>
                          @endforeach

                          <tr class="divide-x divide-gray-200">
                            <td class="p-2 font-medium text-gray-900 whitespace-nowrap">
                              Total
                            </td>

                            <td class="p-2 text-gray-700 whitespace-nowrap">
                              {{ $line->total->formatted }}
                            </td>
                          </tr>

                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </li>
            @endforeach
          </ul>
        </div>

        @if ($this->physicalLines->count() > $maxLines)
          <div class="flex justify-end">
            <button
              class="flex-shrink-0 px-5 py-3 text-xs font-bold text-gray-700 bg-gray-100 border border-transparent rounded-md hover:border-gray-100 hover:bg-gray-50"
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
      </div>

      <div class="mt-4">
        <header class="sr-only">
          Transactions
        </header>

        <ul class="space-y-4">
          @foreach ($order->transactions as $transaction)
            <li class="flex items-center justify-between p-4 text-sm bg-white border rounded-lg shadow-sm">
              <div class="flex items-center gap-6">
                <strong
                  class="px-4 py-2 text-xs font-bold uppercase border border-current rounded-lg text-emerald-700 bg-emerald-100"
                >
                  {{ $transaction->status }}
                </strong>

                <div>
                  <img
                    class="object-contain w-12 h-auto"
                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/1599px-Visa_Inc._logo.svg.png?20170118154621"
                    alt="{{ $transaction->card_type }}"
                  >
                </div>

                <p class="text-sm text-gray-600">
                  <span class="inline-block -translate-y-px">
                    &lowast;&lowast;&lowast;&lowast; &lowast;&lowast;&lowast;&lowast; &lowast;&lowast;&lowast;&lowast;
                  </span>

                  <span class="font-medium">
                    {{ (string) $transaction->last_four }}
                  </span>
                </p>
              </div>

              <strong class="text-sm text-gray-900">
                {{ $transaction->amount->formatted }}
              </strong>
            </li>
          @endforeach
        </ul>
      </div>

      <div class="mt-4">
        <header class="mt-6 font-medium">
          Timeline
        </header>

        <div class="flex items-center mt-4">
          <div class="flex-shrink-0">
            @livewire('hub.components.avatar')
          </div>

          <form class="relative w-full ml-4" wire:submit.prevent="addComment">
            <input
              class="w-full pl-4 pr-32 border border-gray-200 rounded-lg h-[58px] sm:text-sm form-text"
              type="text"
              placeholder="Add a comment"
              wire:model.defer="comment"
              required
            >

            <button
              class="absolute h-[42px] text-xs font-bold leading-[42px] text-gray-700 bg-gray-100 border border-transparent rounded-md hover:border-gray-100 hover:bg-gray-50 w-28 top-2 right-2"
              type="submit"
            >
              <div wire:loading.remove wire:target="addComment">
                Add Comment
              </div>
              <div wire:loading wire:target="addComment">
                <x-hub::icon ref="refresh" style="solid" class="inline-block rotate-180 animate-spin" />
              </div>

            </button>
          </form>
        </div>

        <div class="relative pt-8">
          <span class="absolute inset-y-0 left-5 w-[2px] bg-gray-200"></span>

          <div class="flow-root">
            <ul
              class="-my-8 divide-y-2 divide-gray-200"
              role="list"
            >
              @foreach($this->activityLog as $log)
                <li class="relative py-8 ml-5">
                  <p class="ml-8 font-bold text-gray-900">
                    {{ $log['date']->format('F jS, Y') }}
                  </p>

                  <ul class="mt-4 space-y-6">
                    @foreach($log['items'] as $item)
                      <x-hub::activity-log.order-activity
                        :activity="$item"
                      />
                    @endforeach
                  </ul>
                </li>
              @endforeach
              {{-- @for ($i = 0; $i < 3; $i++)
                <li class="relative py-8 ml-5">
                  <p class="ml-8 font-bold text-gray-900">
                    October 4th, 2021
                  </p>

                  <ul class="mt-4 space-y-6">
                    <li class="relative pl-8">
                      <span
                        class="absolute w-4 h-4 bg-gray-300 rounded-full top-[2px] -left-[calc(0.5rem_-_1px)] ring-4 ring-gray-200"
                      >
                      </span>

                      <div class="flex justify-between">
                        <p class="text-sm font-medium text-gray-700">
                          Order confirmation email was sent to Alec Ritson (alec@neondigital.co.uk)
                        </p>

                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
                          12:40pm GMT
                        </time>
                      </div>

                      <div class="flex gap-4 mt-2">
                        <button
                          class="flex-shrink-0 px-4 py-2 text-xs font-bold text-gray-700 border rounded bg-gray-50 hover:bg-white"
                          type="button"
                        >
                          Resend Email
                        </button>

                        <button
                          class="flex-shrink-0 px-4 py-2 text-xs font-bold text-gray-700 border rounded bg-gray-50 hover:bg-white"
                          type="button"
                        >
                          View Email
                        </button>
                      </div>
                    </li>

                    <li class="relative pl-8">
                      <span
                        class="absolute w-4 h-4 bg-emerald-500 rounded-full top-[2px] -left-[calc(0.5rem_-_1px)] ring-4 ring-emerald-100"
                      >
                      </span>

                      <div class="flex justify-between">
                        <p class="text-sm font-medium text-gray-700">
                          A payment of $186.00 was made.
                        </p>

                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
                          12:37pm GMT
                        </time>
                      </div>
                    </li>

                    <li class="relative pl-8">
                      <span
                        class="absolute w-4 h-4 bg-blue-500 rounded-full top-[2px] -left-[calc(0.5rem_-_1px)] ring-4 ring-blue-100"
                      >
                      </span>

                      <div class="flex justify-between">
                        <p class="text-sm font-medium text-gray-700">
                          Order was created and marked pending.
                        </p>

                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
                          12:30pm GMT
                        </time>
                      </div>
                    </li>
                  </ul>
                </li>
              @endfor --}}
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-4 md:sticky md:top-4">
      <header class="flex items-center justify-between">
        @if($order->customer)
        <strong class="text-gray-700 truncate">
            {{ $order->customer->first_name }}
            @if ($order->customer->last_name)
              {{ $order->customer->last_name }}
            @endif
        </strong>

        <a
          class="flex-shrink-0 px-4 py-2 ml-4 text-xs font-bold text-gray-700 border rounded bg-gray-50 hover:bg-white"
          href="{{ route('hub.customers.show', $order->customer) }}"
        >
          View User
        </a>
        @endif
      </header>

      <section class="bg-white rounded-lg shadow">
        <dl class="text-sm text-gray-600">
          <div class="grid items-center grid-cols-2 gap-2 px-4 py-3 border-b">
            <dt class="font-medium text-gray-500">Status</dt>
            <dd class="text-right"><x-hub::orders.status :status="$order->status" /></dd>
          </div>

          <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
            <dt class="font-medium text-gray-500">Reference</dt>
            <dd class="text-right">{{ $order->reference }}</dd>
          </div>

          <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
            <dt class="font-medium text-gray-500">Customer Reference</dt>
            <dd class="text-right">{{ $order->customer_reference }}</dd>
          </div>

          <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
            <dt class="font-medium text-gray-500">Channel</dt>
            <dd class="text-right">{{ $order->channel->name }}</dd>
          </div>

          @if(!$order->placed_at)
            <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
              <dt class="font-medium text-gray-500">Date Created</dt>
              <dd class="text-right">{{ $order->created_at->format('Y-m-d h:ma') }}</dd>
            </div>
          @endif

          <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
            <dt class="font-medium text-gray-500">Date Placed</dt>
            <dd class="text-right">{{ $order->placed_at->format('Y-m-d h:ma') }}</dd>
          </div>
        </dl>
      </section>

      <section class="p-4 bg-white rounded-lg shadow">
        <header class="flex items-center justify-between">
          <strong class="text-gray-700">
            Shipping Address
          </strong>

          @if($this->shippingAddress)
            <button
              class="px-4 py-2 text-xs font-bold text-gray-700 bg-gray-100 border border-transparent rounded hover:border-gray-100 hover:bg-gray-50"
              type="button"
              wire:click.prevent="$set('showShippingAddressEdit', true)"
            >
              Edit
            </button>
          @endif
        </header>

        @if($shippingAddress)
        <address class="mt-4 text-sm not-italic text-gray-600">
          @if($shippingAddress->company_name)
            {{ $shippingAddress->company_name }} <br>
          @endif
          {{ $shippingAddress->fullName }} <br>
          {{ $shippingAddress->line_one }} <br>

          @if ($shippingAddress->line_two)
            {{ $this->shippingAddress->line_two }} <br>
          @endif

          @if ($shippingAddress->line_three)
            {{ $shippingAddress->line_three }} <br>
          @endif

          @if ($shippingAddress->city)
            {{ $shippingAddress->city }} <br>
          @endif

          @if ($shippingAddress->state)
            {{ $shippingAddress->state }} <br>
          @endif

          {{ $shippingAddress->postcode }} <br>

          {{ $shippingAddress->country->name }}
        </address>
        @else
          <span class="text-sm text-gray-600">No shipping address set</span>
        @endif
      </section>

      <section class="p-4 bg-white rounded-lg shadow">
        <header>
          <strong class="text-gray-700">
            Billing Address
          </strong>
        </header>

        <div class="mt-4 text-sm">
          @if (!$this->shippingEqualsBilling)
            <address class="not-italic text-gray-600">
              @if($this->billing->company_name)
                {{ $this->billing->company_name }} <br>
              @endif

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

              {{ $this->billing->postcode }} <br>

              {{ $this->billing->country->name }}
            </address>
          @else
            <p class="text-gray-500">
              Same as shipping address
            </p>
          @endif
        </div>
      </section>

      <section class="p-4 bg-white rounded-lg shadow">
        <header>
          <strong class="text-gray-700">
            Additional Details
          </strong>
        </header>

        <dl class="mt-4 space-y-2 text-sm text-gray-600">
          <div class="grid grid-cols-3 gap-2">
            <dt class="font-medium text-gray-700">
              Metafield:
            </dt>

            <dd class="col-span-2">
              Lorem ipsum dolor sit amet.
            </dd>
          </div>
        </dl>
      </section>
    </div>

    <x-hub::modal wire:model="showRefund">
      <div class="p-4">
        @livewire('hub.components.orders.refund', [
          'order' => $this->order,
          'amount' => $this->refundAmount / 100,
        ])
      </div>
    </x-hub::modal>

    <x-hub::slideover wire:model="showShippingAddressEdit">
      @include('adminhub::partials.forms.address', [
        'model' => 'shippingAddress',
      ])

      <x-slot name="footer">
        <x-hub::button wire:click.prevent="$set('showShippingAddressEdit', false)" theme="gray">
          {{ __('adminhub::global.cancel') }}
        </x-hub::button>
        <x-hub::button wire:click.prevent="saveShippingAddress">
          {{ __('adminhub::components.orders.show.save_shipping_btn') }}
        </x-hub::button>
      </x-slot>
    </x-hub::slideover>

</section>
