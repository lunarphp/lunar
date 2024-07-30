<section class="space-y-6">
    <x-filament::section.heading>
        {{ $getLabel() }}
    </x-filament::section.heading>


    @livewire('lunar.admin.livewire.components.activity-log-feed', [
        'subject' => $getRecord()
    ])
</section>
