<div class="space-y-4">
    <header>
      <h3 class="font-medium leading-6 text-gray-900 text-md">
        {{ __('adminhub::catalogue.customer.show.customer_groups') }}
      </h3>
      <p class="text-sm text-gray-500">{{ __('adminhub::catalogue.editing.customer-groups.select_groups') }}</p>
    </header>
    <div class="divide-y divide ">
        <div class="grid items-center grid-cols-12 gap-4 py-2 text-sm" x-data="{ scheduling: false, scheduled: false }">
          <div class="col-span-5">
              {{ __('adminhub::global.guest') }}
              <p class="text-xs text-gray-500" x-show="scheduled">{{ __('adminhub::catalogue.editing.customer-groups.publish_on') }}
                {{ now()->addDays(2)->format('d/m/Y H:ma') }}.</p>
          </div>
          <div class="col-span-6" >
            <a href="#" x-show="!scheduling" @click.prevent="scheduling = true" class="text-sky-500 hover:underline">
                {{ __('adminhub::catalogue.editing.customer-groups.schedule_availability') }}
            </a>
            <div x-show="scheduling">
              <x-hub::input.datepicker placeholder="Schedule publish date." />
            </div>
              {{-- <x-hub::input.datepicker placeholder="Schedule publish date." /> --}}
          </div>
          <div>
            <x-hub::input.toggle :on="true" />
          </div>
      </div>

      <div class="grid items-center grid-cols-12 gap-4 py-2 text-sm" x-data="{ scheduling: false, scheduled: false }">
          <div class="col-span-5">
            {{ __('adminhub::global.retail') }}
              <p class="text-xs text-gray-500" x-show="scheduled">{{ __('adminhub::catalogue.editing.customer-groups.publish_on') }}
                {{ now()->addDays(2)->format('d/m/Y H:ma') }}.</p>
          </div>
          <div class="col-span-6" >
            <a href="#" x-show="!scheduling" @click.prevent="scheduling = true" class="text-sky-500 hover:underline">
                {{ __('adminhub::catalogue.editing.customer-groups.schedule_availability') }}
            </a>
            <div x-show="scheduling">
              <x-hub::input.datepicker placeholder="Schedule publish date." />
            </div>
              {{-- <x-hub::input.datepicker placeholder="Schedule publish date." /> --}}
          </div>
          <div>
            <x-hub::input.toggle :on="true" />
          </div>
      </div>

      <div class="grid items-center grid-cols-12 gap-4 py-2 text-sm" x-data="{ scheduling: false, scheduled: false }">
          <div class="col-span-5">
            {{ __('adminhub::global.trade') }}
              <p class="text-xs text-gray-500" x-show="scheduled">{{ __('adminhub::catalogue.editing.customer-groups.publish_on') }}
                {{ now()->addDays(2)->format('d/m/Y H:ma') }}.</p>
          </div>
          <div class="col-span-6" >
            {{-- <a href="#" x-show="!scheduling" @click.prevent="scheduling = true" class="text-sky-500 hover:underline">Schedule availability</a>
            <div x-show="scheduling">
              <x-hub::input.datepicker placeholder="Schedule publish date." />
            </div> --}}
              {{-- <x-hub::input.datepicker placeholder="Schedule publish date." /> --}}
          </div>
          <div>
            <x-hub::input.toggle :on="false" />
          </div>
      </div>
  </div>
</div>
