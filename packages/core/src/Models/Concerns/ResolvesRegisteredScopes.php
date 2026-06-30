<?php

namespace Lunar\Core\Models\Concerns;

use Lunar\Core\Models\Builders\Builder;

/**
 * Resolves consumer-registered local scopes in an Eloquent builder's `__call`,
 * deferring to native scopes, macros, and query methods. Shared by the default
 * Lunar builder and any model-specific builder (e.g. the nested-set builder) so
 * registered scopes work uniformly regardless of a model's builder class.
 */
trait ResolvesRegisteredScopes
{
    /**
     * {@inheritdoc}
     */
    public function __call($method, $parameters)
    {
        $scope = Builder::resolveScope($this->getModel()::class, $method);

        // A native scope, macro, or query method always wins; the registry
        // only fills the gap for scopes registered from outside the model.
        if ($scope !== null
            && ! $this->hasNamedScope($method)
            && ! $this->hasMacro($method)
            && ! static::hasGlobalMacro($method)) {
            return $scope($this, ...$parameters) ?? $this;
        }

        return parent::__call($method, $parameters);
    }
}
