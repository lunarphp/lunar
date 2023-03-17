<?php

namespace Lunar\Base\Traits;

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
     * Returns a unique key for the cache.
     *
     * @return void
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
