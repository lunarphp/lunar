<div class="flex-col px-12 mx-auto space-y-4 max-w-7xl">
  <div class="flex items-center justify-between">
    <strong class="text-lg font-bold md:text-2xl">
      {{ $customer->fullName }}
    </strong>
    <div>
      <x-hub::button type="button" wire:click="save">Save Customer</x-hub::button>
    </div>
  </div>

  <div class="flex gap-x-4">
    <div class="w-2/3 space-y-4">
      <div class="p-4 space-y-4 bg-white rounded shadow">
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

        <div class="grid grid-cols-2 gap-4">
          <div>
            <x-hub::input.group for="company_name" label="Company Name">
              <x-hub::input.text wire:model="customer.company_name" />
            </x-hub::input.group>
          </div>
          <div>
            <x-hub::input.group for="vat_no" label="VAT No.">
              <x-hub::input.text wire:model="customer.vat_no" />
            </x-hub::input.group>
          </div>
        </div>
      </div>
      <div class="bg-white rounded shadow">
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
      </div>
    </div>

    <div class="w-1/3">
      <div class="bg-white rounded shadow">
        <header class="px-4 py-4 font-bold border-b">
          Customer Groups
        </header>

        <div class="p-4 space-y-2">
          @foreach($this->customerGroups as $group)
            <label class="flex items-center p-2 text-sm border rounded cursor-pointer" wire:key="group_{{ $group->id }}">
              <x-hub::input.checkbox wire:model.debounce.500ms="syncedGroups" value="{{ $group->id }}" /> <span class="ml-2">{{ $group->name }}</span>
            </label>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>