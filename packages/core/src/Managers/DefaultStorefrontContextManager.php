<?php

namespace Lunar\Core\Managers;

use Closure;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\Contracts\DefaultStorefrontContext;
use Lunar\Core\DataObjects\StorefrontContext;

class DefaultStorefrontContextManager implements DefaultStorefrontContext
{
    /**
     * @var (Closure(): StorefrontContext)|null
     */
    protected ?Closure $resolver = null;

    public function __construct(
        protected ResolvesStorefrontContext $resolveStorefrontContext,
    ) {}

    public function resolveUsing(Closure $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function hasResolver(): bool
    {
        return $this->resolver !== null;
    }

    public function get(): StorefrontContext
    {
        if ($this->resolver) {
            return ($this->resolver)();
        }

        return $this->resolveStorefrontContext->execute();
    }
}
