<div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white border border-white sm:p-6 dark:bg-gray-800 dark:border-gray-700">
        <header class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ __('adminhub::partials.urls.title') }}
            </h3>
        </header>

        @if ($errors->has('urls'))
            <x-hub::alert level="danger">
                {{ $errors->first('urls') }}
            </x-hub::alert>
        @endif

        <div class="space-y-4">
            <div>
                <div class="flex items-center gap-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <div class="w-64">{{ __('adminhub::global.language') }}</div>
                    <div class="w-full">{{ __('adminhub::global.slug') }}</div>
                    <div class="w-32">{{ __('adminhub::global.default') }}</div>
                </div>
            </div>

            @foreach ($urls as $url)
                <div wire:key="url_{{ $url['key'] }}">
                    <div class="flex items-center gap-4"
                         wire:key="url_{{ $loop->index }}">
                        <div class="w-64">
                            <x-hub::input.select wire:model="urls.{{ $loop->index }}.language_id">
                                @foreach ($this->languages as $lang)
                                    <option value="{{ $lang['id'] }}">{{ $lang['name'] }}</option>
                                @endforeach
                            </x-hub::input.select>
                        </div>

                        <div class="w-full">
                            <x-hub::input.text wire:model.lazy="urls.{{ $loop->index }}.slug" />
                        </div>

                        <div class="flex items-center w-32 gap-4">
                            <x-hub::input.toggle wire:model="urls.{{ $loop->index }}.default" />

                            <button class="text-gray-400 dark:text-gray-300"
                                    wire:click.prevent="removeUrl('{{ $loop->index }}')">
                                <x-hub::icon ref="trash"
                                             style="solid" />
                            </button>
                        </div>
                    </div>
                </div>
                @if ($errors->has("urls.{$loop->index}.*"))
                    <div class="mt-2 text-sm text-red-500">
                        @foreach ($errors->get("urls.{$loop->index}.*") as $fields)
                            @foreach ($fields as $error)
                                {{ $error }}
                            @endforeach
                        @endforeach
                    </div>
                @endif
            @endforeach

            <x-hub::button theme="gray"
                           wire:click.prevent="addUrl">
                {{ __('adminhub::partials.urls.create_btn') }}
            </x-hub::button>
        </div>
    </div>
</div>
