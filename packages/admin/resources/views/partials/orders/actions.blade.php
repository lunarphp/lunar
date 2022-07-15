@if($this->transactions->count())
<button
  class="inline-flex items-center px-4 py-2 font-bold transition border border-transparent border-gray-200 rounded hover:bg-white bg-gray-50 hover:border-gray-200"
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
@endif

@if($this->requiresCapture)
  <button
    class="inline-flex items-center px-4 py-2 font-bold transition border border-transparent border-gray-200 rounded hover:bg-white bg-gray-50 hover:border-gray-200"
    type="button"
    wire:click.prevent="$set('showCapture', true)"
  >
    <x-hub::icon
      ref="credit-card"
      style="solid"
      class="w-4 mr-2"
    />
    {{ __('adminhub::components.orders.show.capture_payment_btn') }}
  </button>
@endif

@livewire('hub.components.orders.status', [
  'order' => $this->order,
])

<div
  class="relative flex justify-end flex-1"
  x-data="{ showMenu: false }"
>
  <x-hub::menu handle="order_actions">
    @if($component->items->count())
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
      class="absolute right-0 z-50 w-screen max-w-[200px] mt-2 text-sm bg-white border rounded-lg shadow-lg top-full overflow-hidden"
      role="menu"
      x-on:click.away="showMenu = false"
      x-show="showMenu"
      x-transition
      x-cloak
    >

        @foreach($component->items as $item)
          @if ($item->component)
            @livewire($item->component, [
              'order' => $this->order,
            ])
          @else
            <x-hub::dropdown.link :route="route($item->route, $this->order->id)">
              {{ $item->name }}
            </x-hub::dropdown.link>
          @endif
        @endforeach

    </div>
    @endif
  </x-hub::menu>
</div>
