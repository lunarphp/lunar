<ul class="space-y-4">
  @foreach ($this->transactions as $transaction)
    <li class="
      text-sm bg-white border
      @if($transaction->type == 'refund') border-orange-300 @endif
      @if($transaction->type == 'intent') border-indigo-300 @endif
      @if($transaction->type == 'capture') border-green-300 @endif
      rounded-lg shadow-sm
    ">
      <div class="flex items-center justify-between p-4">
        <div class="flex items-center gap-6">
          <div>
              <strong class="text-xs text-gray-500">
                {{ $transaction->status }}
              </strong>
          </div>

          <div>
            <svg viewBox="0 0 50 50" class="w-10">
                <use xlink:href="#{{ $transaction->card_type }}"></use>
            </svg>
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

        <strong class="text-sm @if($transaction->type == 'refund') text-orange-500 @else text-gray-900 @endif">
          @if($transaction->type == 'refund')-@endif{{ $transaction->amount->formatted }}
        </strong>

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