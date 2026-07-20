<?php

namespace Lunar\Core\Actions\Attributes;

use Lunar\Core\Contracts\Actions\Attributes\DeletesAttributeGroup;
use Lunar\Core\Exceptions\AttributeActionException;
use Lunar\Core\Models\AttributeGroup;

/**
 * Delete an attribute group. System groups are kept — Lunar itself relies on
 * them. Groups with attributes are also kept: move or delete the attributes
 * first, so none are silently orphaned.
 */
class DeleteAttributeGroup implements DeletesAttributeGroup
{
    public function execute(AttributeGroup $attributeGroup): void
    {
        if ($attributeGroup->system) {
            throw new AttributeActionException('Cannot delete a system attribute group.');
        }

        if ($attributeGroup->attributes()->exists()) {
            throw new AttributeActionException('Cannot delete an attribute group with attributes.');
        }

        $attributeGroup->delete();
    }
}
