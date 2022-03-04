<button
  class="inline-flex border hover:bg-white bg-gray-50 border-gray-200 items-center px-4 py-2 font-bold transition border border-transparent rounded hover:bg-white hover:border-gray-200"
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
  class="inline-flex hover:bg-white bg-gray-50 border border-gray-200 items-center px-4 py-2 font-bold transition border border-transparent rounded hover:bg-white hover:border-gray-200"
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
    class="absolute right-0 z-50 w-screen max-w-[200px] mt-2 text-sm bg-white border rounded-lg shadow-lg top-full overflow-hidden"
    role="menu"
    x-on:click.away="showMenu = false"
    x-show="showMenu"
    x-transition
    x-cloak
  >
    <x-hub::menu handle="order_actions">
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
    </x-hub::menu>
  </div>
</div>