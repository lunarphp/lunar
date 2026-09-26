<?php

namespace Lunar\Api\OpenApi;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * A generated OpenAPI document. `tapDocument()` closures receive it before
 * serialisation and may read or `set()` any path.
 *
 * @implements Arrayable<string, mixed>
 */
final class Document implements Arrayable, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $document
     */
    public function __construct(private array $document) {}

    /** @return array<string, mixed> */
    public function paths(): array
    {
        return $this->document['paths'] ?? [];
    }

    /** @return array<string, mixed> */
    public function components(): array
    {
        return $this->document['components'] ?? [];
    }

    /** @return array<int, array<string, mixed>> */
    public function tags(): array
    {
        return $this->document['tags'] ?? [];
    }

    /**
     * Read a value by dotted path, or by a list of segments when a key itself
     * contains a dot (`['paths', '/products/{id}', 'get']`).
     *
     * @param  string|array<int, string>  $path
     */
    public function get(string|array $path, mixed $default = null): mixed
    {
        $value = $this->document;

        foreach ($this->segments($path) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @param  string|array<int, string>  $path
     */
    public function set(string|array $path, mixed $value): self
    {
        $target = &$this->document;

        foreach ($this->segments($path) as $segment) {
            if (! is_array($target)) {
                $target = [];
            }

            if (! array_key_exists($segment, $target)) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $value;

        return $this;
    }

    /**
     * @param  string|array<int, string>  $path
     */
    public function forget(string|array $path): self
    {
        $segments = $this->segments($path);
        $last = array_pop($segments);
        $parent = &$this->document;

        foreach ($segments as $segment) {
            if (! is_array($parent) || ! array_key_exists($segment, $parent)) {
                return $this;
            }

            $parent = &$parent[$segment];
        }

        if (is_array($parent)) {
            unset($parent[$last]);
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->document;
    }

    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->document, $flags | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->document;
    }

    /**
     * @param  string|array<int, string>  $path
     * @return array<int, string>
     */
    private function segments(string|array $path): array
    {
        return is_array($path) ? array_values($path) : explode('.', $path);
    }
}
