<div>
    <span @class([
        'lt-text-xs lt-inline-block lt-py-1 lt-px-2 lt-rounded',
        'lt-text-blue-600 lt-bg-blue-50' => !@empty($info),
        'lt-text-green-600 lt-bg-green-50' => !@empty($success),
        'lt-text-yellow-600 lt-bg-yellow-50' => !@empty(
            $warning
        ),
        'lt-text-red-600 lt-bg-red-50' => !@empty($danger),
    ])>
        {{ $value }}
    </span>
</div>
