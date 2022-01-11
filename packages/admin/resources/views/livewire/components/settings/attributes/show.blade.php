<div>
  <div class="text-right">
    <x-hub::button>Create Attribute Group</x-hub::button>
  </div>
  <div wire:sort sort.options='{group: "groups", method: "sortGroups"}' class="flex gap-4 mt-8">
    @foreach($sortedAttributeGroups as $group)
       <div
        sort.item="groups"
        sort.id="{{ $group->id }}"
        wire:key="group_{{ $group->id }}"
        class="w-1/3 h-full p-4 text-blue-800 border border-blue-600 rounded bg-blue-50"
      >
        <header class="flex items-center">
          <div sort.handle class="cursor-grab">
            <x-hub::icon ref="switch-horizontal" style="solid"  />
          </div>
          <div class="grow">{{ $group->translate('name') }}</div>
          <x-hub::icon ref="cog" style="solid" class="w-4" />
        </header>

        @if($group->attributes->count())
          <div class="my-4 space-y-2">
            @foreach($group->attributes as $attribute)
              <div class="flex items-center">
                <x-hub::icon ref="selector" style="solid"  />

                <div class="flex items-center px-3 py-2 text-sm border border-blue-600 rounded grow">
                  <span class="grow">{{ $attribute->translate('name') }}</span>

                  <x-hub::icon ref="cog" style="solid" class="w-4" />
                </div>
              </div>
            @endforeach
          </div>
        @endif

        <div class="mt-2">
          <x-hub::button class="w-full">Add Attribute</x-hub::button>
        </div>

       </div>
    @endforeach
  </div>
</div>