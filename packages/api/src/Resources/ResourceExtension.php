<?php

namespace Lunar\Api\Resources;

use Closure;

/**
 * What an add-on implements to widen a built-in resource: extra fields,
 * includes, filters, sorts and endpoints. Mirrors the panel's
 * `SectionExtension` / `TableExtension` so an add-on author learns one pattern.
 */
abstract class ResourceExtension
{
    /** @return class-string<resource> the built-in resource this extends */
    abstract public function extends(): string;

    /** @return array<int, Field> */
    public function fields(): array
    {
        return [];
    }

    /** @return array<int, Embed> */
    public function includes(): array
    {
        return [];
    }

    /** @return array<int, Filter> */
    public function filters(): array
    {
        return [];
    }

    /** @return array<int, Sort> */
    public function sorts(): array
    {
        return [];
    }

    /** Extra endpoints registered under the resource's route prefix. */
    public function routes(): ?Closure
    {
        return null;
    }

    /**
     * Relations to load on index/show.
     *
     * @return array<int|string, mixed>
     */
    public function eagerLoad(): array
    {
        return [];
    }
}
