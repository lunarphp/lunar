@props(['collection'])

<div class="relative group">
    <div @class([
        'bg-white rounded-lg border border-gray-300 w-full flex justify-between',
        'dark:bg-gray-900 dark:border-white/10',
    ])>
        <div class="flex w-full">

            <div @class([
                    'flex items-center bg-gray-50 rounded-l-lg rtl:rounded-r-lg border-r rtl:border-r-0 rtl:border-l border-gray-300 px-2',
                    'dark:bg-gray-800 dark:border-white/10 cursor-grab',
                ])>
                @svg('heroicon-o-arrows-up-down', 'w-3.5 h-3.5')
            </div>



            <button
                class="px-3 text-gray-500 appearance-none"
                type="button"
                title=""
            >
                @svg('heroicon-o-chevron-right', 'w-3.5 h-3.5 transition ease-in-out duration-200 rtl:rotate-180', ['x-bind:class' => "{'ltr:rotate-90 rtl:!rotate-90': open}"])
            </button>


            <button
                @class([
                    'w-full py-2 text-left rtl:text-right appearance-none',
                    'px-4' => false,
                    'cursor-default' => true,
                ])
                type="button"
            >
                <span>{{ $collection }}</span>
            </button>

            <button
                class="px-3 text-gray-500 appearance-none"
                type="button"
                title=""
            >
                @svg('heroicon-o-ellipsis-vertical', 'w-3.5 h-3.5')
            </button>
        </div>

        <div class="items-center flex-shrink-0 hidden px-2 space-x-2 rtl:space-x-reverse group-hover:flex">

        </div>
    </div>
</div>
