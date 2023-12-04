@props([
    'content' => null,
    'icon' => null,
    'color' => 'primary',
])

<div
    {{ 
        $attributes
            ->merge(['class' => 'p-2'])
            ->class([
                'rounded-md ring-1',
                match ($color) {
                    'gray' => 'bg-gray-50 text-gray-600 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20',
                    default => 'bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30',
                },
            ])
            ->style([
                \Filament\Support\get_color_css_variables(
                    $color,
                    shades: [
                        50,
                        400,
                        600,
                        ...($icon) ? [500] : [],
                    ],
                ) => $color !== 'gray',
            ])
    }}
>
    <div class="flex items-center">
        <div class="flex-shrink-0">
            
                @if($icon)
                    <x-filament::icon
                        :icon="$icon"
                        @class([
                            'w-7 h-7',
                            match ($color) {
                                'gray' => 'text-gray-400 dark:text-gray-500',
                                default => 'text-custom-500',
                            },
                        ])
                    />
                @endif                
            
        </div>
        <div class="flex-1 ml-3 md:flex md:justify-between w-full">
            <div class="text-sm">
                {{ $content }}
            </div>
        </div>
    </div>
</div>
