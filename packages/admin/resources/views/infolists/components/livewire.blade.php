<div>
    @livewire(
        $getContent(),
        [
            'record' => $getRecord(),
        ],
        key('lunar_livewire_'.$getContentName())
    )
</div>