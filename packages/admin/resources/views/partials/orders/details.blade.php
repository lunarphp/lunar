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