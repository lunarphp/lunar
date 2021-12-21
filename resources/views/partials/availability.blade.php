<div>
  <div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.availability.heading', [
          'type' => $type ?? 'product'
        ]) }}
      </h3>
    </header>
    <x-hub::alert>
      {{ __('adminhub::partials.availability.schedule_notice', [
        'type' => $type ?? 'product'
      ]) }}
    </x-hub::alert>
    <div class="space-y-4">
      <div class="space-y-4">
          <header class="flex items-center justify-between">
            <div>
              <h3 class="font-medium leading-6 text-gray-900 text-md">
                {{ __('adminhub::partials.availability.channel_heading', [
                  'type' => $type ?? 'product'
                ]) }}
              </h3>
              <p class="text-sm text-gray-500">
                {{ __('adminhub::partials.availability.channel_strapline', [
                  'type' => $type ?? 'product'
                ]) }}
              </p>
            </div>
          </header>
          <div class="divide-y divide ">

            @foreach($this->channels as $channel)
              <div class="grid items-center grid-cols-12 gap-4 py-2 text-sm" x-data="{
                scheduling: @entangle('availability.channels.'.$channel->id.'.scheduling'),
                scheduled: @entangle('availability.channels.'.$channel->id.'.published_at'),
              }" wire:key="{{ $channel->id }}">
                  <div class="col-span-5">
                      {{ $channel->name }}
                      <p class="text-xs text-gray-500" x-show="scheduled">
                        {{ __('adminhub::partials.availability.scheduled_text', [
                          'type' => $type ?? 'product',
                          'date' => now()->parse($availability['channels'][$channel->id]['published_at'] ?? null)->format('d/m/Y H:ia')
                        ]) }}
                      </p>
                  </div>
                  <div class="col-span-6" >
                    @if($availability['channels'][$channel->id]['enabled'] ?? false)
                      <a href="#" x-show="!scheduling" @click.prevent="scheduling = true" class="text-indigo-500 hover:underline">
                        {{ __('adminhub::partials.availability.schedule_btn_text')}}
                      </a>
                      <div class="flex" x-show="scheduling">
                        <div class="w-full mr-4">
                          <x-hub::input.datepicker
                            placeholder="{{ __('adminhub::partials.availability.schedule_placeholder') }}"
                            wire:model="availability.channels.{{ $channel->id }}.published_at"
                            :enable-time="true"
                          />
                        </div>
                        @if($availability['channels'][$channel->id]['published_at'])
                          <button
                            type="button"
                            class="text-sm text-gray-500 hover:text-gray-800"
                            wire:click="$set('availability.channels.{{ $channel->id }}.published_at', null)"
                          >{{ __('adminhub::partials.availability.clear_btn') }}</button>
                        @endif
                      </div>
                    @endif
                  </div>
                  <div>
                    <x-hub::input.toggle
                      wire:model="availability.channels.{{ $channel->id }}.enabled"
                      @change="scheduling = false"
                    />
                  </div>
              </div>
            @endforeach

          </div>
      </div>
      @if($customerGroups)
        <div>
          <header class="flex items-center justify-between">
            <div>
              <h3 class="font-medium leading-6 text-gray-900 text-md">
                {{ __('adminhub::partials.availability.customer_groups.title') }}
              </h3>
              <p class="text-sm text-gray-500">
                {{ __('adminhub::partials.availability.customer_groups.strapline', [
                  'type' => $type ?? 'product'
                ]) }}
              </p>
            </div>
          </header>
          <div class="mt-4 divide-y divide">
            @include('adminhub::partials.availability.customer-groups')
          </div>
        </div>
      @endif

    </div>
  </div>
</div>

</div>
