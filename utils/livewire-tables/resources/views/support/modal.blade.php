@php
$id = $id ?? md5($attributes->wire('model'));

switch ($maxWidth ?? '2xl') {
    case 'sm':
        $maxWidth = 'sm:lt-max-w-sm';
        break;
    case 'md':
        $maxWidth = 'sm:lt-max-w-md';
        break;
    case 'lg':
        $maxWidth = 'sm:lt-max-w-lg';
        break;
    case 'xl':
        $maxWidth = 'sm:lt-max-w-xl';
        break;
    case '2xl':
    default:
        $maxWidth = 'sm:lt-max-w-2xl';
        break;
}
@endphp

<div x-data="{
    show: true,
    focusables() {
        let selector = 'a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'

        return [...$el.querySelectorAll(selector)].filter(el => !el.hasAttribute('disabled'))
    },
    firstFocusable() { return this.focusables()[0] },
    lastFocusable() { return this.focusables().slice(-1)[0] },
    nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
    prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
    nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
    prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1 },
    autofocus() { let focusable = $el.querySelector('[autofocus]'); if (focusable) focusable.focus() },
}"
     x-init="$watch('show', value => value && setTimeout(autofocus, 50))"
     x-on:close.stop="show = false"
     x-on:keydown.escape.window="show = false"
     x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
     x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
     x-show="show"
     id="{{ $id }}"
     class="lt-fixed lt-inset-x-0 lt-top-0 lt-px-4 lt-pt-6 lt-z-[75] sm:lt-px-0 sm:lt-flex sm:lt-items-top sm:lt-justify-center"
     style="display: none">
    <div x-show="show"
         class="lt-fixed lt-inset-0 lt-transition-all"
         x-on:click="show = false"
         x-transition:enter="lt-ease-out lt-duration-300"
         x-transition:enter-start="lt-opacity-0"
         x-transition:enter-end="lt-opacity-100"
         x-transition:leave="lt-ease-in lt-duration-200"
         x-transition:leave-start="lt-opacity-100"
         x-transition:leave-end="lt-opacity-0">
        <div class="lt-absolute lt-inset-0 lt-bg-gray-500/75"></div>
    </div>

    <div x-show="show"
         class="lt-bg-white lt-rounded-lg lt-shadow-xl lt-transition-all sm:lt-w-full lt-z-[75] {{ $maxWidth }}"
         x-transition:enter="lt-ease-out lt-duration-300"
         x-transition:enter-start="lt-opacity-0 translate-y-4 sm:lt-translate-y-0 sm:lt-scale-95"
         x-transition:enter-end="lt-opacity-100 translate-y-0 sm:lt-scale-100"
         x-transition:leave="lt-ease-in lt-duration-200"
         x-transition:leave-start="lt-opacity-100 translate-y-0 sm:lt-scale-100"
         x-transition:leave-end="lt-opacity-0 translate-y-4 sm:lt-translate-y-0 sm:lt-scale-95">
        {{ $slot }}
    </div>
</div>
