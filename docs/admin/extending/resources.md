# Extending Resources

You can add and change the behaviour of existing Filament resources. This might be useful if you wish to add a button for 
additional custom functionality.

Much like extending pages, to extend a resource you need to create and register an extension.

For example, the code below will register a custom extension called `MyProductResourceExtension` for the `ProductResource` Filament resource.

```php
use Lunar\Admin\Support\Facades\LunarPanel;

LunarPanel::registerExtension(new MyProductResourceExtension, \Lunar\Panel\Filament\Resources\ProductResource::class);
```

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
