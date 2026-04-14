<x-filament-widgets::widget>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            {{ $this->saveAction }}
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
