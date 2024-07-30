<div>
    <div class="space-y-4">
        <div class="flex w-full justify-end">
            {{ $this->createRootCollectionAction }}
        </div>
        <div>
            <x-lunarpanel::collections.nested-set-tree :nodes="$nodes" group="parent" />
        </div>
    </div>
    <x-filament-actions::modals />
</div>
