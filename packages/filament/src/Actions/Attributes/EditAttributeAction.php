<?php

namespace Lunar\Filament\Actions\Attributes;

use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Attributes\UpdatesAttribute;
use Lunar\Filament\Schemas\Attribute\AttributeForm;
use Lunar\Filament\Support\Resolver;

/**
 * Edit an attribute through the core UpdatesAttribute action (spec 0063),
 * hydrating the form via AttributeForm::mutateDataForFill so modal edits and
 * the admin's edit page fill and persist identically.
 */
class EditAttributeAction extends EditAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mutateRecordDataUsing(function (array $data, Model $record): array {
            return Resolver::resolve(AttributeForm::class)::mutateDataForFill($record, $data);
        });

        $this->using(function (Model $record, array $data): Model {
            return app(UpdatesAttribute::class)->execute($record, $data);
        });
    }
}
