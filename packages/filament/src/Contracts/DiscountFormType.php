<?php

namespace Lunar\Filament\Contracts;

/**
 * Implemented by discount types that contribute their own Filament schema,
 * fill/save mutators, and relation managers to the bridge's discount form.
 */
interface DiscountFormType
{
    /**
     * Schema components to inject into the discount form.
     */
    public function lunarPanelSchema(): array;

    /**
     * Mutate the model data before displaying it in the form.
     */
    public function lunarPanelOnFill(array $data): array;

    /**
     * Mutate the form data before saving it to the discount model.
     */
    public function lunarPanelOnSave(array $data): array;

    /**
     * Relation managers to attach to the discount form.
     */
    public function lunarPanelRelationManagers(): array;
}
