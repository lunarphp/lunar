<div class="space-y-4">
  @foreach($attributeMapping->groupBy('group_handle') as $groupIndex => $fields)
    <div class="shadow sm:rounded-md" wire:key="attribute-group-{{ $groupIndex }}">
      <div class="flex-col px-4 py-5 space-y-4 bg-white rounded sm:p-6">
        <header>
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            {{ $fields->first()['group'] }}
          </h3>
        </header>
        <div class="space-y-4">
          @foreach($fields as $attIndex => $field)
            <div wire:key="{{ $field['handle'] }}">
              <x-hub::input.group
                :label="$field['name']"
                :for="$field['handle']"
                :required="$field['required']"
                :error="
                  $errors->first('attributeMapping.'.$attIndex.'.data') ?:
                  $errors->first('attributeMapping.'.$attIndex.'.data.'.$this->defaultLanguage->code)
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
