@php
    $transaction = $getState();
@endphp

@once
    @php
        $renderPaymentIcons();
    @endphp
@endonce
<div
    @class([
        'text-sm rounded-lg shadow-md border dark:bg-gray-900',
        'text-gray-950 dark:text-white',
        match($transaction->type){
            'refund' => 'border-orange-300',
            'intent' => 'border-sky-300',
            'capture' => 'border-green-300',
        },
        '!border-red-500 bg-red-50' => !$transaction->success,
        'bg-gray-50' => $transaction->success,
    ])
>
    <div class="p-2 space-y-2">
        <div class="px-4 py-2 rounded text-xs bg-white dark:bg-gray-800 shadow text-gray-600 dark:text-gray-400 ring-1 ring-gray-100 dark:ring-gray-700">
            <span>{{ $transaction->driver }}</span> //
            <span>{{ $transaction->reference }}</span>
        </div>

        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded shadow ring-1 ring-gray-100 dark:ring-gray-700">
            <div class="flex items-center gap-6">
                <div>
                    <strong class="text-xs">
                        {{ $transaction->status }}
                    </strong>
                </div>

                <div>
                    <svg viewBox="0 0 50 50" class="w-10">
                        <use xlink:href="#{{ strtolower($transaction->card_type) }}"></use>
                    </svg>
                </div>

                @if($transaction->last_four)
                    <p class="text-sm">
                        <span class="inline-block -translate-y-px">
                            &lowast;&lowast;&lowast;&lowast; &lowast;&lowast;&lowast;&lowast; &lowast;&lowast;&lowast;&lowast;
                        </span>

                        <span class="font-medium">
                            {{ (string) $transaction->last_four }}
                        </span>
                    </p>
                @endif
            </div>

            <strong
                @class([
                    "text-sm",
                    'text-red-500' => !$transaction->success,
                    match($transaction->type){
                        'refund' => "text-orange-500",
                        default => "text-gray-900 dark:text-gray-100",
                    },
                ])
            >
                @if($transaction->type == 'refund')-@endif{{ $transaction->amount->formatted }}
            </strong>
        </div>

        <div class="px-4 py-2 bg-white dark:bg-gray-800 shadow rounded flex items-center justify-between text-gray-600 dark:text-gray-400 ring-1 ring-gray-100 dark:ring-gray-700">
            <div class="text-xs flex items-center gap-2">
                <div>
                    <x-filament::icon
                        icon="heroicon-o-clock"
                        class="w-4"
                    />
                </div>
                <span>{{ $transaction->created_at->format('jS F Y h:ia') }}</span>
            </div>

            <div class="flex space-x-2">
            @foreach($transaction->paymentChecks() as $check)
                    <x-filament::badge
                        :icon="$check->successful ? 'heroicon-m-check' : 'heroicon-m-x-mark'"
                        :color="$check->successful ?  \Filament\Support\Colors\Color::Sky : 'gray'"
                    >
                        {{ $check->label }}: {{ $check->message }}
                    </x-filament::badge>
            @endforeach
            </div>
        </div>

        @if($transaction->notes)
            <div class="px-4 py-2 bg-white dark:bg-gray-800 shadow flex items-center rounded gap-2 ring-1 ring-gray-100 dark:ring-gray-700">
                <div>
                    <x-filament::icon
                        icon="heroicon-o-chat-bubble-oval-left-ellipsis"
                        class="w-4"
                    />
                </div>

                <p class="text-sm">{{ $transaction->notes }}</p>
            </div>
        @endif
    </div>

    <div
        @class([
            "bottom-0 left-0 block w-full text-center rounded-b-lg border-t text-xs py-1",
            "!bg-red-50 !dark:bg-red-400/10 !border-red-300 !text-red-600 !dark:text-red-400" => !$transaction->success,
            match($transaction->type){
                'refund' => "bg-orange-50 dark:bg-orange-400/10 border-orange-300 text-orange-600 dark:text-orange-400",
                'intent' => "bg-sky-50 dark:bg-sky-400/10 border-sky-300 text-sky-600 dark:text-sky-400",
                'capture' => "bg-green-50 dark:bg-green-400/10 border-green-300 text-green-600 dark:text-green-400",
            },
        ])
    >
        @if(!$transaction->success)
            {{ __('lunarpanel::order.transactions.failed') }}
        @else
            {{ __('lunarpanel::order.transactions.'.$transaction->type) }}
        @endif
    </div>
</div>

