<div class="space-y-4">
    @foreach ($attributeGroups ?? $this->attributeGroups as $groupIndex => $group)
        <div @class(['shadow sm:rounded-md' => $inline ?? false])
             wire:key="attribute-group-{{ $groupIndex }}">
            <div @class([
                'space-y-4 bg-white border-white border dark:bg-gray-800 dark:border-gray-700 rounded',
                ' px-4 py-5 sm:p-6' => !($inline ?? false),
            ])>
                <header>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $group['model']->translate('name') }}
                    </h3>
                </header>

                <div class="space-y-4">
                    @foreach ($group['fields'] as $attIndex => $field)
                        <div wire:key="attributes_{{ $field['handle'] }}">
                            <x-hub::input.group :label="$field['name']"
                                                :for="$field['handle']"
                                                :required="$field['required']"
                                                :error="$errors->first(
                                                    ($mapping ?? 'attributeMapping') . '.' . $attIndex . '.data',
                                                ) ?:
                                                    $errors->first(
                                                        ($mapping ?? 'attributeMapping') .
                                                            '.' .
                                                            $attIndex .
                                                            '.data.' .
                                                            $this->defaultLanguage->code,
                                                    )">
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
