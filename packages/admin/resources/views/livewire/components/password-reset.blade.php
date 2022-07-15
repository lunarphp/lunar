<div>
    @if ($invalid)
        <div class="mt-8 space-y-4">
            <div class="p-4 border border-red-100 rounded-md bg-red-50">
                <div class="flex items-center gap-2">
                    <span class="text-red-600 shrink-0">
                        <x-hub::icon ref="x-circle"
                                     style="solid" />
                    </span>

                    <strong class="text-sm font-medium text-red-600">
                        {{ __('adminhub::auth.reset-password.invalid') }}
                    </strong>
                </div>
            </div>

            <p>
                <a href="{{ route('hub.login') }}"
                   class="text-blue-600 transition hover:text-blue-500">
                    {{ __('adminhub::auth.reset-password.back_link') }}
                </a>
            </p>
        </div>
    @else
        <form method="POST"
              wire:submit.prevent="process"
              class="mt-8 space-y-6">
            @csrf

            @if ($token)
                <div>
                    <label for="password"
                           class="sr-only">
                        {{ __('adminhub::inputs.new_password') }}
                    </label>

                    <div class="relative">
                        <span @class([
                            'absolute inset-y-0 grid text-blue-600 left-3 place-content-center',
                            '!text-red-600' => $errors->has('password'),
                        ])>
                            <x-hub::icon ref="lock-closed"
                                         class="w-5 h-5" />
                        </span>

                        <input id="password"
                               name="password"
                               type="password"
                               autocomplete="new-password"
                               wire:model.defer="password"
                               placeholder="{{ __('adminhub::inputs.new_password') }}"
                               @class([
                                   'w-full py-3 pl-10 pr-3 text-gray-900 dark:text-white rounded-md shadow-sm dark:bg-gray-800 sm:text-sm form-input',
                                   'border-red-600' => $errors->has('password'),
                                   'border-gray-200 dark:border-gray-700' => !$errors->has('password'),
                               ]) />
                    </div>

                    @error('password')
                        <em class="block mt-2 text-sm not-italic font-medium text-red-600">
                            {{ $message }}
                        </em>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation"
                           class="sr-only">
                        {{ __('adminhub::inputs.confirm_password') }}
                    </label>

                    <div class="relative">
                        <span @class([
                            'absolute inset-y-0 grid text-blue-600 left-3 place-content-center',
                            '!text-red-600' => $errors->has('password_confirmation'),
                        ])>
                            <x-hub::icon ref="lock-closed"
                                         class="w-5 h-5" />
                        </span>

                        <input id="password_confirmation"
                               name="password_confirmation"
                               type="password"
                               autocomplete="new-password"
                               wire:model.defer="password_confirmation"
                               placeholder="{{ __('adminhub::inputs.new_password_confirmation') }}"
                               @class([
                                   'w-full py-3 pl-10 pr-3 text-gray-900 dark:text-white rounded-md shadow-sm dark:bg-gray-800 sm:text-sm form-input',
                                   'border-red-600' => $errors->has('password_confirmation'),
                                   'border-gray-200 dark:border-gray-700' => !$errors->has(
                                       'password_confirmation'
                                   ),
                               ]) />
                    </div>

                    @error('password_confirmation')
                        <em class="block mt-2 text-sm not-italic font-medium text-red-600">
                            {{ $message }}
                        </em>
                    @enderror
                </div>
            @endif

            @if (!$token)
                <div>
                    <label for="email"
                           class="sr-only">
                        {{ __('adminhub::inputs.email') }}
                    </label>

                    <div class="relative">
                        <span @class([
                            'absolute inset-y-0 grid text-blue-600 left-3 place-content-center',
                            '!text-red-600' => $errors->has('email'),
                        ])>
                            <x-hub::icon ref="mail"
                                         class="w-5 h-5" />
                        </span>

                        <input id="email"
                               name="email"
                               type="email"
                               autocomplete="email"
                               wire:model.defer="email"
                               placeholder="{{ __('adminhub::inputs.email') }}"
                               @class([
                                   'w-full py-3 pl-10 pr-3 text-gray-900 dark:text-white rounded-md shadow-sm dark:bg-gray-800 sm:text-sm form-input',
                                   'border-red-600' => $errors->has('email'),
                                   'border-gray-200 dark:border-gray-700' => !$errors->has('email'),
                               ]) />
                    </div>

                    @error('email')
                        <em class="block mt-2 text-sm not-italic font-medium text-red-600">
                            {{ $message }}
                        </em>
                    @enderror
                </div>
            @endif

            <div>
                <button type="submit"
                        class="w-full p-3 text-sm text-white transition bg-blue-600 rounded-md hover:bg-blue-500">
                    <div wire:loading.delay
                         wire:target="process">
                        <x-hub::loading-indicator class="w-5 h-5 mx-auto" />
                    </div>

                    <div wire:loading.delay.remove
                         wire:target="process">
                        <span>
                            {{ __('adminhub::auth.reset-password.' . ($token ? 'update_btn' : 'send_btn')) }}
                        </span>
                    </div>
                </button>
            </div>
        </form>
    @endif
</div>
