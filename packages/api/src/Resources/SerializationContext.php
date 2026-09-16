<?php

namespace Lunar\Api\Resources;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Api\Query\IncludeTree;
use Lunar\Api\Query\Query;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Core\DataObjects\StorefrontContext;

/**
 * Everything serialisation needs, and nothing from the HTTP request itself:
 * controllers build one from the request, the webhook dispatcher and console
 * build one from config, so a resource serialises identically in all three.
 */
final class SerializationContext
{
    /**
     * @param  array<string, array<int, string>>  $fields  requested sparse fieldsets, keyed by resource type
     */
    public function __construct(
        public readonly SurfaceRegistry $registry,
        public readonly array $fields = [],
        public readonly IncludeTree $includes = new IncludeTree,
        public readonly Translations $translations = Translations::Map,
        public readonly ?string $locale = null,
        public readonly ?StorefrontContext $storefront = null,
        public readonly Authenticatable|Authorizable|null $principal = null,
    ) {}

    public function surface(): string
    {
        return $this->registry->surface;
    }

    public function version(): string
    {
        return $this->registry->version;
    }

    public function locale(): string
    {
        return $this->locale ?? app()->getLocale();
    }

    /**
     * The sparse fieldset requested for a type, or null for every field.
     *
     * @return array<int, string>|null
     */
    public function fieldsFor(string $type): ?array
    {
        return $this->fields[$type] ?? null;
    }

    public function can(string $ability): bool
    {
        if (! $this->principal instanceof Authorizable) {
            return false;
        }

        return $this->principal->can($ability);
    }

    /** The context for serialising one requested include's related resources. */
    public function descend(string $include): self
    {
        return new self(
            $this->registry,
            $this->fields,
            $this->includes->child($include),
            $this->translations,
            $this->locale,
            $this->storefront,
            $this->principal,
        );
    }

    public function withQuery(Query $query): self
    {
        return new self(
            $this->registry,
            $query->fields,
            $query->includes,
            $this->translations,
            $this->locale,
            $this->storefront,
            $this->principal,
        );
    }

    /**
     * Serialise models with another registered resource, for fields that embed
     * a child collection outright (cart lines, order lines, addresses).
     *
     * @param  class-string<resource>  $resource
     * @param  Model|iterable<Model>|null  $models
     * @return array<string, mixed>|array<int, array<string, mixed>>|null
     */
    public function serialize(string $resource, Model|iterable|null $models): ?array
    {
        $definition = $this->registry->definition($resource);

        if ($models === null) {
            return null;
        }

        if ($models instanceof Model) {
            return $definition->serialize($models, $this);
        }

        return $definition->serializeMany($models, $this);
    }
}
