<div class="space-y-4">
    @if (count($items))
        <div>
            <ul class="space-y-2">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ route($item->route) }}"
                           @class([
                               'menu-link',
                               'menu-link--active' => $item->isActive($active),
                               'menu-link--inactive' => !$item->isActive($active),
                           ])>
                            {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                            <span class="text-sm font-medium">
                                {{ $item->name }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse ($sections as $section)
        @if (count($section->getItems()))
            <div>
                <header class="text-sm font-semibold text-gray-600">
                    {{ $section->name }}
                </header>

                <ul class="mt-2 space-y-2">
                    @foreach ($section->getItems() as $item)
                        <li>
                            <a href="{{ route($item->route) }}"
                               @class([
                                   'flex items-center gap-2 p-2 rounded text-gray-500',
                                   'bg-blue-50 text-blue-700 hover:text-blue-600' => $item->isActive($active),
                                   'hover:bg-blue-50 hover:text-blue-700' => !$item->isActive($active),
                               ])>
                                {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                <span class="text-sm font-medium">
                                    {{ $item->name }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @empty
    @endforelse
</div>
