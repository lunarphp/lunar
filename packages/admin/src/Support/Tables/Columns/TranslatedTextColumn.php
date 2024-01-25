<?php

namespace Lunar\Admin\Support\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslatedTextColumn extends TextColumn
{
    protected bool $attributeData = false;

    protected string $fieldHydrated = '';

    protected function setUp(): void
    {
        parent::setUp();

        $name = $this->getName();
        
        $this->formatStateUsing(static function (Model $record) use ($name) {
            return $record->translate($name);
        });
    }

    public function fieldHydrated(string $fieldHydrated): static
    {
        $this->fieldHydrated = $fieldHydrated;

        return $this;
    }

    public function limitedTooltip(): static
    {
        $attributeData = $this->getAttributeData();

        $name = $this->getFieldHydrated();

        $this->tooltip(function (TextColumn $column, Model $record) use ($name, $attributeData): ?string {
            $state = $attributeData ? $record->translateAttribute($name) : $record->translate($name);

            if (strlen($state) <= $column->getCharacterLimit()) {
                return null;
            }

            // Only render the tooltip if the column contents exceeds the length limit.
            return $state;
        });

        return $this;
    }

    public function attributeData(bool $attributeData): static
    {
        $this->attributeData = $attributeData;

        $this->fieldHydrated(Str::replace('attribute_data.', '', $this->getName()));

        $name = $this->getFieldHydrated();

        $this->formatStateUsing(static function (Model $record) use ($name, $attributeData) {
            return $attributeData ? $record->translateAttribute($name) : $record->translate($name);
        });

        return $this;
    }

    public function getFieldHydrated(): string
    {
        return $this->fieldHydrated;
    }

    public function getAttributeData(): bool
    {
        return $this->attributeData;
    }
}
