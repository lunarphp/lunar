<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Brand;

/**
 * Pick a Brand. Defaults to relationship mode (`brand`/`name`) which
 * matches the canonical usage on Product, Collection, etc. — call sites
 * can override `->relationship(...)` or `->getSearchResultsUsing(...)`
 * when picking by foreign key in detached contexts.
 */
class BrandSelect extends Select
{
    protected bool $withInlineCreate = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.brand.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.brand.placeholder'));
        $this->relationship('brand', 'name');
        $this->searchable();
        $this->preload();
        $this->createOptionForm(fn (): array => $this->withInlineCreate
            ? [TextInput::make('name')->required()]
            : []);
    }

    public function withoutInlineCreate(bool $condition = true): static
    {
        $this->withInlineCreate = ! $condition;

        if ($this->withInlineCreate) {
            $this->createOptionForm([TextInput::make('name')->required()]);
        } else {
            $this->createOptionForm([]);
        }

        return $this;
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Brand::class;
    }
}
