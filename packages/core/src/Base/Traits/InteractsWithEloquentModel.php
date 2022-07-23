<?php

namespace GetCandy\Base\Traits;

use GetCandy\Base\ModelFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Traits\ForwardsCalls;

trait InteractsWithEloquentModel
{
    use ForwardsCalls;

    /**
     * Handle dynamic and static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! in_array(get_called_class(), ModelFactory::getBaseModelClasses()) || $this->excludeWhen($method)) {
            return parent::__call($method, $parameters);
        }

        $model = ModelFactory::getInstance()->getRegisteredModel(get_called_class());

        return $this->forwardCallTo($model, $method, $parameters);
    }

    /**
     * Exclude when method matches builder or livewire hooks.
     *
     * @param  string  $method
     * @return bool
     */
    protected function excludeWhen(string $method): bool
    {
        $builderMethods = get_class_methods(new Builder($this->getConnection()));
        $livewireHooks = [
            'boot',
            'hydrate',
            'mount',
            'booted',
            'updating',
            'updated',
            'rendering',
            'rendered',
            'dehydrate',
        ];

        return in_array($method, array_merge($builderMethods, $livewireHooks));
    }
}
