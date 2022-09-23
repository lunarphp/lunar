@php
$id = $id ?? md5($attributes->wire('model'));

switch ($maxWidth ?? '2xl') {
    case 'sm':
        $maxWidth = 'sm:max-w-sm';
        break;
    case 'md':
        $maxWidth = 'sm:max-w-md';
        break;
    case 'lg':
        $maxWidth = 'sm:max-w-lg';
        break;
    case 'xl':
        $maxWidth = 'sm:max-w-xl';
        break;
    case '2xl':
    default:
        $maxWidth = 'sm:max-w-2xl';
        break;
}
@endphp

<div x-data="{ show: @entangle($attributes->wire('model')) }"
     x-cloak
     x-show="show"
     x-on:keydown.escape.window="show = false"
     id="{{ $id }}"
     class="fixed inset-x-0 top-0 px-4 pt-6 z-75 sm:px-0 sm:flex sm:items-top sm:justify-center">
    <div x-cloak
         x-show="show"
         class="fixed inset-0 transition-all"
         x-transition.opacity>
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div x-cloak
         x-show="show"
         x-trap.noscroll="show"
         x-on:click.away="show = false"
         x-init="$watch('show', (isShown) => isShown && $focus.first())"
         x-transition
         class="bg-white rounded-lg shadow-xl transition-all sm:w-full z-75 {{ $maxWidth }}">
        {{ $slot }}
    </div>
</div>
