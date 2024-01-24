<div>
  @props(['offColor' => 'gray', 'onColor' => 'success', 'statePath'])
  <button
          x-data="{
                state: @entangle($statePath).live,
            }"
          x-bind:aria-checked="state?.toString()"
          x-on:click="state = ! state"
          x-bind:class="
                state
                    ? '{{
                        match ($onColor) {
                            'gray' => 'fi-color-gray bg-gray-200 dark:bg-gray-700',
                            default => 'fi-color-custom bg-custom-600',
                        }
                    }}'
                    : '{{
                        match ($offColor) {
                            'gray' => 'fi-color-gray bg-gray-200 dark:bg-gray-700',
                            default => 'fi-color-custom bg-custom-600',
                        }
                    }}'
            "
          x-bind:style="
                state
                    ? '{{
                        \Filament\Support\get_color_css_variables(
                            $onColor,
                            shades: [600],
                            alias: 'forms::components.toggle.on',
                        )
                    }}'
                    : '{{
                        \Filament\Support\get_color_css_variables(
                            $offColor,
                            shades: [600],
                            alias: 'forms::components.toggle.off',
                        )
                    }}'
            "
          {{
              $attributes
                  ->merge([
                      'aria-checked' => 'false',
                      'autofocus' => false,
                      'disabled' => false,
                      'id' => '',
                      'role' => 'switch',
                      'type' => 'button',
                      'wire:loading.attr' => 'disabled',
                      'wire:target' => $statePath,
                  ], escape: false)
                  ->class(['fi-fo-toggle relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent outline-none transition-colors duration-200 ease-in-out disabled:pointer-events-none disabled:opacity-70'])
          }}
  >
    <span
            class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            x-bind:class="{
                    'translate-x-5 rtl:-translate-x-5': state,
                    'translate-x-0': ! state,
                }"
    >
      <span
              class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
              aria-hidden="true"
              x-bind:class="{
                        'opacity-0 ease-out duration-100': state,
                        'opacity-100 ease-in duration-200': ! state,
                    }"
      >
      </span>
      <span
              class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
              aria-hidden="true"
              x-bind:class="{
                        'opacity-100 ease-in duration-200': state,
                        'opacity-0 ease-out duration-100': ! state,
                    }"
      >
      </span>
    </span>
  </button>
</div>