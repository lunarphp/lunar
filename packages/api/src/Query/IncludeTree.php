<?php

namespace Lunar\Api\Query;

/**
 * Requested includes as a tree: `brand,variants.values` becomes
 * `{brand: {}, variants: {values: {}}}`.
 */
final class IncludeTree
{
    /** @var array<string, IncludeTree> */
    private array $children = [];

    /** @param  array<int, string>  $paths  dotted include paths */
    public static function fromPaths(array $paths): self
    {
        $tree = new self;

        foreach ($paths as $path) {
            $tree->add($path);
        }

        return $tree;
    }

    public function add(string $path): void
    {
        $segments = explode('.', $path, 2);
        $name = $segments[0];

        $this->children[$name] ??= new self;

        if (isset($segments[1]) && $segments[1] !== '') {
            $this->children[$name]->add($segments[1]);
        }
    }

    /** @return array<int, string> */
    public function names(): array
    {
        return array_keys($this->children);
    }

    public function has(string $name): bool
    {
        return isset($this->children[$name]);
    }

    public function child(string $name): self
    {
        return $this->children[$name] ?? new self;
    }

    public function isEmpty(): bool
    {
        return $this->children === [];
    }

    /** Depth of the deepest path; an empty tree is 0. */
    public function depth(): int
    {
        $depth = 0;

        foreach ($this->children as $child) {
            $depth = max($depth, 1 + $child->depth());
        }

        return $depth;
    }

    /**
     * Every dotted path, for eager-load statements and links.
     *
     * @return array<int, string>
     */
    public function paths(string $prefix = ''): array
    {
        $paths = [];

        foreach ($this->children as $name => $child) {
            $path = $prefix === '' ? $name : "{$prefix}.{$name}";
            $paths[] = $path;
            $paths = array_merge($paths, $child->paths($path));
        }

        return $paths;
    }
}
