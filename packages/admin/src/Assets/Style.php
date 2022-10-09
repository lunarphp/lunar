<?php

namespace Lunar\Hub\Assets;

class Style extends Asset
{
    /**
     * Get asset url.
     *
     * @return string
     */
    public function url(): string
    {
        if (! $this->isRemote()) {
            return route('hub.assets.styles', $this->name);
        }

        return $this->path;
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
