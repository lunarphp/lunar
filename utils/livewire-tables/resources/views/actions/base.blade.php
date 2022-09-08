<div>
    @if ($url)
        <a role="menuitem"
           href="{{ call_user_func($url, $record) }}"
           class="lt-text-xs lt-font-medium lt-rounded-md lt-text-gray-600 lt-block lt-px-3 lt-py-2 hover:lt-bg-gray-50">
            <span class="lt-capitalize">
                {{ $label }}
            </span>
        </a>
    @else
        <p class="text-sm font-medium lt-text-gray-600 lt-px-3 lt-py-2">
            <span class="lt-capitalize">
                {{ $label }}
            </span>
        </p>
    @endif
</div>
