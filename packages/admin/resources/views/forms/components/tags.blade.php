<div>
    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
            {{ $getLabel() }}
        </span>
    </label>
    @livewire('lunar.admin.livewire.components.tags', [
        'taggable' => $getRecord()
    ])
</div>
