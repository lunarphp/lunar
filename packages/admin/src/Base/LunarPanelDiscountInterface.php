<?php

namespace Lunar\Admin\Base;

interface LunarPanelDiscountInterface
{
    /**
     * Return the schema to use in the Lunar admin panel
     */
    public function lunarPanelSchema(): array;

    /**
     * Mutate the model data before displaying it in the admin form.
     */
    public function lunarPanelOnFill(array $data): array;

    /**
     * Mutate the form data before saving it to the discount model.
     */
    public function lunarPanelOnSave(array $data): array;
}
