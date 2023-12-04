@php
    $getRoles = fn ($permissionHandle) => $getPermissionRoles()[$permissionHandle] ?? [];
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div 
        x-data="{ 
            tooltip: @js(__('lunarpanel::staff.acl.tooltip.roles-included')),
        }"
    >
        <x-filament-tables::container class="[&_table]:h-[1px] transition-all">       
            <x-filament-tables::table>
                <x-slot name="header">
                    <th></th>
                    
                    <x-filament-tables::header-cell alignment='center' class="w-1/3">
                        {{ $getLabel() }}
                    </x-filament-tables::header-cell>
                </x-slot>

                @foreach ($getGroupedPermissions() as $groupedPermission)
                    <x-filament-tables::row>
                        <x-filament-tables::cell
                            :wire:key="'lunar.permission.name.' . $groupedPermission->handle"
                            class="!p-3"
                        >
                            <div class="grid gap-0.5">
                                
                                <div class="flex gap-2.5 items-center">
                                    <h4 class="whitespace-normal text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $groupedPermission->transLabel }}
                                    </h4>
                                    
                                    
                                    @if(sizeof($roles = $getRoles($groupedPermission->handle)))
                                        <div class="flex gap-1" x-tooltip="{
                                            content: tooltip,
                                            theme: $store.theme,
                                        }">
                                            @foreach($roles as $role)
                                                <x-filament::badge>
                                                    {{ $role }}
                                                </x-filament::badge>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if($description = $groupedPermission->transDescription)
                                    <p class="whitespace-normal text-sm text-gray-500 dark:text-gray-400">
                                        {{ $description }}
                                    </p>
                                @endif
                            </div>

                        </x-filament-tables::cell>

                        <x-filament-tables::cell
                            :wire:key="'lunar.permission.' . $groupedPermission->handle"
                            class="!p-0"
                        >
                            <div 
                                x-cloak
                                class="group h-full px-4 py-3 flex items-center justify-center hover:cursor-pointer transition-all group hover:bg-primary-50/50 dark:hover:bg-white/5"
                                x-data="{
                                    enabled: $wire.$entangle('{{ $getStatePath() }}.{{ $groupedPermission->handle }}', true),
                                    toggle(){
                                        if(this.enabled !== null){
                                            this.enabled = !this.enabled
                                        }
                                    }
                                }"
                                x-bind:class="{
                                    '!cursor-default': enabled === null
                                }"
                                x-on:click="toggle"
                            >
                                <x-heroicon-s-link
                                    x-show="enabled === null"
                                    :wire:key="'lunar.permission.' . $groupedPermission->handle.'.inherited'"
                                    class="w-6 h-6 text-primary-500 dark:text-primary-400/80" 
                                    defer/>
                                
                                <x-heroicon-s-check-circle 
                                    x-show="enabled === true"
                                    :wire:key="'lunar.permission.' . $groupedPermission->handle.'.enabled'"
                                    class="w-8 h-8 group-hover:scale-110 transition-transform text-green-500 group-hover:text-green-600 dark:text-green-400/80 dark:group-hover:text-green-400" 
                                    defer/>

                                <x-heroicon-s-x-circle 
                                    x-show="enabled === false"
                                    :wire:key="'lunar.permission.' . $groupedPermission->handle.'.disabled'"
                                    class="w-8 h-8 group-hover:scale-110 transition-transform text-red-500 group-hover:text-red-600 dark:text-red-400/80 dark:group-hover:text-red-400" 
                                    defer/>
                            </div>
                        </x-filament-tables::row>
                    </x-filament-tables::row>

                    @foreach($groupedPermission->children as $permission)
                        <x-filament-tables::row>
                            <x-filament-tables::cell
                                :wire:key="'lunar.permission.name.' . $permission->handle"
                                class="py-2.5 !ps-6 pe-2"
                            >
                                <div class="grid gap-0.5">
                                    <div class="flex gap-2.5 items-center">
                                        <h4 class="whitespace-normal text-sm font-medium text-gray-950 dark:text-white">
                                            {{ $permission->transLabel }}
                                        </h4>

                                        @if(sizeof($roles = $getRoles($permission->handle)))
                                            <div class="flex gap-1" x-tooltip="{
                                                content: tooltip,
                                                theme: $store.theme,
                                            }">
                                                @foreach($roles as $role)
                                                    <x-filament::badge>
                                                        {{ $role }}
                                                    </x-filament::badge>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    @if($description = $permission->transDescription)
                                        <p class="whitespace-normal text-sm text-gray-500 dark:text-gray-400">
                                            {{ $description }}
                                        </p>
                                    @endif
                                </div>
                            </x-filament-tables::cell>
                                
                            <x-filament-tables::cell
                                :wire:key="'lunar.permission.' . $groupedPermission->handle . '.' . $permission->handle"
                                class="!p-0"
                            >
                                <div 
                                    x-cloak
                                    class="group h-full px-4 py-3 flex items-center justify-center hover:cursor-pointer transition-all hover:bg-primary-50 dark:hover:bg-white/5"
                                    x-data="{
                                        group: $wire.$entangle('{{ $getStatePath() }}.{{ $groupedPermission->handle }}', false),
                                        enabled: $wire.$entangle('{{ $getStatePath() }}.{{ $permission->handle }}', true),
                                        init(){
                                            $watch('group', value => {
                                                if(this.group === false){
                                                    this.enabled = false
                                                }
                                            })
                                        },
                                        toggle(){
                                            if(this.group !== false && this.enabled !== null){
                                                this.enabled = !this.enabled
                                            }
                                        }
                                    }"
                                    x-on:click="toggle"
                                    x-bind:class="{
                                        'bg-gray-300/20 dark:bg-white/10 pointer-events-none': group === false,
                                        '!cursor-default': enabled === null,
                                    }"
                                >
                                    <x-heroicon-s-link
                                        x-show="enabled === null"
                                        :wire:key="'lunar.permission.' . $groupedPermission->handle . '.' . $permission->handle.'.inherited'"
                                        class="w-6 h-6 text-primary-500 dark:text-primary-400/80"
                                        defer/>

                                    <x-heroicon-s-check-circle 
                                        x-show="enabled === true"
                                        :wire:key="'lunar.permission.' . $groupedPermission->handle . '.' . $permission->handle.'.enabled'"
                                        class="w-8 h-8 group-hover:scale-110 transition-transform text-green-500 group-hover:text-green-600 dark:text-green-400/80 dark:group-hover:text-green-400"
                                        defer/>

                                    <x-heroicon-s-x-circle 
                                        x-show="enabled === false"
                                        :wire:key="'lunar.permission.' . $groupedPermission->handle . '.' . $permission->handle.'.disabled'"
                                        class="w-8 h-8 group-hover:scale-110 transition-transform text-red-500 group-hover:text-red-600 dark:text-red-400/80 dark:group-hover:text-red-400" 
                                        x-bind:class="{
                                            '!text-red-400/80 dark:!text-red-400/60': group === false
                                        }"
                                        defer/>
                                </div>
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @endforeach
                @endforeach
            </x-filament-tables::table>
        </x-filament-tables::container>
    </div>

    <svg hidden class="hidden">
        @stack('bladeicons')
    </svg>
</x-dynamic-component>
