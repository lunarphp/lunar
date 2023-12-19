<?php

namespace Lunar\Hub\Base;

use Livewire\Component;

interface DiscountTypesInterface
{
    /**
     * Register a discount type to be used when editing.
     *
     * @param  string  $discountTypeClass
     * @param  string  $componentClass
     */
    public function register($discountType, $component): self;

    /**
     * Get the component for editing by it's given type.
     *
     * @param  string  $type
     */
    public function getComponent($type): ?Component;
}
