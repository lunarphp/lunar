<?php

namespace Lunar\Filament\Schemas\ProductType;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Forms\Components\AttributeSelector;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ProductTypeForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
                Tabs::make('Attributes')->tabs([
                    Tab::make(__('lunar-filament::producttype.tabs.product_attributes.label'))
                        ->schema([
                            AttributeSelector::make('attributeMapping')
                                ->withType(Product::morphName())
                                ->relationship(name: 'attributeMapping')
                                ->label('')
                                ->columnSpan(2),
                        ]),
                    Tab::make(__('lunar-filament::producttype.tabs.variant_attributes.label'))
                        ->schema([
                            AttributeSelector::make('attributeMapping')
                                ->withType(ProductVariant::morphName())
                                ->relationship(name: 'attributeMapping')
                                ->label('')
                                ->columnSpan(2),
                        ])->visible(
                            config('lunar.filament.enable_variants', true)
                        ),
                ])->columnSpan(2),
            ]),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::producttype.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }
}
