<?php

namespace GetCandy\Hub\Assets;

class Style extends Asset
{
    /**
     * Get asset url.
     *
     * @return string
     */
    public function url(): string
    {
        return route('hub.assets.styles', $this->name);
    }

    /**
     * Get the response headers for the asset.
     *
     * @return array<string, string>
     */
    public function toResponseHeaders(): array
    {
        return [
            'Content-Type' => 'text/css',
        ];
    }
}
