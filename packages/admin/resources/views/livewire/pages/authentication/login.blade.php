<div>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      @include('adminhub::partials.getcandy-stamp')

      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        {{ __('adminhub::auth.welcome') }}
      </h2>
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      @livewire('hub.components.login-form')
    </div>
  </div>
</div>
