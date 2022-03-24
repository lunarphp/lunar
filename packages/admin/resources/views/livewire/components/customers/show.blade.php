<div class="flex-col px-12 mx-auto space-y-4 max-w-7xl">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">
      {{ $customer->fullName }}
    </strong>
  </div>

  <div class="md:flex gap-x-4">
    <div class="space-y-4 md:w-2/3">
      <!-- This example requires Tailwind CSS v2.0+ -->
      <div>
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
          <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">
              {{ __('adminhub::components.customers.show.metrics.total_orders') }}
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->ordersCount }}</dd>
          </div>

          <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">
              {{ __('adminhub::components.customers.show.metrics.avg_spend') }}
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->avgSpend->formatted }}</dd>
          </div>

          <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">
              {{ __('adminhub::components.customers.show.metrics.total_spend') }}
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->totalSpend->formatted }}</dd>
          </div>
        </dl>
      </div>
      <div class="bg-white rounded shadow">
        <header class="px-4 py-4 font-bold border-b">
          {{ __('adminhub::components.customers.show.year_spending') }}
        </header>
        <div class="p-4 h-80">
        @livewire('hub.components.reporting.apex-chart', ['options' => $this->spendingChart])
        </div>
      </div>

      <div x-data="{ tab: 'purchase_history' }">
        <div>
          <div class="hidden sm:block">
            <nav class="flex space-x-4" aria-label="Tabs">
              <!-- Current: "bg-indigo-100 text-indigo-700", Default: "text-gray-500 hover:text-gray-700" -->
              <button
                type="button"
                x-on:click.prevent="tab = 'purchase_history'"
                class="px-3 py-2 text-sm font-medium rounded-md "
                :class="{
                  'bg-white shadow': tab == 'purchase_history',
                  'hover:text-gray-700 text-gray-500': tab != 'purchase_history'
                }"
              >
                {{ __('adminhub::components.customers.show.purchase_history') }}
              </button>

              <button
                type="button"
                x-on:click.prevent="tab = 'order_history'"
                class="px-3 py-2 text-sm font-medium rounded-md "
                :class="{
                  'bg-white shadow': tab == 'order_history',
                  'hover:text-gray-700 text-gray-500': tab != 'order_history'
                }"
              >
                {{ __('adminhub::components.customers.show.order_history') }}
              </button>

              <button
                type="button"
                x-on:click.prevent="tab = 'users'"
                class="px-3 py-2 text-sm font-medium rounded-md "
                :class="{
                  'bg-white shadow': tab == 'users',
                  'hover:text-gray-700 text-gray-500': tab != 'users'
                }"
              >
                {{ __('adminhub::components.customers.show.users') }}
              </button>

              <a
                href="#"
                x-on:click.prevent="tab = 'addresses'"
                class="px-3 py-2 text-sm font-medium rounded-md "
                :class="{
                  'bg-white shadow': tab == 'addresses',
                  'hover:text-gray-700 text-gray-500': tab != 'addresses'
                }"
              >
                {{ __('adminhub::components.customers.show.addresses') }}
              </a>
            </nav>
          </div>
        </div>

        <div x-show="tab == 'purchase_history'" class="mt-4">
          @include('adminhub::partials.customers.purchase-history')
        </div>

        <div x-show="tab == 'order_history'" class="mt-4">
          @include('adminhub::partials.customers.order-history')
        </div>

        <div x-show="tab == 'users'" class="mt-4">
          @include('adminhub::partials.customers.users')
        </div>

        <div x-show="tab == 'addresses'" class="mt-4">
          @include('adminhub::partials.customers.addresses')
        </div>

      </div>
    </div>

    <div class="w-1/3">
      <div class="bg-white rounded shadow">
        <div class="p-4 space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <x-hub::input.group for="title" :label="__('adminhub::inputs.title')">
                <x-hub::input.text wire:model="customer.title" />
              </x-hub::input.group>
            </div>
            <div>
              <x-hub::input.group for="first_name" :label="__('adminhub::inputs.firstname')">
                <x-hub::input.text wire:model="customer.first_name" />
              </x-hub::input.group>
            </div>
            <div>
              <x-hub::input.group for="last_name" :label="__('adminhub::inputs.lastname')">
                <x-hub::input.text wire:model="customer.last_name" />
              </x-hub::input.group>
            </div>
          </div>

          <x-hub::input.group for="company_name" :label="__('adminhub::inputs.company_name.label')">
            <x-hub::input.text wire:model="customer.company_name" />
          </x-hub::input.group>

          <x-hub::input.group for="vat_no" :label="__('adminhub::inputs.vat_no.label')">
            <x-hub::input.text wire:model="customer.vat_no" />
          </x-hub::input.group>
          <header class="">
            {{ __('adminhub::components.customers.show.customer_groups') }}
          </header>

          <div class="space-y-2">
            @foreach($this->customerGroups as $group)
              <label class="flex items-center p-2 text-sm border rounded cursor-pointer" wire:key="group_{{ $group->id }}">
                <x-hub::input.checkbox wire:model.debounce.500ms="syncedGroups" value="{{ $group->id }}" /> <span class="ml-2">{{ $group->name }}</span>
              </label>
            @endforeach
          </div>
        </div>
        <div class="p-4 text-right rounded-b bg-gray-50">
            <x-hub::button type="button" wire:click="save">
              <div wire:loading wire:target="save">
                <div>
                  <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </div>
              <div wire:loading.remove wire:target="save">
                <span>{{ __('adminhub::components.customers.show.save_customer') }}</span>
              </div>
            </x-hub::button>
        </div>
      </div>
    </div>
  </div>
</div>