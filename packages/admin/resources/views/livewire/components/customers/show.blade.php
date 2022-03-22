<div class="flex-col px-12 mx-auto space-y-4 max-w-7xl">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">
      {{ $customer->fullName }}
    </strong>
  </div>

  <div class="flex gap-x-4">
    <div class="w-2/3 space-y-4">
      <!-- This example requires Tailwind CSS v2.0+ -->
      <div>
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
          <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->ordersCount }}</dd>
          </div>

          <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Avg. Spend</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->avgSpend->formatted }}</dd>
          </div>

          <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Spend</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $this->totalSpend->formatted }}</dd>
          </div>
        </dl>
      </div>
      <div class="bg-white rounded shadow">
        <header class="px-4 py-4 font-bold border-b">
          Spending the past year
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
                Purchase History
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
                Order History
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
                Users
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
                Addresses
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

      </div>

      {{-- <div class="bg-white rounded shadow">
        <div class="bg-white rounded shadow">
            <header class="px-4 py-4 font-bold border-b">
              Users
            </header>

            <div class="p-4 space-y-2">
              <x-hub::table>
                <x-slot name="head">
                  <x-hub::table.heading>
                    {{ __('adminhub::global.name') }}
                  </x-hub::table.heading>

                  <x-hub::table.heading>
                    {{ __('adminhub::global.email') }}
                  </x-hub::table.heading>
                </x-slot>
                <x-slot name="body">
                  @forelse($customer->users as $user)
                    <x-hub::table.row>
                      <x-hub::table.cell>
                        {{ $user->name }}
                      </x-hub::table.cell>

                      <x-hub::table.cell>
                        {{ $user->email }}
                      </x-hub::table.cell>
                    </x-hub::table.row>
                  @empty

                  @endforelse
                </x-slot>
              </x-hub::table>
            </div>
          </div>
      </div> --}}
    </div>

    <div class="w-1/3">
      <div class="bg-white rounded shadow">
        <div class="p-4 space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <x-hub::input.group for="title" label="Title">
                <x-hub::input.text wire:model="customer.title" />
              </x-hub::input.group>
            </div>
            <div>
              <x-hub::input.group for="first_name" label="First Name">
                <x-hub::input.text wire:model="customer.first_name" />
              </x-hub::input.group>
            </div>
            <div>
              <x-hub::input.group for="last_name" label="Last Name">
                <x-hub::input.text wire:model="customer.last_name" />
              </x-hub::input.group>
            </div>
          </div>

          <x-hub::input.group for="company_name" label="Company Name">
            <x-hub::input.text wire:model="customer.company_name" />
          </x-hub::input.group>

          <x-hub::input.group for="vat_no" label="VAT No.">
            <x-hub::input.text wire:model="customer.vat_no" />
          </x-hub::input.group>
          <header class="">
            Customer Groups
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
          <x-hub::button type="button" wire:click="save">Save Customer</x-hub::button>
        </div>
      </div>
    </div>
  </div>
</div>