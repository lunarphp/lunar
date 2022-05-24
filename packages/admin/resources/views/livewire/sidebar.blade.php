<div>
    <x-hub::menu handle="sidebar" current="{{ request()->route()->getName() }}">
        <div class="flex-col space-y-3">
            @foreach ($component->items as $item)
                <a href="{{ route($item->route) }}"
                    class="group @if (!$item->isActive($component->attributes->get('current'))) text-gray-400 hover:text-gray-900 dark:text-gray-500 dark:hover:text-gray-200 @else text-blue-600 @endif mx-5 flex items-center rounded-md px-2 py-2 text-base font-medium">
                    {!! $item->renderIcon() !!}
                    <span class="ml-2">{{ $item->name }}</span>
                </a>
            @endforeach
        </div>
    </x-hub::menu>

    <div class="mt-4">
        @if (Auth::user()->can('settings'))
            <a href="{{ route('hub.settings') }}"
                class="group @if (!Str::contains(request()->url(), 'settings')) text-gray-400 hover:text-gray-900 dark:text-gray-500 dark:hover:text-gray-200 @else text-gray-900 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 @endif mx-5 flex items-center rounded-md px-2 py-2 text-base">
                {!! GetCandy\Hub\GetCandyHub::icon('cog', 'w-6 h-6 mr-2') !!}
                {{ __('adminhub::global.settings') }}
            </a>
        @endif
    </div>
</div>
