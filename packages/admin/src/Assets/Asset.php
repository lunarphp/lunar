<?php

namespace GetCandy\Hub\Assets;

use DateTime;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

abstract class Asset implements Responsable
{
    /**
     * Name of the asset.
     *
     * @var string
     */
    protected $name;

    /**
     * Path of the Asset.
     *
     * @var string|null
     */
    protected $path;

    /**
     * Construct a new Asset instance.
     *
     * @param string|self $name
     * @param string|null $path
     */
    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * Get asset name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get asset path.
     *
     * @return string|null
     */
    public function path(): ?string
    {
        return $this->path;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        return response(
            file_get_contents($this->path),
            200,
            $this->toResponseHeaders(),
        )->setLastModified(DateTime::createFromFormat('U', (string) filemtime($this->path)));
    }

    /**
     * Get asset url.
     *
     * @return string
     */
    abstract public function url(): string;

    /**
     * Get response headers.
     *
     * @return array<string, string>
     */
    abstract public function toResponseHeaders(): array;
}
