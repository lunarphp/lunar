<?php

namespace Lunar\Hub\Assets;

use DateTime;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

abstract class Asset implements Responsable
{
    /**
     * Name of the asset.
     */
    protected string $name;

    /**
     * Path of the Asset.
     *
     * @var string|null
     */
    protected string $path;

    /**
     * Determine wether Asset is remote or not.
     *
     * @var bool
     */
    protected $remote;

    /**
     * Construct a new Asset instance.
     *
     * @param  bool|null  $remote
     */
    public function __construct(string|self $name, string $path, $remote = null)
    {
        if ($name instanceof self) {
            $this->name = $name->name();
            $this->path = $name->path();
            $this->remote = $name->isRemote();

            return;
        }

        if (is_null($remote)) {
            $remote = Str::startsWith($path, ['http://', 'https://', '://']);
        }

        $this->name = $name;

        $this->path = $path;

        $this->remote = $remote;
    }

    /**
     * Make a remote URL.
     */
    public static function remote(string $path): static
    {
        return new static(md5($path), $path, true);
    }

    /**
     * Get asset name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get asset path.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Determine if URL is remote.
     */
    public function isRemote(): bool
    {
        return $this->remote;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
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
     */
    abstract public function url(): string;

    /**
     * Get response headers.
     *
     * @return array<string, string>
     */
    abstract public function toResponseHeaders(): array;
}
