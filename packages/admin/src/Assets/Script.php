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
        $prefix = config('getcandy-hub.system.path', 'hub');

        return "/{$prefix}/scripts/{$this->name}";
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
