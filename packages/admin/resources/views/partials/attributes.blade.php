<div class="space-y-4">
  @foreach(($attributeGroups ?? $this->attributeGroups) as $groupIndex => $group)
    <div class="@if(!($inline ?? false)) shadow sm:rounded-md @endif" wire:key="attribute-group-{{ $groupIndex }}">
      <div class="flex-col space-y-4 bg-white rounded @if(!($inline ?? false)) px-4 py-5 sm:p-6 @endif">
        <header>
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            {{ $group['model']->translate('name') }}
          </h3>
        </header>
        <div class="space-y-4">
          @foreach($group['fields'] as $attIndex => $field)
            <div wire:key="attributes_{{ $field['handle'] }}">
              <x-hub::input.group
                :label="$field['name']"
                :for="$field['handle']"
                :required="$field['required']"
                :error="
                  $errors->first(($mapping ?? 'attributeMapping').'.'.$attIndex.'.data') ?:
                  $errors->first(($mapping ?? 'attributeMapping').'.'.$attIndex.'.data.'.$this->defaultLanguage->code)
                "
              >
                @include($field['view'], [
                  'field' => $field,
                ])
              </x-hub::input.group>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  @endforeach
</div>
