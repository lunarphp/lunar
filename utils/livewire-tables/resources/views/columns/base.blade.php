<div>
    @if ($url)
        <a href="{{ call_user_func($url, $record) }}"
           class="lt-text-blue-600 hover:lt-underline">
    @endif

    {{ $value }}

    @if ($url)
        </a>
    @endif
</div>
