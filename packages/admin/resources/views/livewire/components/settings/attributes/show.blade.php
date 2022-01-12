<div>
  <div class="text-right">
    <x-hub::button wire:click.prevent="$set('showGroupCreate', true)">
      {{ __('adminhub::components.attributes.show.create_group_btn') }}
    </x-hub::button>
  </div>
  <div
    wire:sort
    sort.options='{group: "groups", method: "sortGroups"}'
    class="mt-8 space-y-2"
  >
    @foreach($sortedAttributeGroups as $group)
      <div
        wire:key="group_{{ $group->id }}"
        x-data="{ expanded: false }"
        sort.item="groups"
        sort.id="{{ $group->id }}"
      >
        <div
          class="flex items-center"
        >
          <div wire:loading wire:target="sort">
              <x-hub::icon ref="refresh" style="solid" class="w-5 mr-2 text-gray-300 rotate-180 animate-spin" />
          </div>

          <div wire:loading.remove wire:target="sort">
            <div sort.handle class="cursor-grab">
              <x-hub::icon ref="selector" style="solid" class="mr-2 text-gray-400 hover:text-gray-700" />
            </div>
          </div>

          <div class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300">
              <div class="flex items-center justify-between expand">
                {{ $group->translate('name') }}
              </div>
              <div class="flex">
                @if($group->attributes->count())
                  <button @click="expanded = !expanded">
                    <div class="transition-transform" :class="{
                      '-rotate-90 ': expanded
                    }">
                      <x-hub::icon ref="chevron-left" style="solid"  />
                    </div>
                  </button>
                @endif
                <x-hub::dropdown minimal>
                  <x-slot name="options">
                    <x-hub::dropdown.button wire:click="$set('editGroupId', {{ $group->id }})" class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                      {{ __('adminhub::components.attributes.show.edit_group_btn') }}
                    </x-hub::dropdown.button>

                    <x-hub::dropdown.button wire:click="$set('editGroupId', {{ $group->id }})" class="flex items-center justify-between px-4 py-2 text-sm text-red-500 border-b hover:bg-gray-50">
                      {{ __('adminhub::components.attributes.show.delete_group_btn') }}
                    </x-hub::dropdown.button>
                  </x-slot>
                </x-hub::dropdown>
              </div>
          </div>
        </div>
        @if($group->attributes->count())
          <div
            class="py-4 pl-2 pr-4 mt-2 space-y-2 bg-black border-l rounded bg-opacity-5 ml-7"
            wire:sort
            sort.options='{group: "attributes", method: "sortAttributes"}'
            x-show="expanded"
          >
            @foreach($group->attributes as $attribute)
              <div
                class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300"
                wire:key="attribute_{{ $attribute->id }}"
                sort.item="attributes"
                sort.parent="{{ $group->id }}"
                sort.id="{{ $attribute->id }}"
              >
                <div sort.handle class="cursor-grab">
                  <x-hub::icon ref="selector" style="solid" class="mr-2 text-gray-400 hover:text-gray-700" />
                </div>
                  <span class="grow">{{ $attribute->translate('name') }}</span>
                  <x-hub::icon ref="cog" style="solid" class="w-4" />

                  <x-hub::dropdown minimal>
                    <x-slot name="options">
                      <x-hub::dropdown.link href="" class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                        {{ __('adminhub::components.attributes.show.edit_attribute_btn') }}
                        <x-hub::icon ref="pencil" style="solid" class="w-4" />
                      </x-hub::dropdown.link>
                    </x-slot>
                  </x-hub::dropdown>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    @endforeach
  </div>

  <x-hub::modal.dialog wire:model="showGroupCreate">
    <x-slot name="title">{{ __('adminhub::components.attributes.show.create_title') }}</x-slot>
    <x-slot name="content">
      @livewire('hub.components.settings.attributes.attribute-group-edit', [
        'typeHandle' => $type,
        'attributableType' => $this->typeClass,
      ])
    </x-slot>
    <x-slot name="footer"></x-slot>
  </x-hub::modal.dialog>

  @if($this->attributeGroupToEdit)
    <x-hub::modal.dialog wire:model="editGroupId">
      <x-slot name="title">{{ __('adminhub::components.attributes.show.edit_title') }}</x-slot>
      <x-slot name="content">
        @livewire('hub.components.settings.attributes.attribute-group-edit', [
          'typeHandle' => $type,
          'attributableType' => $this->typeClass,
          'attributeGroup' => $this->attributeGroupToEdit,
        ])
      </x-slot>
      <x-slot name="footer"></x-slot>
    </x-hub::modal.dialog>
  @endif
</div>