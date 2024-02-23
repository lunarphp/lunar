
# Overview

The Lunar Panel is highly customizable, you can add and change the behaviour of existing Filament resources. This might be useful if you wish to add a button for
additional custom functionality. 


##  Extending Pages

To extend a page you need to create and register an extension.

For example, the code below will register a custom extension called `MyEditExtension` for the `EditProduct` Filament page.

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Panel\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Admin\Filament\Resources\Pages\MyEditExtension;

LunarPanel::registerExtension(new MyEditExtension, EditProduct::class);

```

For example, the code below will register a custom extension called `MyListExtension` for the `ListProduct` Filament page.

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Panel\Filament\Resources\ProductResource\Pages\ListProduct;
use App\Admin\Filament\Resources\Pages\MyEditExtension;

LunarPanel::registerExtension(new MyListExtension, ListProduct::class);

```

##  Extending Resources
Much like extending pages, to extend a resource you need to create and register an extension.

For example, the code below will register a custom extension called `MyProductResourceExtension` for the `ProductResource` Filament resource.

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Panel\Filament\Resources\ProductResource;
use App\Admin\Filament\Resources\MyProductResourceExtension;

LunarPanel::registerExtension(new MyProductResourceExtension, ProductResource::class);
```

##  Register multiple extensions
Lunar includes several methods, you can use registerExtensions to register multiple class

```php
LunarPanel::registerExtensions([
    MyEditExtension::class => EditProduct::class
    MyProductResourceExtension::class => ProductResource::class
    // ...
]);

```````

## Extendable resources

All Lunar panel resources are extendable. This means you can now add your own functionality or change out existing behaviour.

```php
use Lunar\Panel\Filament\Resources\ActivityResource;
use Lunar\Panel\Filament\Resources\AttributeGroupResource;
use Lunar\Panel\Filament\Resources\BrandResource;
use Lunar\Panel\Filament\Resources\ChannelResource;
use Lunar\Panel\Filament\Resources\CollectionGroupResource;
use Lunar\Panel\Filament\Resources\CollectionResource;
use Lunar\Panel\Filament\Resources\CurrencyResource;
use Lunar\Panel\Filament\Resources\CustomerGroupResource;
use Lunar\Panel\Filament\Resources\CustomerResource;
use Lunar\Panel\Filament\Resources\DiscountResource;
use Lunar\Panel\Filament\Resources\LanguageReousrce;
use Lunar\Panel\Filament\Resources\OrderResource;
use Lunar\Panel\Filament\Resources\ProductOptionrResource;
use Lunar\Panel\Filament\Resources\ProductResource;
use Lunar\Panel\Filament\Resources\ProductResource;
use Lunar\Panel\Filament\Resources\ProductTypeResource;
use Lunar\Panel\Filament\Resources\ProductVariantResource;
use Lunar\Panel\Filament\Resources\StaffResource;
use Lunar\Panel\Filament\Resources\TagResource;
use Lunar\Panel\Filament\Resources\TaxClassResource;
use Lunar\Panel\Filament\Resources\TaxZoneResource;
```
