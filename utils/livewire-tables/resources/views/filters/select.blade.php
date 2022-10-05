<div>
    <label for="{{ $field }}"
           class="lt-block lt-text-xs lt-font-medium lt-text-gray-700 lt-capitalize">
        {{ $heading }}
    </label>

    <select id="{{ $field }}"
            wire:model="filters.{{ $field }}"
            class="lt-mt-1 lt-text-sm lt-text-gray-700 lt-border-gray-200 lt-rounded-md focus:lt-outline-none focus:lt-ring focus:lt-ring-blue-100 focus:lt-border-blue-300 lt-form-select">
        @foreach ($options as $option)
            <option value="{{ $option['value'] }}">
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
</div>
