<div class="flex-col space-y-4">
  <div class="overflow-hidden rounded-md shadow">
    @if(!$details['licensed'])
    <x-hub::alert level="danger">
        {{ __('adminhub::notifications.addon.not_licensed') }}
    </x-hub::alert>
    @endif
  </div>
  <div class="overflow-hidden bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::catalogue.addons.show.addon_details') }}
      </h3>
    </div>
    <div class="border-t border-gray-200">
      <dl>
        <div class="px-4 py-5 bg-gray-50 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">
            {{ __('adminhub::global.name') }}
          </dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            {{ $details['name'] }}
          </dd>
        </div>
        <div class="px-4 py-5 bg-white sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">
            {{ __('adminhub::catalogue.addons.show.developer') }}
          </dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            {{ $details['author'] }}
          </dd>
        </div>
        <div class="px-4 py-5 bg-gray-50 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">
            {{ __('adminhub::catalogue.addons.show.marketplace') }}
          </dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <a href="{{ $details['marketplaceUrl'] }}" class="text-indigo-500 hover:underline">View on Marketplace</a>
          </dd>
        </div>
        <div class="px-4 py-5 bg-white sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">
            {{ __('adminhub::global.version') }}
          </dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            {{ $details['version'] }}
          </dd>
        </div>
      </dl>
    </div>
  </div>
</div>
