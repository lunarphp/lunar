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
                      <x-hub::activity-log.order-activity
                        :activity="$item"
                      />
                    @endforeach
                  </ul>
                </li>
              @endforeach
              {{-- @for ($i = 0; $i < 3; $i++)
                <li class="relative py-8 ml-5">
                  <p class="ml-8 font-bold text-gray-900">
                    October 4th, 2021
                  </p>

                  <ul class="mt-4 space-y-6">
                    <li class="relative pl-8">
                      <span
                        class="absolute w-4 h-4 bg-gray-300 rounded-full top-[2px] -left-[calc(0.5rem_-_1px)] ring-4 ring-gray-200"
                      >
                      </span>

                      <div class="flex justify-between">
                        <p class="text-sm font-medium text-gray-700">
                          Order confirmation email was sent to Alec Ritson (alec@neondigital.co.uk)
                        </p>

                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
                          12:40pm GMT
                        </time>
                      </div>

                      <div class="flex gap-4 mt-2">
                        <button
                          class="flex-shrink-0 px-4 py-2 text-xs font-bold text-gray-700 border rounded bg-gray-50 hover:bg-white"
                          type="button"
                        >
                          Resend Email
                        </button>

                        <button
                          class="flex-shrink-0 px-4 py-2 text-xs font-bold text-gray-700 border rounded bg-gray-50 hover:bg-white"
                          type="button"
                        >
                          View Email
                        </button>
                      </div>
                    </li>

                    <li class="relative pl-8">
                      <span
                        class="absolute w-4 h-4 bg-emerald-500 rounded-full top-[2px] -left-[calc(0.5rem_-_1px)] ring-4 ring-emerald-100"
                      >
                      </span>

                      <div class="flex justify-between">
                        <p class="text-sm font-medium text-gray-700">
                          A payment of $186.00 was made.
                        </p>

                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
                          12:37pm GMT
                        </time>
                      </div>
                    </li>

                    <li class="relative pl-8">
                      <span
                        class="absolute w-4 h-4 bg-blue-500 rounded-full top-[2px] -left-[calc(0.5rem_-_1px)] ring-4 ring-blue-100"
                      >
                      </span>

                      <div class="flex justify-between">
                        <p class="text-sm font-medium text-gray-700">
                          Order was created and marked pending.
                        </p>

                        <time class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 font-medium">
                          12:30pm GMT
                        </time>
                      </div>
                    </li>
                  </ul>
                </li>
              @endfor --}}
            </ul>
          </div>
        </div>