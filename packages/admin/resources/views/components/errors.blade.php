<div> 
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