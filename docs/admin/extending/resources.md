# Extending Resources

## MyProductResourceExtension

An example of extending the ProductResource

```php
class MyProductResourceExtension extends \Lunar\Admin\Support\Extending\ResourceExtension
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
    
    public function getPages(array $pages) : array
    {
        return [
            ...$pages,
            // This is just a standard Filament page
            // see https://filamentphp.com/docs/3.x/panels/pages#creating-a-page
            MyPage::class,
        ];
    }
    
    public function getSubNavigation(array $nav) : array
    {
        return [
            ...$nav,
            // This is just a standard Filament page
            // see https://filamentphp.com/docs/3.x/panels/pages#creating-a-page
            MyPage::class,
        ];
    }
}

// Typically placed in your AppServiceProvider file...
LunarPanel::extensions([
    \Lunar\Admin\Filament\Resources\ProductResource::class => MyProductResourceExtension::class,
]);
```
