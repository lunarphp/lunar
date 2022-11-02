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

            @if (auth()->user()->admin)
                <div class="text-right">
                    <label class="inline-flex items-center cursor-pointer">
                        <span @class([
                            'block mr-2 text-xs font-bold uppercase',
                            'text-green-500' => $staff->admin,
                            'text-gray-400' => !$staff->admin,
                        ])>
                            {{ __('adminhub::global.admin') }}
                        </span>

                        <x-hub::input.toggle :on="$staff->admin"
                                             wire:click.prevent="toggleAdmin" />
                    </label>
                </div>
            @endif
        </div>

        @if ($staff->admin)
            <x-hub::alert>
                {{ __('adminhub::settings.staff.form.admin_message') }}
            </x-hub::alert>
        @else
            <ul @class([
                'mt-2 divide-y divide-gray-200',
                'opacity-50 pointer-events-none' => $staff->admin,
            ])>
                @foreach ($firstPartyPermissions as $permission)
                    <li class="py-4">
                        <div class="flex items-center justify-between ">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $permission->name }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    {{ $permission->description }}
                                </p>
                            </div>

                            <x-hub::input.toggle :on="$staffPermissions->contains($permission->handle)"
                                                 wire:click.prevent="togglePermission('{{ $permission->handle }}', {{ $permission->children->pluck('handle') }})"
                                                 x-init="$watch('checked', (isChecked) => $emit('.permission-settings', { checked: isChecked }))" />
                        </div>

                        @if ($permission->children->count())
                            <div @class([
                                'py-2 pl-4 mt-2 space-y-2 border-l',
                                'opacity-50 pointer-events-none' => !$staffPermissions->contains(
                                    $permission->handle
                                ),
                            ])>
                                @foreach ($permission->children as $child)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $child->name }}
                                            </p>

                                            <p class="text-sm text-gray-500">
                                                {{ $child->description }}
                                            </p>
                                        </div>

                                        <x-hub::input.toggle :on="$staffPermissions->contains($child->handle)"
                                                             class="permission-{{ Str::before($child->handle, ':') }} permission-{{ Str::replace(':', '-', $child->handle) }}"
                                                             wire:click.prevent="togglePermission('{{ $child->handle }}')" />
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
