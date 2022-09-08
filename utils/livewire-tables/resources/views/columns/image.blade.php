<div>
    @if($url)
        <a href="{{ call_user_func($url, $record) }}">
    @endif
    @if ($thumbnail = $value)
        <img class="rounded w-10"
             src="{{ $thumbnail }}"
             loading="lazy" />
    @else
        <x-hub::icon ref="photograph" class="w-10 h-10 text-gray-300" />
    @endif
    @if($url)
        </a>
    @endif
</div>
