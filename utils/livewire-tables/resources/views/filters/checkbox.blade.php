<div>
    <label for="{{ $field }}"
           class="lt-block lt-text-xs lt-font-medium lt-text-gray-700 lt-capitalize">
        {{ $heading }}
    </label>

    <input id="{{ $field }}"
           wire:model="filters.{{ $field }}"
           type="checkbox"
           class="lt-mt-1 lt-border-gray-200 lt-w-5 lt-rounded lt-h-5 focus:lt-outline-none focus:lt-ring focus:lt-ring-sky-100 focus:lt-border-sky-300 lt-form-checkbox" />
</div>
