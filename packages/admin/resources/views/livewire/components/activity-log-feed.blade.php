<div>
  <div class="flex items-center mt-4">
    <div class="flex-shrink-0">
      @livewire('hub.components.avatar')
    </div>
    <form class="relative w-full ml-4" wire:submit.prevent="addComment">
      <textarea
        class="w-full pl-4 pr-32 pt-5 border border-gray-200 rounded-lg h-[58px] sm:text-sm form-text"
        type="text"
        placeholder="Add a comment"
        wire:model.defer="comment"
        required
        multiline
      >
      </textarea>

      <button
        class="absolute h-[42px] text-xs font-bold leading-[42px] text-gray-700 bg-gray-100 border border-transparent rounded-md hover:border-gray-100 hover:bg-gray-50 w-28 top-2 right-2"
        type="submit"
      >
        <div wire:loading.remove wire:target="addComment">
          Add Comment
        </div>
        <div wire:loading wire:target="addComment">
          <x-hub::icon ref="refresh" style="solid" class="inline-block rotate-180 animate-spin" />
        </div>

      </button>
    </form>
  </div>
  <div class="relative pt-8">
    <span class="absolute inset-y-0 left-5 w-[2px] bg-gray-200"></span>

    <div class="flow-root">
      <ul
        class="-my-8 divide-y-2 divide-gray-200"
        role="list"
      >
        @foreach($this->activityLog as $log)
          <li class="relative py-8 ml-5">
            <p class="ml-8 font-bold text-gray-900">
              {{ $log['date']->format('F jS, Y') }}
            </p>

            <ul class="mt-4 space-y-6">
              @foreach($log['items'] as $item)
                <li class="relative pl-8">
                  <div class="absolute top-[2px] -left-[calc(0.5rem_-_1px)]">
                    @if($item['log']->causer)
                      <x-hub::gravatar :email="$item['log']->causer->email" class="w-5 h-5 rounded-full" />
                    @else
                      <span
                        class="absolute w-4 h-4
                          @if($item['log']->description == 'created')
                            bg-blue-500 ring-blue-100
                          @elseif($item['log']->description == 'status-update')
                            bg-purple-500 ring-purple-100
                          @elseif($item['log']->description == 'updated')
                            bg-teal-500 ring-teal-100
                          @else
                            bg-gray-300 ring-gray-200
                          @endif
                          rounded-full ring-4"
                      >
                      </span>
                    @endif
                  </div>
                  <div class="flex justify-between">
                    <div>
                      <div class="text-xs font-medium text-gray-500">
                        @if(!$item['log']->causer)
                          {{ __('adminhub::components.activity-log.system') }}
                        @else
                          {{ $item['log']->causer->fullName ?: $item['log']->causer->name }}
                        @endif
                      </div>
                      <div class="mt-2 text-sm font-medium text-gray-700">
                        @foreach ($item['renderers'] as $class)
                          {{ $class->render($item['log']) }}
                        @endforeach
                      </div>
                    </div>

                    <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
                      {{ $item['log']->created_at->format('h:ia')}}
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
