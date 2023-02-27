<div>
    @if ($url)
        <a href="{{ call_user_func($url, $record) }}">
    @endif

    @if ($thumbnail = $value)
        <x-hub::thumbnail :src="$thumbnail" />
    @else
        <x-hub::icon ref="photograph"
                     class="lt-w-16 lt-h-16 lt-text-gray-300" />
    @endif

    @if ($url)
        </a>
    @endif
</div>
