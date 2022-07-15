@foreach($this->customerGroups as $group)
<div class="grid items-center grid-cols-12 gap-4 py-2 text-sm" >
    <div class="col-span-2">
        <div class="truncate">{{ $group->name }}</div>
    </div>
    <div class="col-span-7">
      @if($availability['customerGroups'][$group->id]['status'] != 'hidden')
      <button type="button" class="text-indigo-500 hover:underline" wire:click="$set('availability.customerGroups.{{ $group->id }}.scheduling', true)">
        @if($startDate = $availability['customerGroups'][$group->id]['starts_at'])
          @if($endDate = $availability['customerGroups'][$group->id]['ends_at'])
            {{ __('adminhub::partials.availability.customer_groups.scheduled_range', [
              'from' => $startDate,
              'to' => $endDate,
            ]) }}
          @else
            {{ __('adminhub::partials.availability.customer_groups.scheduled_from', [
              'datetime' => $startDate
            ]) }}
          @endif
        @elseif($endDate = $availability['customerGroups'][$group->id]['ends_at'])
          {{ __('adminhub::partials.availability.customer_groups.scheduled_to', [
              'datetime' => $endDate
          ]) }}
        @else
          {{ __('adminhub::partials.availability.customer_groups.scheduled_always') }}
        @endif
      </button>
      @else
        <p class="text-sm text-red-600">{{ __('adminhub::partials.availability.customer_groups.scheduled_never') }}</p>
      @endif

      <x-hub::modal.dialog wire:model="availability.customerGroups.{{ $group->id }}.scheduling">
        <x-slot name="title">
          {{ __('adminhub::partials.availability.customer_groups.schedule_modal.title') }}
        </x-slot>

        <x-slot name="content">
          <div class="space-y-4">
            <x-hub::input.group
              :label="__('adminhub::partials.availability.customer_groups.schedule_modal.starts_at.label')"
              :instructions="__('adminhub::partials.availability.customer_groups.schedule_modal.starts_at.instructions')"
              for="starts_at"
            >
              <div class="flex">
                <div class="w-full mr-4">
                  <x-hub::input.datepicker id="starts_at" wire:model="availability.customerGroups.{{ $group->id }}.starts_at" :options="[ 'enableTime' => true ]"/>
                </div>
                @if($availability['customerGroups'][$group->id]['starts_at'])
                <button
                  type="button"
                  class="text-sm text-gray-500 hover:text-gray-800"
                  wire:click="$set('availability.customerGroups.{{ $group->id }}.starts_at', null)"
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
                  <x-hub::input.datepicker id="ends_at" wire:model="availability.customerGroups.{{ $group->id }}.ends_at" :options="[ 'enableTime' => true ]"/>
                </div>
                @if($availability['customerGroups'][$group->id]['ends_at'])
                <button
                  type="button"
                  class="text-sm text-gray-500 hover:text-gray-800"
                  wire:click="$set('availability.customerGroups.{{ $group->id }}.ends_at', null)"
                >{{ __('adminhub::partials.availability.clear_btn') }}</button>
                @endif
              </div>
            </x-hub::input.group>
          </div>
        </x-slot>
        <x-slot name="footer">
          <x-hub::button type="button" wire:click="$set('availability.customerGroups.{{ $group->id }}.scheduling', false)">
            {{ __('adminhub::partials.availability.customer_groups.schedule_modal.btn_text') }}
          </x-hub::button>
        </x-slot>
      </x-hub::modal.dialog>
    </div>
    <div class="col-span-3">
      <x-hub::input.select wire:model="availability.customerGroups.{{ $group->id }}.status">
        <option value="visible" selected>
          {{ __('adminhub::partials.availability.customer_groups.visible') }}
        </option>
        <option value="hidden">
          {{ __('adminhub::partials.availability.customer_groups.hidden') }}
        </option>
        @if($customerGroups['purchasable'] ?? true)
        <option value="purchasable">
          {{ __('adminhub::partials.availability.customer_groups.purchasable') }}
        </option>
        @endif
      </x-hub::input.select>
    </div>
</div>
@endforeach
