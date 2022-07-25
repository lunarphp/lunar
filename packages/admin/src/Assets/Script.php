<?php

namespace GetCandy\Hub\Assets;

class Script extends Asset
{
    /**
     * Get asset url.
     *
     * @return string
     */
    public function url(): string
    {
        if (! $this->isRemote()) {
            return route('hub.assets.scripts', $this->name);
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
            'Content-Type' => 'application/javascript',
        ];
    }
}
