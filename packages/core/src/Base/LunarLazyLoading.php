<?php

namespace Lunar\Core\Base;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Exceptions\LunarLazyLoadingViolation;

class LunarLazyLoading
{
    /**
     * The user-supplied violation handler, if any.
     *
     * @var (callable(Model, string): void)|null
     */
    protected $violationHandler = null;

    /**
     * The user-supplied missing-attribute handler, if any.
     *
     * @var (callable(Model, string): void)|null
     */
    protected $missingAttributeHandler = null;

    /**
     * The user-supplied discarded-attribute handler, if any.
     *
     * @var (callable(Model, array<int, string>): void)|null
     */
    protected $discardedAttributeHandler = null;

    /**
     * Determine whether strict mode is enabled for Lunar models right now.
     */
    public function enabled(): bool
    {
        $value = config('lunar.database.prevent_lazy_loading', 'auto');

        if ($value === 'auto') {
            return ! app()->isProduction();
        }

        return (bool) $value;
    }

    /**
     * Register a custom callback for lazy-loading violations.
     *
     * @param  (callable(Model, string): void)|null  $callback
     */
    public function handleViolationUsing(?callable $callback): void
    {
        $this->violationHandler = $callback;
    }

    /**
     * Register a custom callback for accessing missing attributes.
     *
     * @param  (callable(Model, string): void)|null  $callback
     */
    public function handleMissingAttributeUsing(?callable $callback): void
    {
        $this->missingAttributeHandler = $callback;
    }

    /**
     * Register a custom callback for silently discarded attributes.
     *
     * @param  (callable(Model, array<int, string>): void)|null  $callback
     */
    public function handleDiscardedAttributeUsing(?callable $callback): void
    {
        $this->discardedAttributeHandler = $callback;
    }

    /**
     * Handle a Lunar model lazy-loading violation.
     */
    public function handleViolation(Model $model, string $relation): void
    {
        if ($this->violationHandler !== null) {
            call_user_func($this->violationHandler, $model, $relation);

            return;
        }

        throw new LunarLazyLoadingViolation($model, $relation);
    }

    /**
     * Handle access to a missing attribute on a Lunar model.
     */
    public function handleMissingAttribute(Model $model, string $key): void
    {
        if ($this->missingAttributeHandler !== null) {
            call_user_func($this->missingAttributeHandler, $model, $key);

            return;
        }

        throw new MissingAttributeException($model, $key);
    }

    /**
     * Handle silently discarded mass-assigned attributes on a Lunar model.
     *
     * @param  array<int, string>  $keys
     */
    public function handleDiscardedAttributes(Model $model, array $keys): void
    {
        if ($this->discardedAttributeHandler !== null) {
            call_user_func($this->discardedAttributeHandler, $model, $keys);

            return;
        }

        throw new MassAssignmentException(sprintf(
            'Add fillable property [%s] to allow mass assignment on [%s].',
            implode(', ', $keys),
            get_class($model)
        ));
    }
}
