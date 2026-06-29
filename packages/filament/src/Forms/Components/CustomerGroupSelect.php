<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\CustomerGroup;

class CustomerGroupSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.customer_group.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.customer_group.placeholder'));
        $this->relationship('customerGroup', 'name');
        $this->preload();
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return CustomerGroup::class;
    }
}
