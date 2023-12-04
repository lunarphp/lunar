@php    
    $content = $getContent();
    $state = $getState() ?? $content;
    $icon = $getIcon($state) ?? $getDefaultIcon();
    $color = $getColor($state) ?? 'primary';
@endphp

<x-lunarpanel::alert 
    :color="$color"
    :content="$content"
    :icon="$icon"
    class="p-4"
/>
