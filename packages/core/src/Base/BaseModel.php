<?php

namespace Lunar\Core\Base;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Base\Traits\HasModelExtending;
use Lunar\Core\Facades\LunarLazyLoading;

abstract class BaseModel extends Model
{
    use HasModelExtending;

    /**
     * Create a new instance of the Model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('lunar.database.table_prefix').$this->getTable());

        if ($connection = config('lunar.database.connection')) {
            $this->setConnection($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolveCollectionFromAttribute()
    {
        $reflectionClass = new \ReflectionClass(static::class);

        $attributes = $reflectionClass->getAttributes(
            CollectedBy::class
        );

        if (! isset($attributes[0]) || ! isset($attributes[0]->getArguments()[0])) {
            return null;
        }

        return $attributes[0]->getArguments()[0];
    }

    /**
     * {@inheritdoc}
     */
    public function fill(array $attributes)
    {
        if ($this->shouldEnforceLunarStrictMode() && ! $this->totallyGuarded()) {
            $fillable = $this->fillableFromArray($attributes);
            $discarded = array_diff(array_keys($attributes), array_keys($fillable));

            if (! empty($discarded)) {
                LunarLazyLoading::handleDiscardedAttributes($this, array_values($discarded));
            }
        }

        return parent::fill($attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelationshipFromMethod($method)
    {
        if ($this->exists && ! $this->wasRecentlyCreated && $this->shouldEnforceLunarStrictMode()) {
            LunarLazyLoading::handleViolation($this, $method);
        }

        return parent::getRelationshipFromMethod($method);
    }

    /**
     * {@inheritdoc}
     */
    protected function throwMissingAttributeExceptionIfApplicable($key)
    {
        if ($this->exists && ! $this->wasRecentlyCreated && $this->shouldEnforceLunarStrictMode()) {
            LunarLazyLoading::handleMissingAttribute($this, $key);

            return null;
        }

        return parent::throwMissingAttributeExceptionIfApplicable($key);
    }

    /**
     * Whether Lunar-scoped strict mode should fire on this model right now.
     */
    protected function shouldEnforceLunarStrictMode(): bool
    {
        return LunarLazyLoading::enabled();
    }
}
