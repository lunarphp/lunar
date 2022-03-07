<ul class="space-y-4">
  @foreach ($order->transactions as $transaction)
    <li class="flex items-center relative justify-between p-4 text-sm bg-white border @if($transaction->refund) border-orange-300 @endif rounded-lg shadow-sm">
      <div class="flex items-center gap-6">
        <div>
          @if($transaction->refund)
            <strong
              class="px-2 py-1 text-xs font-bold text-orange-600 bg-orange-100 border border-current rounded-lg"
            >
              Refunded
            </strong>
          @else
            <strong
              class="px-2 py-1 text-xs font-bold border border-current rounded-lg text-emerald-600 bg-emerald-100"
            >
              {{ $transaction->status }}
            </strong>
          @endif
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

      <strong class="text-sm @if($transaction->refund) text-orange-500 @else text-gray-900 @endif">
        @if($transaction->refund)-@endif{{ $transaction->amount->formatted }}
      </strong>
    </li>
  @endforeach
</ul>