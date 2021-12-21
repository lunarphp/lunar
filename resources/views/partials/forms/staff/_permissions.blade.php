<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <div class="grid items-center grid-cols-2">
      <div class="space-y-1">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('adminhub::settings.staff.form.permissions_heading') }}
        </h3>

        <p class="max-w-2xl text-sm text-gray-500">
          {{ __('adminhub::settings.staff.form.permissions_description') }}
        </p>
      </div>


      @if(auth()->user()->admin)
        <div class="text-right">
          <label class="inline-flex items-center cursor-pointer">
            <span class="block mr-2 text-xs font-bold @if($staff->admin) text-green-500 @else text-gray-400 @endif uppercase">Admin</span>
            <x-hub::input.toggle
              :on="$staff->admin"
              wire:click.prevent="toggleAdmin"
            />
          </label>
        </div>
      @endif
    </div>

    @if($staff->admin)
      <x-hub::alert>
        {{ __('adminhub::settings.staff.form.admin_message') }}
      </x-hub:alert>
    @else
      <ul role="list" class="mt-2 divide-y divide-gray-200 @if($staff->admin) opacity-50 pointer-events-none @endif">
        @foreach($firstPartyPermissions as $permission)
          <li class="py-4">
            <div class="flex items-center justify-between ">
              <div class="flex flex-col">
                <p class="text-sm font-medium text-gray-900" id="privacy-option-1-label">
                  {{ $permission->name }}
                </p>
                <p class="text-sm text-gray-500" id="privacy-option-1-description">
                  {{ $permission->description }}
                </p>
              </div>
              <x-hub::input.toggle
                :on="$staffPermissions->contains($permission->handle)"
                wire:click.prevent="togglePermission('{{ $permission->handle }}', {{ $permission->children->pluck('handle') }})"
              />
            </div>
            @if($permission->children->count())
              <div class="py-2 pl-4 mt-2 space-y-2 border-l @if(!$staffPermissions->contains($permission->handle)) opacity-50 pointer-events-none @endif">
                @foreach($permission->children as $child)
                  <div class="flex items-center justify-between ">
                    <div class="flex flex-col">
                      <p class="text-sm font-medium text-gray-900" id="privacy-option-1-label">
                        {{ $child->name }}
                      </p>
                      <p class="text-sm text-gray-500" id="privacy-option-1-description">
                        {{ $child->description }}
                      </p>
                    </div>
                    <x-hub::input.toggle
                      :on="$staffPermissions->contains($child->handle)"
                      wire:click.prevent="togglePermission('{{ $child->handle }}')"
                    />
                  </div>
                @endforeach
              </div>
            @endif
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</div>
