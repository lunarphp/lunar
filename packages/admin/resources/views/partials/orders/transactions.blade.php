<ul class="space-y-4">
  @foreach ($this->transactions as $transaction)
    <li
      @class([
        'text-sm rounded-lg shadow-sm border shadow' => true,
        'border-orange-300' => $transaction->type == 'refund',
        'border-indigo-300' => $transaction->type == 'intent',
        'border-green-300' => $transaction->type == 'capture',
      ])
    >
      <div class="p-2 space-y-2">
        <div class="px-4 py-2 rounded text-gray-500 text-xs bg-white shadow-sm">
          <span>{{ $transaction->driver }}</span> //
          <span>{{ $transaction->reference }}</span>
        </div>
        <div class="flex items-center justify-between p-4 bg-white rounded shadow-sm">
          <div class="flex items-center gap-6">
            <div>
                <strong class="text-xs text-gray-500">
                  {{ $transaction->status }}
                </strong>
            </div>

            <div>
              <svg viewBox="0 0 50 50" class="w-10">
                <use xlink:href="#{{ strtolower($transaction->card_type) }}"></use>
              </svg>
            </div>

            @if($transaction->last_four)
              <p class="text-sm text-gray-600">
                <span class="inline-block -translate-y-px">
                  &lowast;&lowast;&lowast;&lowast; &lowast;&lowast;&lowast;&lowast; &lowast;&lowast;&lowast;&lowast;
                </span>

                <span class="font-medium">
                  {{ (string) $transaction->last_four }}
                </span>
              </p>
            @endif
          </div>

          <strong class="text-sm @if($transaction->type == 'refund') text-orange-500 @else text-gray-900 @endif">
            @if($transaction->type == 'refund')-@endif{{ $transaction->amount->formatted }}
          </strong>
        </div>

        <div class="px-4 py-2 bg-white shadow-sm rounded flex items-center justify-between">
          <div class="text-xs flex items-center">
            <x-hub::icon ref="clock" class="w-4 text-gray-400 mr-1" />
            <span class="text-gray-600">{{ $transaction->created_at->format('jS F Y h:ma') }}</span>
          </div>

          @if($threeD = $transaction->meta?->threedSecure ?? null)
            <div class="flex space-x-2">
              <div
                @class([
                  'rounded flex items-center py-1 px-2 rounded-md text-xs' => true,
                  'text-blue-600 bg-blue-50' => $threeD->address ?? false,
                  'text-gray-500 bg-gray-50' => !($threeD->address ?? false)
                ])
              >
                <x-hub::icon :ref="$threeD->postalCode ? 'check' : 'x'" style="solid" class="w-3" />
                Address
              </div>

              <div
                @class([
                  'rounded flex items-center py-1 px-2 rounded-md text-xs' => true,
                  'text-blue-600 bg-blue-50' => $threeD->postalCode ?? false,
                  'text-gray-500 bg-gray-50' => !($threeD->postalCode ?? false)
                ])
              >
                <x-hub::icon :ref="$threeD->postalCode ? 'check' : 'x'" style="solid" class="w-3" />
                Postal Code
              </div>


              <div
                @class([
                  'rounded flex items-center py-1 px-2 rounded-md text-xs' => true,
                  'text-blue-600 bg-blue-50' => $threeD->securityCode ?? false,
                  'text-gray-500 bg-gray-50' => !($threeD->securityCode ?? false)
                ])
              >
                <x-hub::icon :ref="$threeD->securityCode ? 'check' : 'x'" style="solid" class="w-3" />
                Security Code
              </div>

            </div>
          @endif
        </div>

        @if($transaction->notes)
          <div class="px-4 py-2 bg-white shadow-sm flex items-center rounded">
            <x-hub::icon ref="chat" class="w-4 text-gray-400 mr-2" />
            <p class="text-sm">{{ $transaction->notes }}</p>
          </div>
        @endif
      </div>

      <div class="
        bottom-0 left-0 block w-full text-center rounded-b-lg border-t text-xs py-1
        @if($transaction->type == 'refund') bg-orange-50 border-orange-300 text-orange-500 @endif
        @if($transaction->type == 'intent') bg-indigo-50 border-indigo-300 text-indigo-500 @endif
        @if($transaction->type == 'capture') bg-green-50 border-green-300 text-green-600 @endif
      ">
        {{ __('adminhub::partials.orders.transactions.'.$transaction->type) }}
      </div>
    </li>
  @endforeach
</ul>
