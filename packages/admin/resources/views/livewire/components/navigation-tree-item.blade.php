<div class="relative group" x-sortable-item="{{ $node['id'] }}">
    <div @class([
        'w-full flex justify-between',
        'dark:bg-gray-900 dark:border-white/10',
    ])>
        <div class="flex w-full space-x-2">
            <div @class([
                'flex items-center text-gray-400 hover:text-gray-500',
                'cursor-grab',
            ]) x-sortable-handle>
                <x-filament::icon alias="lunar::reorder" class="w-5 h-5" />
            </div>

            <div class="flex grow bg-white border shadow-sm rounded">
                @if($node['children_count'])
                    <button
                            class="px-3 text-gray-500 appearance-none"
                            type="button"
                            title=""
                            wire:click.prevent="toggleChildren"
                    >
                        <x-filament::icon
                                alias="lunar::chevron-right"
                                @class([
                                    'w-3.5 h-3.5 transition ease-in-out duration-200 rtl:rotate-180',
                                    'ltr:rotate-90 rtl:!rotate-90' => !!$node['children'],
                                ])
                        />
                    </button>
                @else
                    <div class="w-10">
                        &nbsp;
                    </div>
                @endif

                <button
                        @class([
                            'w-full py-2 text-left rtl:text-right appearance-none',
                            'px-4' => false,
                            'cursor-default' => true,
                        ])
                        type="button"
                >
                    <div class="flex items-center space-x-2">
                        <div>
                            @if($node['thumbnail'])
                            <img src="{{ $node['thumbnail'] }}" class="w-10 border rounded p-0.5">
                            @else
                                <x-filament::icon alias="lunar::image-placeholder" class="p-1 text-gray-200 w-10 h-10" />
                            @endif
                        </div>
                        <span>{{ $node['name'] }}</span>
                    </div>

                </button>

                <div class="items-center flex-shrink-0 hidden px-2 space-x-2 rtl:space-x-reverse group-hover:flex">
                    {{ $this->getActionGroup() }}
                </div>
            </div>
        </div>

    </div>

    <x-filament-actions::modals />
</div>