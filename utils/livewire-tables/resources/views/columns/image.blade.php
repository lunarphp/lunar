<div>
    @if ($url)
        <a href="{{ call_user_func($url, $record) }}">
    @endif
    @if ($thumbnail = $value)
        <img class="lt-rounded lt-w-10 lt-h-10"
             src="{{ $thumbnail }}"
             loading="lazy" />
    @else
        <x-hub::icon ref="photograph"
                     class="lt-w-10 lt-h-10 lt-text-gray-300" />
    @endif

    @if ($url)
        </a>
    @endif
</div>
