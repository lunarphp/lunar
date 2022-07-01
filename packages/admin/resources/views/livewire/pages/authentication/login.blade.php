<section class="relative overflow-hidden bg-white dark:bg-gray-900">
    <img src="https://getcandy.io/assets/imgs/logos/favicon.svg"
         alt="GetCandy Logo"
         class="absolute inset-0 w-full h-full scale-150 opacity-10 blur-3xl" />

    <main class="grid min-h-screen place-content-center">
        <div class="relative w-screen max-w-lg px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
            <div
                 class="px-6 py-10 bg-white rounded-lg shadow-2xl dark:bg-gray-800 shadow-blue-500/25 dark:shadow-blue-500/10 sm:px-8 sm:py-12">
                <img src="https://getcandy.io/assets/imgs/logos/favicon.svg"
                     alt="GetCandy Logo"
                     class="w-12 h-12 mx-auto" />

                <div class="mt-6 text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome to GetCandy</h1>

                    <p class="mt-4 text-sm leading-relaxed text-gray-500 dark:text-gray-40">
                        Good morning! Let's hope today brings plenty of sales.
                        <br class="hidden sm:block" />
                        Log in to administrate your e-commerce store.
                    </p>
                </div>

                @livewire('hub.components.login-form')
            </div>

            <p class="mt-8 text-sm text-center text-gray-500 dark:text-gray-400">
                Forgot your password?

                <a href="{{ route('hub.password-reset') }}"
                   class="text-blue-600 transition hover:text-blue-500">
                    {{ __('adminhub::auth.forgot-password.link') }}
                </a>
            </p>
        </div>
    </main>
</section>


{{-- <div>
  <div class="flex flex-col justify-center min-h-screen py-12 bg-gray-50 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      @include('adminhub::partials.getcandy-stamp')

      <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900">
        {{ __('adminhub::auth.welcome') }}
      </h2>
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      @livewire('hub.components.login-form')
    </div>
  </div>
</div> --}}
