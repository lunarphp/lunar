# Extending Relation Managers

## MyCustomerGroupPricingRelationManagerExtension

An example of extending the CustomerGroupPricingRelationManager

```php
class MyCustomerGroupPricingRelationManagerExtension extends \Lunar\Admin\Support\Extending\RelationManagerExtension
{
    public function extendForm(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([
            ...$form->getComponents(withHidden: true),
            
            \Filament\Forms\Components\TextInput::make('custom_column')
        ]);
    }
    
    public function extendTable(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table->columns([
            ...$table->getColumns(),
            \Filament\Tables\Columns\TextColumn::make('product_code')
        ]);
    }
}

// Typically placed in your AppServiceProvider file...
LunarPanel::extensions([
    \Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupPricingRelationManager::class => MyCustomerGroupPricingRelationManagerExtension::class,
]);
```
