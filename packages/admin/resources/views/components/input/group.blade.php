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

    <x-hub::errors :error="$error" :errors="$errors" />
</div>
