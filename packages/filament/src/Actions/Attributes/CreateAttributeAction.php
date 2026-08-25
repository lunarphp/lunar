<?php

namespace Lunar\Filament\Actions\Attributes;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Livewire\Component;
use Lunar\Core\Contracts\Actions\Attributes\CreatesAttribute;
use Lunar\Core\Models\Attribute;

/**
 * Create an attribute through the core CreatesAttribute action (spec 0063).
 * Mounted on an attribute group's relation manager the owner group supplies
 * `attribute_group_id`; anywhere else the form's group select does, and a
 * blank selection creates the attribute ungrouped.
 */
class CreateAttributeAction extends CreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->using(function (array $data, Component $livewire): Attribute {
            if ($livewire instanceof RelationManager) {
                $data['attribute_group_id'] = $livewire->getOwnerRecord()->getKey();
            }

            return app(CreatesAttribute::class)->execute($data);
        });
    }
}
