<div class="grid gap-1">
    <span>{{ __('lunarpanel::components.activity-log.partials.tags.updated') }}</span>

    @if(filled($added))
        <div class="flex items-center gap-2">
            <span class="text-xs font-normal">
                {{ __('lunarpanel::components.activity-log.partials.tags.added') }}
            </span>
            
            <div class="flex gap-1">
                @foreach($added as $tag)
                    <x-filament::badge :color="\Filament\Support\Colors\Color::Green" size="sm">
                        {{ $tag }}
                    </x-filament::badge>
                @endforeach
            </div>
        </div>
    @endif

    @if(filled($removed))
        <div class="flex items-center gap-2">
            <span class="text-xs font-normal">
                {{ __('lunarpanel::components.activity-log.partials.tags.removed') }}
            </span>
            
            <div class="flex gap-1">
                @foreach($removed as $tag)
                    <x-filament::badge :color="\Filament\Support\Colors\Color::Red" size="sm">
                        {{ $tag }}
                    </x-filament::badge>
                @endforeach
            </div>
        </div>
    @endif
</div>
