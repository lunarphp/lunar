<div>
    <section class="relative overflow-hidden bg-white dark:bg-gray-900">
        <x-hub::branding.logo class="absolute inset-0 w-full h-full scale-150 opacity-10 blur-3xl"
                              iconOnly />

        <main class="grid min-h-screen place-content-center">
            <div class="relative w-screen max-w-lg px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
                <div
                     class="px-6 py-10 bg-white rounded-lg shadow-2xl dark:bg-gray-800 shadow-sky-500/25 dark:shadow-sky-500/10 sm:px-8 sm:py-12">
                    <x-hub::branding.logo class="w-12 h-12 mx-auto"
                                          iconOnly />

                    <div class="mt-6 text-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ __('adminhub::auth.reset-your-password') }}
                        </h1>
                    </div>

                    @livewire('hub.components.password-reset')
                </div>

                <p class="mt-8 text-sm text-center text-gray-500 dark:text-gray-400">
                    {{ __('adminhub::auth.forgot-password.remember') }}

                    <a href="{{ route('hub.login') }}"
                       class="text-sky-500 transition hover:text-sky-500">
                        {{ __('adminhub::auth.sign-in.btn') }}
                    </a>
                </p>
            </div>
        </main>
    </section>

    <x-hub::notification />
</div>
