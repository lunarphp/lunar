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
     x-init="$watch('show', value => value && setTimeout(autofocus, 50))"
     x-on:keydown.escape.window="show = false"
     x-cloak
     x-show="show"
     x-trap.inert.noscroll="show"
     id="{{ $id }}"
     class="fixed inset-x-0 top-0 px-4 pt-6 z-75 sm:px-0 sm:flex sm:items-top sm:justify-center">
    <div x-cloak
         x-show="show"
         class="fixed inset-0 transition-all"
         x-on:click="show = false"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div x-cloak
         x-show="show"
         class="bg-white rounded-lg shadow-xl transition-all sm:w-full z-75 {{ $maxWidth }}"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        {{ $slot }}
    </div>
</div>
