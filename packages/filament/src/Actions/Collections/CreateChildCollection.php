<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Filament\Forms\Components\TranslatedText;
use Lunar\Filament\Support\Concerns\CreatesChildCollections;

/**
 * Creates a child collection from an explicit `id` argument supplied by the
 * caller — paired with widgets/tools (e.g. a tree view) that sit outside a
 * Filament table relationship and resolve the parent collection themselves.
 *
 * For the in-table-relationship variant used by Filament resource pages,
 * see {@see \Lunar\Filament\Tables\Actions\Collections\CreateChildCollection}.
 */
class CreateChildCollection extends CreateAction
{
    use CreatesChildCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->action(function (?Model $model, array $arguments, array $data): void {
            $parent = Collection::find($arguments['id']);

            $this->createChildCollection($parent, $data['name']);

            $this->success();
        });

        $attribute = Attribute::where('attribute_type', '=', Collection::morphName())
            ->where('handle', '=', 'name')->first();

        $formInput = TextInput::class;

        if ($attribute?->type == \Lunar\Core\FieldTypes\TranslatedText::class) {
            $formInput = TranslatedText::class;
        }

        $this->schema([
            $formInput::make('name')->required(),
        ]);

        $this->label(
            __('lunar-filament::actions.collections.create_child.label')
        );

        $this->createAnother(false);

        $this->modalHeading(
            __('lunar-filament::actions.collections.create_child.label')
        );
    }
}
