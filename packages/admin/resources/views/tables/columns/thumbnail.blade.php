<div {{ $attributes->merge($getExtraAttributes())->class(['px-4 py-3 filament-tables-image-column']) }}>
    @php
        $height = $getHeight();
        $width = $getWidth() ?? ($isRounded() ? $height : null);
        $thumbnail = $getState()
    @endphp

    <div
        style="
            {!! $height !== null ? "height: {$height};" : null !!}
            {!! $width !== null ? "width: {$width};" : null !!}
        "
        @class(['rounded-full overflow-hidden' => $isRounded()])
    >
        @if ($path = $thumbnail)
            <img
                src="{{ $path }}"
                style="
                    {!! $height !== null ? "height: {$height};" : null !!}
                    {!! $width !== null ? "width: {$width};" : null !!}
                "
                @class(['object-cover object-center' => $isRounded()])
                {{ $getExtraImgAttributeBag() }}
                loading="lazy"
            >
        @else
          <x-hub::icon ref="photograph"
           class="w-8 h-8 mx-auto text-gray-300" />
        @endif
    </div>
</div>
