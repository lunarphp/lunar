<div>
    <label for="{{ $for }}"
           class="flex items-center text-sm font-medium text-gray-700">
        <span class="block">{{ $labelPrefix ?? null }}</span>

        <span class="block">
            {{ $label }}

            @if ($required)
                <sup class="text-xs text-red-600">&#42;</sup>
            @endif
        </span>
    </label>

    <div class="relative mt-1">
        {{ $slot }}
    </div>

    @if ($instructions)
        <p class="mt-2 text-sm text-gray-500">{{ $instructions }}</p>
    @endif

    @if ($error)
        <div class="flex items-center gap-1 mt-2">
            @if ($errorIcon)
                <x-hub::icon ref="exclamation-circle"
                             class="w-5 h-5 text-red-500" />
            @endif

            <p class="text-sm text-red-600">{{ $error }}</p>
        </div>
    @endif

    @if (count($errors))
        <div class="space-y-1">
            @foreach ($errors as $error)
                @if (is_array($error))
                    @foreach ($error as $text)
                        <p class="text-sm text-red-600">{{ $text }}</p>
                    @endforeach
                @else
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endif
            @endforeach
        </div>
    @endif
</div>
