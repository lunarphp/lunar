<?php

namespace Lunar\Admin\Support\Actions\Collections;

use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Actions\Traits\CreatesChildCollections;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Models\Collection;

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

        $this->form([
            TranslatedText::make('name')->required(),
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
