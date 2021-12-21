<div class="flex-col px-12 mx-auto space-y-4 max-w-7xl">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">Order</strong>
    <div>
      <x-hub::button type="button" wire:click="$set('updatingStatus', true)">Update Status</x-hub::button>
    </div>
  </div>

  <div class="grid grid-cols-6 gap-4">
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
                <td class="p-2 text-sm">{{ $tax->name }}</td>
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
  </x-hub::modal.dialog>
</div>
