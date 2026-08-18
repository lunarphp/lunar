<?php

namespace Lunar\Core\Actions\Attributes;

use Lunar\Core\Contracts\Actions\Attributes\DeletesAttribute;
use Lunar\Core\Exceptions\AttributeActionException;
use Lunar\Core\Models\Attribute;

/**
 * Delete an attribute. System attributes are kept — Lunar itself relies on
 * them. The model's deleting hook removes the attribute's model-type links
 * and product type references.
 */
class DeleteAttribute implements DeletesAttribute
{
    public function execute(Attribute $attribute): void
    {
        if ($attribute->system) {
            throw new AttributeActionException('Cannot delete a system attribute.');
        }

        $attribute->delete();
    }
}
