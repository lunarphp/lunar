<?php

namespace Lunar\Base\Traits;

use Spatie\LaravelBlink\BlinkFacade as Blink;

trait CachesProperties
{
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
