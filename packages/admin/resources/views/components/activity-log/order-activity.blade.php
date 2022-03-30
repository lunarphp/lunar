<li class="relative pl-8">

  <div class="absolute top-[2px] -left-[calc(0.5rem_-_1px)]">
    @if($activity->causer)
      <x-hub::gravatar :email="$activity->causer->email" class="w-5 h-5 rounded-full" />
    @else
      <span
        class="absolute w-4 h-4
          @if($activity->description == 'created')
            bg-blue-500 ring-blue-100
          @elseif($activity->description == 'status-update')
            bg-purple-500 ring-purple-100
          @elseif($activity->description == 'updated')
            bg-teal-500 ring-teal-100
          @else
            bg-gray-300 ring-gray-200
          @endif
          rounded-full ring-4"
      >
      </span>
    @endif
  </div>

  <div class="flex justify-between">
    <div>
      <div class="text-xs font-medium text-gray-500">
        @if(!$activity->causer)
          {{ __('adminhub::components.activity-log.system') }}
        @else
          {{ $activity->causer->fullName ?: $activity->causer->name }}
        @endif
      </div>
      <p class="mt-2 text-sm font-medium text-gray-700">
        @if($activity->event == 'status-update')
          <div class="flex items-center text-sm font-medium text-gray-700">
            {{ __('adminhub::components.activity-log.orders.status_change') }}
            <div class="flex items-center ml-2">
              <strong><x-hub::orders.status :status="$activity->getExtraProperty('previous')" /></strong>
              <x-hub::icon ref="chevron-right" style="solid" class="w-4 mx-1" />
              <strong><x-hub::orders.status :status="$activity->getExtraProperty('new')" /></strong>
            </div>
          </div>
        @elseif($activity->event == 'created')
          {{ __('adminhub::components.activity-log.orders.order_created') }}
        @elseif($activity->event == 'comment')
          {!! nl2br($activity->getExtraProperty('content')) !!}
        @elseif($activity->event == 'capture')
          {{ __('adminhub::components.activity-log.orders.capture', [
            'amount' => price($activity->getExtraProperty('amount'), $this->order->currency)->formatted,
            'last_four' => $activity->getExtraProperty('last_four'),
          ]) }}
        @elseif($activity->event == 'intent')
          {{ __('adminhub::components.activity-log.orders.authorized', [
            'amount' => price($activity->getExtraProperty('amount'), $this->order->currency)->formatted,
            'last_four' => $activity->getExtraProperty('last_four'),
          ]) }}
        @elseif($activity->event == 'refund')
          {{ __('adminhub::components.activity-log.orders.refund', [
            'amount' => price($activity->getExtraProperty('amount'), $this->order->currency)->formatted,
            'last_four' => $activity->getExtraProperty('last_four'),
          ]) }}
          @if($notes = $activity->getExtraProperty('notes'))
            <p class="mt-2 text-sm text-gray-600">{{ nl2br($notes) }}</p>
          @endif
        @endif
      </p>
    </div>

    <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
      {{ $activity->created_at->format('h:ia')}}
    </time>
  </div>
</li>