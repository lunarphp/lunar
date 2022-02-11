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
    @forelse($sortedAttributeGroups as $group)
      <div
        wire:key="group_{{ $group->id }}"
        x-data="{ expanded: {{ $group->attributes->count() <= 4 ? 'true' : 'false' }} }"
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

                    <x-hub::dropdown.button wire:click="$set('attributeCreateGroupId', {{ $group->id }})" class="flex items-center justify-between px-4 py-2 text-sm border-b hover:bg-gray-50">
                      {{ __('adminhub::components.attributes.show.create_attribute') }}
                    </x-hub::dropdown.button>

                    <x-hub::dropdown.button wire:click="$set('deleteGroupId', {{ $group->id }})" class="flex items-center justify-between px-4 py-2 text-sm border-b hover:bg-gray-50">
                      <span class="text-red-500">{{ __('adminhub::components.attributes.show.delete_group_btn') }}</span>
                    </x-hub::dropdown.button>
                  </x-slot>
                </x-hub::dropdown>
              </div>
          </div>
        </div>
        <div class="py-4 pl-2 pr-4 mt-2 space-y-2 bg-black border-l rounded bg-opacity-5 ml-7" @if($group->attributes->count()) x-show="expanded" @endif>
          <div
            class="space-y-2"
            wire:sort
            sort.options='{group: "attributes", method: "sortAttributes", owner: {{ $group->id }}}'
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
                <span class="truncate grow">{{ $attribute->translate('name') }}</span>
                <div class="mr-4 text-xs text-gray-500">
                  {{ class_basename($attribute->type) }}
                </div>
                <div>
                  <x-hub::dropdown minimal>
                    <x-slot name="options">
                      <x-hub::dropdown.button
                        type="button"
                        wire:click="$set('editAttributeId', {{ $attribute->id }})"
                        class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50"
                      >
                        {{ __('adminhub::components.attributes.show.edit_attribute_btn') }}
                        <x-hub::icon ref="pencil" style="solid" class="w-4" />
                      </x-hub::dropdown.button>

                      @if(!$attribute->system)
                      <x-hub::dropdown.button wire:click="$set('deleteAttributeId', {{ $attribute->id }})" class="flex items-center justify-between px-4 py-2 text-sm border-b hover:bg-gray-50">
                        <span class="text-red-500">{{ __('adminhub::components.attributes.show.delete_attribute_btn') }}</span>
                      </x-hub::dropdown.button>
                      @endif
                    </x-slot>
                  </x-hub::dropdown>
                </div>
              </div>
            @endforeach
          </div>
          @if(!$group->attributes->count())
            <span class="mx-4 text-sm text-gray-500">
              {{ __('adminhub::components.attributes.show.no_attributes_text') }}
            </span>
          @endif
        </div>
      </div>
    @empty
      <div class="w-full text-center text-gray-500">
        {{ __('adminhub::components.attributes.show.no_groups') }}
      </div>
    @endforelse
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

  @if($this->attributeGroupToDelete)
    <x-hub::modal.dialog wire:model="deleteGroupId">
      <x-slot name="title">{{ __('adminhub::components.attributes.show.delete_title') }}</x-slot>
      <x-slot name="content">
        <x-hub::alert level="danger">
          @if(!$this->groupProtected)
            {{ __('adminhub::components.attributes.show.delete_warning') }}
          @else
            {{ __('adminhub::components.attributes.show.group_protected') }}
          @endif
        </x-hub::alert>
      </x-slot>
      <x-slot name="footer">
        <div class="flex justify-between">
          <x-hub::button theme="gray" wire:click="$set('deleteGroupId', null)" type="button">
            {{ __('adminhub::global.cancel') }}
          </x-hub::button>
          @if(!$this->groupProtected)
          <x-hub::button theme="danger" type="button" wire:click="deleteGroup">
            {{ __('adminhub::global.delete') }}
          </x-hub::button>
          @endif
        </div>
      </x-slot>
    </x-hub::modal.dialog>
  @endif

  @if($this->attributeToDelete)
    <x-hub::modal.dialog wire:model="deleteAttributeId">
      <x-slot name="title">{{ __('adminhub::components.attributes.show.delete_attribute_title') }}</x-slot>
      <x-slot name="content">
        <x-hub::alert level="danger">
          @if(!$this->attributeToDelete->system)
            {{ __('adminhub::components.attributes.show.delete_attribute_warning') }}
          @else
            {{ __('adminhub::components.attributes.show.delete_attribute_protected') }}
          @endif
        </x-hub::alert>
      </x-slot>
      <x-slot name="footer">
        <div class="flex justify-between">
          <x-hub::button theme="gray" wire:click="$set('deleteAttributeId', null)" type="button">
            {{ __('adminhub::global.cancel') }}
          </x-hub::button>
          @if(!$this->attributeToDelete->system)
          <x-hub::button theme="danger" type="button" wire:click="deleteAttribute">
            {{ __('adminhub::global.delete') }}
          </x-hub::button>
          @endif
        </div>
      </x-slot>
    </x-hub::modal.dialog>
  @endif

  @if($this->attributeCreateGroup)
    @livewire('hub.components.settings.attributes.attribute-edit', [
      'group' => $this->attributeCreateGroup,
    ])
  @endif
  @if($this->attributeToEdit)
    @livewire('hub.components.settings.attributes.attribute-edit', [
      'attribute' => $this->attributeToEdit,
    ])
  @endif
</div>