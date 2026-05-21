<?php

namespace Lunar\Admin\Filament\Resources\ProductTypeResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Forms\Components\AttributeSelector;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

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
                    Tab::make(__('lunarpanel::producttype.tabs.product_attributes.label'))
                        ->schema([
                            AttributeSelector::make('mappedAttributes')
                                ->withType(Product::morphName())
                                ->relationship(name: 'mappedAttributes')
                                ->label('')
                                ->columnSpan(2),
                        ]),
                    Tab::make(__('lunarpanel::producttype.tabs.variant_attributes.label'))
                        ->schema([
                            AttributeSelector::make('mappedAttributes')
                                ->withType(ProductVariant::morphName())
                                ->relationship(name: 'mappedAttributes')
                                ->label('')
                                ->columnSpan(2),
                        ])->visible(
                            config('lunar.panel.enable_variants', true)
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
            ->label(__('lunarpanel::producttype.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }
}
