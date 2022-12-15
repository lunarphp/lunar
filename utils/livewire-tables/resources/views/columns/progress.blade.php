@php
    $background = match ($color) {
        'primary' => 'lt-bg-blue-600',
        'secondary' => 'lt-bg-gray-600',
        'danger' => 'lt-bg-red-600',
        'success' => 'lt-bg-green-600',
        'warning' => 'lt-bg-yellow-600',
        default => $color
    };
@endphp
<div>
    <div class="flex items-center space-x-4 px-4">
        <div class="min-h w-full bg-gray-200 rounded-full dark:bg-gray-700">
            <div @class([
                'px-4 py-0.5 text-xs text-white text-center leading-none rounded-full',
                $background,
            ]) style="width: {{ $progress }}%"> {{ $label }}</div>
        </div>
    </div>
</div>
