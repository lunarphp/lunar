<section class="space-y-6">
    <x-filament::section.heading>
        {{ $getLabel() }}
    </x-filament::section.heading>


    @livewire(\Lunar\Admin\Livewire\Components\ActivityLogFeed::class, [
        'subject' => $getRecord()
    ])
</section>
