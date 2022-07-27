<dl class="text-sm text-gray-600">
  <div class="grid items-center grid-cols-2 gap-2 px-4 py-3 border-b">
    <dt class="font-medium text-gray-500">{{ __('adminhub::partials.orders.details.status') }}</dt>
    <dd class="text-right"><x-hub::orders.status :status="$order->status" /></dd>
  </div>

  <div
    x-data="{
      copy(text) {
        if (window.clipboardData && window.clipboardData.setData) {
            $wire.call('notify', '{{ __('adminhub::notifications.clipboard.copied') }}')
            // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
            return window.clipboardData.setData('Text', text);
        } else if (document.queryCommandSupported && document.queryCommandSupported('copy')) {
            var textarea = document.createElement('textarea');
            textarea.textContent = text;
            textarea.style.position = 'fixed';  // Prevent scrolling to bottom of page in Microsoft Edge.
            document.body.appendChild(textarea);
            textarea.select();
            try {
              $wire.call('notify', '{{ __('adminhub::notifications.clipboard.copied') }}')
              return document.execCommand('copy');  // Security exception may be thrown by some browsers.
            }
            catch (ex) {
              $wire.call('notify', '{{ __('adminhub::notifications.clipboard.failed_copy') }}')
            }
            finally {
                document.body.removeChild(textarea);
            }
        }
      }
    }">

  <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
    <dt class="font-medium text-gray-500">{{ __('adminhub::partials.orders.details.reference') }}</dt>
    <dd class="text-right">
      <div class="flex items-center justify-end space-x-4">
        <div>
          {{ $order->reference }}
        </div>
        <button type="button" x-on:click="copy('{{ $order->reference }}')">
          <x-hub::icon ref="clipboard" class="w-4" />
        </button>
      </div>
    </dd>
  </div>

  <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
    <dt class="font-medium text-gray-500">{{ __('adminhub::partials.orders.details.customer_reference') }}</dt>
    <dd class="text-right">
      <div class="flex items-center justify-end space-x-4">
        <div>
          {{ $order->customer_reference }}
        </div>
        @if($order->customer_reference)
        <button type="button" x-on:click="copy('{{ $order->customer_reference }}')">
          <x-hub::icon ref="clipboard" class="w-4" />
        </button>
        @endif
      </div>
    </dd>
  </div>

  </div>

  <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
    <dt class="font-medium text-gray-500">{{ __('adminhub::partials.orders.details.channel') }}</dt>
    <dd class="text-right">{{ $order->channel->name }}</dd>
  </div>

  @if(!$order->placed_at)
    <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
      <dt class="font-medium text-gray-500">{{ __('adminhub::partials.orders.details.date_created') }}</dt>
      <dd class="text-right">{{ $order->created_at->format('Y-m-d h:ia') }}</dd>
    </div>
  @endif

  <div class="grid grid-cols-2 gap-2 px-4 py-3 border-b">
    <dt class="font-medium text-gray-500">{{ __('adminhub::partials.orders.details.date_placed') }}</dt>
    <dd class="text-right">
      @if($order->placed_at)
        {{ $order->placed_at->format('Y-m-d h:ia') }}
      @else
        -
      @endif
    </dd>
  </div>
</dl>
