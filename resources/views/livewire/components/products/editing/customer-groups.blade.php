<div class="space-y-4">
    <header>
      <h3 class="font-medium leading-6 text-gray-900 text-md">
        Customer Groups
      </h3>
      <p class="text-sm text-gray-500">Select which customer groups this product is available for.</p>
    </header>
    <div class="divide-y divide ">
        <div class="grid items-center grid-cols-12 gap-4 py-2 text-sm" x-data="{ scheduling: false, scheduled: false }">
          <div class="col-span-5">
              Guest
              <p class="text-xs text-gray-500" x-show="scheduled">This product is scheduled to be published on {{ now()->addDays(2)->format('d/m/Y H:ma') }}.</p>
          </div>
          <div class="col-span-6" >
            <a href="#" x-show="!scheduling" @click.prevent="scheduling = true" class="text-indigo-500 hover:underline">Schedule availability</a>
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
              Retail
              <p class="text-xs text-gray-500" x-show="scheduled">This product is scheduled to be published on {{ now()->addDays(2)->format('d/m/Y H:ma') }}.</p>
          </div>
          <div class="col-span-6" >
            <a href="#" x-show="!scheduling" @click.prevent="scheduling = true" class="text-indigo-500 hover:underline">Schedule availability</a>
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
              Trade
              <p class="text-xs text-gray-500" x-show="scheduled">This product is scheduled to be published on {{ now()->addDays(2)->format('d/m/Y H:ma') }}.</p>
          </div>
          <div class="col-span-6" >
            {{-- <a href="#" x-show="!scheduling" @click.prevent="scheduling = true" class="text-indigo-500 hover:underline">Schedule availability</a>
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
