<x-hub::modal :id="$id"
              :maxWidth="$maxWidth"
              {{ $attributes }}>
    @if ($form)
        <form wire:submit.prevent="{{ $form }}">
    @endif

    <div class="px-6 py-4">
        <div class="text-lg">
            {{ $title }}
        </div>

        <div class="mt-4">
            {{ $content }}
        </div>
    </div>

    <div class="px-6 py-4 text-right bg-gray-100 rounded-b">
        {{ $footer }}
    </div>

    @if ($form)
        </form>
    @endif
</x-hub::modal>
