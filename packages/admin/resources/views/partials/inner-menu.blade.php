<div x-show="showInnerMenu">
    <aside class="hidden h-full lg:block lg:flex-shrink-0 lg:order-first">
        <div class="relative flex flex-col h-full overflow-y-auto bg-white border-r border-gray-100 w-72">
            <div class="px-4 py-16">
                <x-hub::menu handle="settings"
                             current="{{ request()->route()->getName() }}">
                    <ul class="space-y-2">
                        @foreach ($component->items as $item)
                            <li>
                                <a href="{{ route($item->route) }}"
                                   @class([
                                       'flex items-center gap-2 p-2 rounded text-gray-500',
                                       'bg-blue-50 text-blue-700 hover:text-blue-600' => $item->isActive(
                                           $component->attributes->get('current')
                                       ),
                                       'hover:bg-blue-50 hover:text-blue-700' => !$item->isActive(
                                           $component->attributes->get('current')
                                       ),
                                   ])>
                                    {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                    <span class="text-sm font-medium">
                                        {{ $item->name }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-hub::menu>
            </div>
        </div>
    </aside>
</div>
