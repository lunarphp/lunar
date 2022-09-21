@props([
    'size' => 'default',
    'theme' => 'default',
])

<button
        {{ $attributes->merge([
                'class' => 'lt-button',
            ])->class([
                'lt-px-3 lt-py-2 lt-text-sm' => $size == 'default',
                'lt-px-3 lt-py-2 lt-text-xs' => $size == 'sm',
                'lt-px-2.5 lt-py-2 lt-text-xs' => $size == 'xs',
                'lt-button-gray' => $theme == 'default',
                'lt-button-success' => $theme == 'success',
                'lt-button-primary' => $theme == 'primary',
                'lt-button-danger' => $theme == 'danger',
            ]) }}>
    {{ $slot }}
</button>
