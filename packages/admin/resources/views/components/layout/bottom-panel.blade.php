<div
    class="fixed bottom-0 start-0 end-0 z-40 p-6 border-t border-gray-100 lg:start-auto bg-white/75"
    :class="{
        'lg:w-[calc(100vw_-_16rem)]': !menuCollapsed,
        'w-full': menuCollapsed
    }"
>
    {{ $slot }}
</div>