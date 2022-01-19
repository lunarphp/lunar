@foreach($this->channels as $channel)
  <div class="grid items-center grid-cols-12 gap-4 py-2 text-sm" >
    <div class="col-span-2">
        <div class="truncate">{{ $channel->name }}</div>
    </div>
    <div class="col-span-7">
      @if($availability['channels'][$channel->id]['enabled'])
        <button type="button" class="text-indigo-500 hover:underline" wire:click="$set('availability.channels.{{ $channel->id }}.scheduling', true)">
          @if($startDate = $availability['channels'][$channel->id]['starts_at'])
            @if($endDate = $availability['channels'][$channel->id]['ends_at'])
              {{ __('adminhub::partials.availability.channels.scheduled_range', [
                'from' => $startDate,
                'to' => $endDate,
              ]) }}
            @else
              {{ __('adminhub::partials.availability.channels.scheduled_from', [
                'datetime' => $startDate
              ]) }}
            @endif
          @elseif($endDate = $availability['channels'][$channel->id]['ends_at'])
            {{ __('adminhub::partials.availability.channels.scheduled_to', [
                'datetime' => $endDate
            ]) }}
          @else
            {{ __('adminhub::partials.availability.channels.scheduled_always') }}
          @endif
        </button>
      @else
        <p class="text-sm text-red-600">{{ __('adminhub::partials.availability.channels.scheduled_never') }}</p>
      @endif

      <x-hub::modal.dialog wire:model="availability.channels.{{ $channel->id }}.scheduling">
        <x-slot name="title">
          {{ __('adminhub::partials.availability.channels.schedule_modal.title') }}
        </x-slot>

        <x-slot name="content">
          <div class="space-y-4">
            <x-hub::input.group
              :label="__('adminhub::partials.availability.channels.schedule_modal.starts_at.label')"
              :instructions="__('adminhub::partials.availability.channels.schedule_modal.starts_at.instructions')"
              for="starts_at"
            >
              <div class="flex">
                <div class="w-full mr-4">
                  <x-hub::input.datepicker id="starts_at" wire:model="availability.channels.{{ $channel->id }}.starts_at" :enable-time="true"/>
                </div>
                @if($availability['channels'][$channel->id]['starts_at'])
                <button
                  type="button"
                  class="text-sm text-gray-500 hover:text-gray-800"
                  wire:click="$set('availability.channels.{{ $channel->id }}.starts_at', null)"
                >{{ __('adminhub::partials.availability.clear_btn') }}</button>
                @endif
              </div>
            </x-hub::input.group>

            <x-hub::input.group
              :label="__('adminhub::partials.availability.customer_groups.schedule_modal.ends_at.label')"
              :instructions="__('adminhub::partials.availability.customer_groups.schedule_modal.ends_at.instructions')"
              for="ends_at"
            >
              <div class="flex">
                <div class="w-full mr-4">
                  <x-hub::input.datepicker id="ends_at" wire:model="availability.channels.{{ $channel->id }}.ends_at" :enable-time="true"/>
                </div>
                @if($availability['channels'][$channel->id]['ends_at'])
                <button
                  type="button"
                  class="text-sm text-gray-500 hover:text-gray-800"
                  wire:click="$set('availability.channels.{{ $channel->id }}.ends_at', null)"
                >{{ __('adminhub::partials.availability.clear_btn') }}</button>
                @endif
              </div>
            </x-hub::input.group>
          </div>
        </x-slot>
        <x-slot name="footer">
          <x-hub::button type="button" wire:click="$set('availability.channels.{{ $channel->id }}.scheduling', false)">
            {{ __('adminhub::partials.availability.channels.schedule_modal.btn_text') }}
          </x-hub::button>
        </x-slot>
      </x-hub::modal.dialog>
    </div>
    <div class="col-span-3">
      <x-hub::input.toggle
        wire:model="availability.channels.{{ $channel->id }}.enabled"
      />
    </div>
</div>
  </div>
@endforeach