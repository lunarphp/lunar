<div>
  <div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.availability.heading', [
          'type' => __('adminhub::types.' . $type ?? 'product')
        ]) }}
      </h3>
    </header>
    <x-hub::alert>
      {{ __('adminhub::partials.availability.schedule_notice', [
        'type' => __('adminhub::types.' . $type ?? 'product')
      ]) }}
    </x-hub::alert>
    <div class="space-y-4">
      <div class="space-y-4">
          <header class="flex items-center justify-between">
            <div>
              <h3 class="font-medium leading-6 text-gray-900 text-md">
                {{ __('adminhub::partials.availability.channel_heading', [
                  'type' => __('adminhub::types.' . $type ?? 'product')
                ]) }}
              </h3>
              <p class="text-sm text-gray-500">
                {{ __('adminhub::partials.availability.channel_strapline', [
                  'type' => __('adminhub::types.' . $type ?? 'product')
                ]) }}
              </p>
            </div>
          </header>
          <div class="divide-y divide ">
            @include('adminhub::partials.availability.channels')
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
                  'type' => __('adminhub::types.' . $type ?? 'product')
                ]) }}
              </p>
            </div>
          </header>
          <div class="mt-4 divide-y divide">
            @include('adminhub::partials.availability.customer-groups', [
                'customerGroupType' => $customerGroupType ?? 'select',
            ])
          </div>
        </div>
      @endif

    </div>
  </div>
</div>

</div>
