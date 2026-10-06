<?php

namespace Lunar\Core\Models\Concerns;

use ReflectionClass;
use Spatie\LaravelBlink\BlinkFacade as Blink;

trait CachesProperties
{
    public static function bootCachesProperties()
    {
        static::retrieved(function ($model) {
            $model->restoreProperties();
        });
    }

    public function refresh()
    {
        parent::refresh();

        $ro = new ReflectionClass($this);

        foreach ($this->cachableProperties as $property) {
            $defaultValue = $ro->getProperty($property)->getDefaultValue();

            $this->{$property} = $defaultValue;
        }

        return $this;
    }

    /**
     * Array access prefers a declared calculated property over an attribute of
     * the same name, so `$line['total']` and `->pluck('total')` (which read
     * models through ArrayAccess) return the PriceValue, as `$line->total`
     * does, rather than the persisted `total` column.
     */
    public function offsetExists($offset): bool
    {
        if ($this->isCachableProperty($offset)) {
            return isset($this->{$offset});
        }

        return parent::offsetExists($offset);
    }

    public function offsetGet($offset): mixed
    {
        if ($this->isCachableProperty($offset)) {
            return $this->{$offset};
        }

        return parent::offsetGet($offset);
    }

    protected function isCachableProperty(mixed $key): bool
    {
        return is_string($key) && in_array($key, $this->cachableProperties, true);
    }

    /**
     * Returns a unique key for the cache.
     *
     * @return string
     */
    protected function cachePropertiesPrefix()
    {
        return get_class($this).$this->id.'_';
    }

    /**
     * Cache properties for reuse in same request.
     *
     * @return void
     */
    public function cacheProperties()
    {
        foreach ($this->cachableProperties as $property) {
            Blink::put($this->cachePropertiesPrefix().$property, $this->{$property});
        }

        return $this;
    }

    /**
     * Restores properties from the same request.
     *
     * @return void
     */
    public function restoreProperties()
    {
        foreach ($this->cachableProperties as $property) {
            if (Blink::has($this->cachePropertiesPrefix().$property)) {
                $this->{$property} = Blink::get($this->cachePropertiesPrefix().$property);
            }
        }
    }
}
