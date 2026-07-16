<?php

namespace Lunar\Panel\Sections;

class SectionRegistry
{
    /** @var array<string, Section> */
    protected array $sections = [];

    /** @var array<string, SectionExtension[]> */
    protected array $extensions = [];

    public function register(Section $section): void
    {
        $this->sections[$section->key()] = $section;
    }

    public function registerExtension(SectionExtension $extension): void
    {
        $this->extensions[$extension->extends()][] = $extension;
    }

    /** @return array<string, SectionExtension[]> */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /** @return array<string, Section> */
    public function all(): array
    {
        return $this->sections;
    }

    public function get(string $key): ?Section
    {
        return $this->sections[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->sections[$key]);
    }
}
