<form method="POST"
      wire:submit.prevent="login"
      class="mt-8 space-y-6">
    @csrf
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
                       'border-red-600' => $errors->has('password'),
                       'border-gray-200 dark:border-gray-700' => !$errors->has('password'),
                   ]) />
        </div>

        @error('email')
            <em class="block mt-2 text-sm not-italic font-medium text-red-600">
                {{ $message }}
            </em>
        @enderror
    </div>

    <div>
        <label for="password"
               class="sr-only">
            {{ __('adminhub::inputs.password') }}
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
                   autocomplete="current-password"
                   wire:model.defer="password"
                   placeholder="{{ __('adminhub::inputs.password') }}"
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
        <label for="remember-me"
               class="flex items-center gap-2">
            <input id="remember-me"
                   name="remember-me"
                   type="checkbox"
                   wire:model.defer="remember"
                   class="w-6 h-6 border-gray-200 rounded-md shadow-sm form-checkbox dark:bg-gray-800 dark:border-gray-700 dark:focus:ring-offset-gray-800" />

            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('adminhub::inputs.remember_me') }}
            </span>
        </label>
    </div>

    @if (session()->has('error'))
        <div class="p-4 border border-red-100 rounde-md bg-red-50">
            <div class="flex items-center gap-2">
                <span class="text-red-600 shrink-0">
                    <x-hub::icon ref="x-circle"
                                 style="solid" />
                </span>

                <strong class="text-sm font-medium text-red-600">
                    {{ session('error') }}
                </strong>
            </div>
        </div>
    @endif

    <button type="submit"
            class="w-full p-3 text-sm text-white transition bg-blue-600 rounded-md hover:bg-blue-500">
        <div wire:loading.delay
             wire:target="login">
            <x-hub::loading-indicator class="w-5 h-5 mx-auto" />
        </div>

        <div wire:loading.delay.remove
             wire:target="login">
            <span>
                {{ __('adminhub::auth.sign-in.btn') }}
            </span>
        </div>
    </button>
</form>
