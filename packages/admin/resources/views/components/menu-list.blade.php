<div class="space-y-2">
    @if (count($items))
        <ul class="space-y-2">
            @foreach ($items as $item)
                <li>
                    <a href="{{ route($item->route) }}"
                       @class([
                           'menu-link group',
                           'menu-link--active' => $item->isActive($active),
                           'menu-link--inactive !text-gray-700' => !$item->isActive($active),
                       ])>
                        <span>
                            {!! $item->renderIcon('w-5 h-5') !!}
                        </span>

                        <span class="text-sm font-medium">
                            {{ $item->name }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    @forelse ($sections as $section)
        @if (count($section->getItems()))
            <div>
                <header class="text-sm font-semibold text-gray-600">
                    {{ $section->name }}
                </header>

                <ul class="mt-1 space-y-0.5">
                    @foreach ($section->getItems() as $item)
                        <li>
                            <a href="{{ route($item->route) }}"
                               @class([
                                   'menu-link group',
                                   'menu-link--active' => $item->isActive($active),
                                   'menu-link--inactive !text-gray-700' => !$item->isActive($active),
                               ])>
                                <span>
                                    {!! $item->renderIcon('w-5 h-5') !!}
                                </span>

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
