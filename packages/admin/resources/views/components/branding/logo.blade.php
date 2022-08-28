@if (!$iconOnly)
     <img src="https://getcandy.io/hub/getcandy_logo.svg"
          {{ $attributes }}
          alt="GetCandy Logo"
          class="w-auto h-10"
          x-cloak />

@else
     <img src="https://getcandy.io/assets/imgs/logos/favicon.svg"
          {{ $attributes }}
          alt="GetCandy Logo"
          class="w-8 h-8 mx-auto" />
@endif
