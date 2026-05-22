<?php

namespace Lunar\Admin\Support\Actions\Collections;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Actions\Traits\CreatesChildCollections;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Filament\Forms\Components\TranslatedText;

/**
 * Creates a child collection from an explicit `id` argument supplied by the
 * caller — used by the admin shell's `CollectionTreeView` widget, which sits
 * outside a Filament table relationship and resolves the parent itself.
 *
 * For the in-table-relationship variant used by the children page, see
 * {@see \Lunar\Filament\Tables\Actions\Collections\CreateChildCollection}.
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
            __('lunarpanel::actions.collections.create_child.label')
        );

        $this->createAnother(false);

        $this->modalHeading(
            __('lunarpanel::actions.collections.create_child.label')
        );
    }
}
