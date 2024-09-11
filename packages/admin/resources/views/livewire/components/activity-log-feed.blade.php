<div>
    <div class="flex items-center gap-4 mt-4">
        <div class="shrink-0">
            @livewire('hub.components.avatar')
        </div>

        <form class="relative w-full"
              wire:submit.prevent="addComment">
            <textarea class="w-full pl-4 pr-32 pt-5 border border-gray-200 rounded-lg h-[58px] sm:text-sm form-text"
                      type="text"
                      placeholder="{{ __('adminhub::components.comments.placeholder') }}"
                      wire:model.defer="comment"
                      required
                      multiline></textarea>

            <button class="absolute h-[42px] text-xs font-bold leading-[42px] text-gray-700 bg-gray-100 border border-transparent rounded-md hover:border-gray-100 hover:bg-gray-50 w-28 top-2 right-2"
                    type="submit">
                <div wire:loading.remove
                     wire:target="addComment">
                    {{ __('adminhub::components.comments.add_btn') }}
                </div>

                <div wire:loading
                     wire:target="addComment">
                    <x-hub::icon ref="refresh"
                                 style="solid"
                                 class="inline-block rotate-180 animate-spin" />
                </div>
            </button>
        </form>
    </div>

    <div class="relative pt-8 -ml-[5px]">
        <span class="absolute inset-y-0 left-5 w-[2px] bg-gray-200 rounded-full"></span>

        <div class="flow-root">
            <ul class="-my-8 divide-y-2 divide-gray-200"
                role="list">
                @foreach ($this->activityLog as $log)
                    <li class="relative py-8 ml-5">
                        <p class="ml-8 font-bold text-gray-900">
                            {{ $log['date']->format('F jS, Y') }}
                        </p>

                        <ul class="mt-4 space-y-6">
                            @foreach ($log['items'] as $item)
                                <li class="relative pl-8">
                                    <div @class([
                                        'absolute top-[2px]',
                                        '-left-[calc(0.75rem_-_1px)]' => $item['log']->causer,
                                        '-left-[calc(0.5rem_-_1px)]' => !$item['log']->causer,
                                    ])>
                                        @if ($item['log']->causer)
                                            <x-hub::gravatar :email="$item['log']->causer->email"
                                                             class="w-6 h-6 rounded-full ring-4 ring-gray-200" />
                                        @else
                                            <span @class([
                                                'absolute w-4 h-4 rounded-full ring-4 bg-gray-300 ring-gray-200',
                                                '!bg-sky-500 !ring-sky-100' => $item['log']->description == 'created',
                                                '!bg-purple-500 !ring-purple-100' =>
                                                    $item['log']->description == 'status-update',
                                                '!bg-teal-500 !ring-teal-100' => $item['log']->description == 'updated',
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
                                            <div class="text-xs font-medium text-gray-500">
                                                @if (!$item['log']->causer)
                                                    {{ __('adminhub::components.activity-log.system') }}
                                                @else
                                                    {{ $item['log']->causer->fullName ?: $item['log']->causer->name }}
                                                @endif
                                            </div>

                                            @if (count($item['renderers']))
                                                <div class="mt-2 text-sm font-medium text-gray-700">
                                                    @foreach ($item['renderers'] as $class)
                                                        {{ $class->render($item['log']) }}
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
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
</div>
