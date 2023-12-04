<x-filament-panels::page>
    <x-filament-tables::container class="[&_table]:h-[1px] !overflow-auto">
        <x-filament-tables::table>
            <x-slot name="header">
                <th 
                    class="min-w-[50vw] md:min-w-[32rem] z-10 sticky left-0 bg-gray-50 dark:bg-gray-800 dark:border-b border-white/10"
                ></th>
                
                @foreach($this->roles as $role)
                    <x-filament-tables::header-cell alignment='center'>
                        <span class="whitespace-normal">{{ $role->transLabel }}</span>
                    </x-filament-tables::header-cell>
                @endforeach
            </x-slot>

            @foreach ($this->groupedPermissions as $groupedPermission)
                <x-filament-tables::row>
                    <x-filament-tables::cell
                        class="z-10 sticky left-0 bg-white dark:bg-gray-900 !p-3"
                        :wire:key="'lunar.permission.name.' . $groupedPermission->handle"
                    >
                        <div class="whitespace-normal grid gap-0.5">
                            <h4 class="text-sm font-medium text-gray-950 dark:text-white">
                                {{ $groupedPermission->transLabel }}
                            </h4>

                            @if($description = $groupedPermission->transDescription)
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $description }}
                                </p>
                            @endif
                        </div>
                    </x-filament-tables::cell>

                    @foreach($this->roles as $role)
                        <x-filament-tables::cell
                            :wire:key="'lunar.permission.' . $role->handle . '.' . $groupedPermission->handle"
                            class="!p-0"
                        >
                            <div 
                                x-cloak
                                class="group h-full px-4 py-3 flex items-center justify-center hover:cursor-pointer transition-all group hover:bg-primary-50/50 dark:hover:bg-white/5"
                                x-data="{
                                    enabled: $wire.$entangle('{{ $this->getStatePath($role->handle, $groupedPermission->handle) }}', true)
                                }"
                                x-on:click="enabled = !enabled"
                            >
                                <x-heroicon-s-check-circle 
                                    x-show="enabled"
                                    :wire:key="'lunar.permission.' . $role->handle . '.' . $groupedPermission->handle.'.enabled'"
                                    class="w-8 h-8 group-hover:scale-110 transition-transform text-green-500 group-hover:text-green-600 dark:text-green-400/80 dark:group-hover:text-green-400" 
                                    defer/>

                                <x-heroicon-s-x-circle 
                                    x-show="!enabled"
                                    :wire:key="'lunar.permission.' . $role->handle . '.' . $groupedPermission->handle.'.disabled'"
                                    class="w-8 h-8 group-hover:scale-110 transition-transform text-red-500 group-hover:text-red-600 dark:text-red-400/80 dark:group-hover:text-red-400" 
                                    defer/>
                            </div>
                        </x-filament-tables::row>
                    @endforeach
                </x-filament-tables::row>

                @foreach($groupedPermission->children as $permission)
                    <x-filament-tables::row>
                        <x-filament-tables::cell
                            class="py-2.5 !ps-6 pe-2 z-10 sticky left-0 bg-white dark:bg-gray-900"
                            :wire:key="'lunar.permission.name.' . $permission->handle"
                        >
                            <div class="whitespace-normal grid gap-0.5">
                                <h4 class="text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $permission->transLabel }}
                                </h4>

                                @if($description = $permission->transDescription)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $description }}
                                    </p>
                                @endif
                            </div>
                        </x-filament-tables::cell>
                            
                        @foreach($this->roles as $role)
                            <x-filament-tables::cell
                                :wire:key="'lunar.permission.' . $role->handle . '.' . $groupedPermission->handle . '.' . $permission->handle"
                                class="!p-0"
                            >
                                <div 
                                    x-cloak
                                    class="group h-full px-4 py-3 flex items-center justify-center hover:cursor-pointer transition-all hover:bg-primary-50 dark:hover:bg-white/5"
                                    x-data="{
                                        enabled: $wire.$entangle('{{ $this->getStatePath($role->handle, $permission->handle) }}', true),
                                        group: $wire.$entangle('{{ $this->getStatePath($role->handle, $groupedPermission->handle) }}', false)
                                    }"
                                    x-on:click="enabled = !enabled"
                                    x-bind:class="{
                                        'bg-gray-300/20 dark:bg-white/10 pointer-events-none': !group,
                                    }"
                                >
                                    <x-heroicon-s-check-circle 
                                        x-show="enabled"
                                        :wire:key="'lunar.permission.' . $role->handle . '.' . $groupedPermission->handle . '.' . $permission->handle.'.enabled'"
                                        class="w-8 h-8 group-hover:scale-110 transition-transform text-green-500 group-hover:text-green-600 dark:text-green-400/80 dark:group-hover:text-green-400"
                                        defer/>

                                    <x-heroicon-s-x-circle 
                                        x-show="!enabled"
                                        :wire:key="'lunar.permission.' . $role->handle . '.' . $groupedPermission->handle . '.' . $permission->handle.'.disabled'"
                                        class="w-8 h-8 group-hover:scale-110 transition-transform text-red-500 group-hover:text-red-600 dark:text-red-400/80 dark:group-hover:text-red-400" 
                                        x-bind:class="{
                                            '!text-red-400/80 dark:!text-red-400/60': !group
                                        }"
                                        defer/>
                                </div>
                            </x-filament-tables::cell>
                        @endforeach
                    </x-filament-tables::row>
                @endforeach
            @endforeach

            <x-filament-tables::row>
                <x-filament-tables::cell
                    class="z-10 sticky left-0 bg-gray-50 dark:bg-gray-800"
                />
                @foreach($this->roles as $role)
                    <x-filament-tables::cell
                        :wire:key="'lunar.role.' . $role->handle . '.delete'"
                        class="!p-0"
                    >
                        @if($role->firstParty)
                            <div class="w-full h-full bg-gray-50 dark:bg-gray-800">
                            </div>
                        @else
                            <div class="flex justify-center items-center p-2 transition-all hover:bg-danger-100/80 dark:hover:bg-danger-300/20">
                                {{ ($this->deleteRoleAction)(['handle' => $role->handle]) }}
                            </div>
                        @endif
                    </x-filament-tables::cell>
                @endforeach
            </x-filament-tables::row>
        </x-filament-tables::table>
    </x-filament-tables::container>
    
    <svg hidden class="hidden">
        @stack('bladeicons')
    </svg>
</x-filament-panels::page>