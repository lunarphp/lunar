# Extending Resources

## MyProductResourceExtension

An example of extending the ProductResource

```php
class MyProductResourceExtension extends \Lunar\Panel\Support\Extending\ResourceExtension
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
    
    public function getRelations(array $managers) : array
    {
        return [
            ...$managers,
            // This is just a standard Filament relation manager.
            // see https://filamentphp.com/docs/3.x/panels/resources/relation-managers#creating-a-relation-manager
            MyCustomProductRelationManager::class,
        ];
    }
}

// Typically placed in your AppServiceProvider file...
LunarPanel::registerExtension(new MyCreateExtension, CreateProduct::class);
```
