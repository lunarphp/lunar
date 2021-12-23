<div>
  <div class="flex flex-col justify-center min-h-screen py-12 bg-gray-50 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      @include('adminhub::partials.getcandy-stamp')

      <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900">
        Welcome to the Hub.
      </h2>
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        @livewire('hub.components.login-form')
    </div>
  </div>
</div>
