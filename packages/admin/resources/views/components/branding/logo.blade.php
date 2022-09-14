@if ($iconOnly)
    <img src="https://lunarphp.io/assets/imgs/logos/favicon.svg"
         {{ $attributes }}
         alt="Lunar Logo"
         class="w-8 h-8 mx-auto" />
@else
    <img src="https://lunarphp.io/hub/lunar_logo.svg"
         {{ $attributes }}
         alt="Lunar Logo"
         class="w-auto h-10"
         x-cloak />
@endif
