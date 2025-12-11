<div class="px-2 pb-4 scroll-mt-32" id="lunar-panel-timeline">
    <div class="relative flex items-end gap-4 mt-4">
        <div class="shrink-0">
            <div>
                <img src="{{ $this->userAvatar }}"
                     class="inline-block w-8 h-8 rounded-full" />
            </div>
        </div>

        <form class="w-full"
            wire:submit.prevent="addComment">

            {{ $this->form }}

            <div class="absolute right-0 mt-2">
                {{ $this->addCommentAction }}
            </div>
        </form>
    </div>

    <div class="relative pt-8 -ml-[5px] z-10 pointer-events-none">
        <span class="absolute inset-y-0 left-5 w-[2px] bg-gray-200 dark:bg-gray-600 rounded-full"></span>

        <div class="flow-root">
            <ul class="-my-8 divide-y-2 divide-gray-200 dark:divide-gray-600"
                role="list">
                @foreach ($this->activityLog as $log)
                    <li class="relative py-8 ml-5">
                        <p class="ml-8 font-bold text-gray-950 dark:text-gray-300">
                            {{ $log['date']->format('F jS, Y') }}
                        </p>

                        <ul class="mt-4 space-y-6 pointer-events-auto">
                            @foreach ($log['items'] as $item)
                                @php
                                    $logUserName = $item['log']->causer ? ($item['log']->causer->fullName ?: $item['log']->causer->name) : null;
                                @endphp

                                <li class="relative pl-8">
                                    <div @class([
                                        'absolute top-[2px]',
                                        '-left-[calc(0.75rem_-_1px)]' => $item['log']->causer,
                                        '-left-[calc(0.5rem_-_1px)]' => !$item['log']->causer,
                                    ])>
                                        @if ($email = $item['log']->causer?->email)
                                            <img
                                                src="{{ $this->getAvatarUrl($email) }}"
                                                class="w-6 h-6 rounded-full ring-4 ring-gray-200 dark:ring-gray-600"
                                                alt="{{ $logUserName }}"
                                            />
                                        @else
                                            <span @class([
                                                'absolute w-4 h-4 rounded-full ring-4',
                                                match($item['log']->description){
                                                    'created' => 'bg-sky-500 ring-sky-100 dark:ring-sky-800',
                                                    'updated' => 'bg-teal-500 ring-teal-100 dark:ring-teal-800',
                                                    'status-update' => 'bg-purple-500 ring-purple-100 dark:ring-purple-800',
                                                    default => 'bg-gray-300 ring-gray-200 dark:ring-gray-600',
                                                },
                                            ])>
                                            </span>
                                        @endif
                                    </div>

                                    <div @class([
                                        'flex justify-between',
                                        'pt-[5px]' => $item['log']->causer,
                                        'pt-[1px]' => !$item['log']->causer,
                                    ])>
                                        <div>
                                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                @if (!$item['log']->causer)
                                                    {{ __('lunarpanel::components.activity-log.system') }}
                                                @else
                                                    {{ $logUserName }}
                                                @endif
                                            </div>

                                            @if (count($item['renderers']))
                                                <div class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                                    @foreach ($item['renderers'] as $class)
                                                        {{ $class->render($item['log']) }}
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 dark:text-gray-400 font-medium">
                                            {{ $item['log']->created_at->format('h:ia') }}
                                        </time>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="pt-4">
        {{ $this->activityLog->links('lunarpanel::components.activity-log.timeline-paginator.index', data: ['scrollTo' => '#lunar-panel-timeline']) }}
    </div>
</div>
