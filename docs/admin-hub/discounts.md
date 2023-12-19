# Discounts

If you have [registered your own discount types](/core/extending/discounts) in the core, you will likely want to provide an interface so authenticated staff members can add the data required.

:::warning
You need to make sure you have [registered your discount](/core/extending/discounts) with the Lunar core beforehand.
:::

```php
use Lunar\Hub\Facades\DiscountTypes;
use App\Http\Livewire\Components\CustomDiscountComponent;
use App\DiscountTypes\MyCustomDiscountType;

DiscountTypes::register(MyCustomDiscountType::class, CustomDiscountComponent::class);
```

The component UI should then appear when the user has chosen the custom discount type.

Create a Livewire component to handle your custom discount type.

```php
<?php

namespace App\Http\Livewire\Components;

use Lunar\Facades\DB;
use Lunar\Models\Discount;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\AbstractDiscountType;

class CustomDiscountComponent extends AbstractDiscountType
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * {@ineheritDoc}.
     */
    public function rules()
    {
        return [
            'discount.data' => 'array',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        parent::mount();
    }

    public function getValidationMessages()
    {
        return [];
    }
    
    /**
     * Handle when the discount data is updated.
     *
     * @return void
     */
    public function updatedDiscountData()
    {
        $this->emitUp('discountData.updated', $this->discount->data);
    }

    /**
     * Save the product discount.
     *
     * @return void
     */
    public function save($discountId)
    {
        $this->discount = Discount::find($discountId);
            
        DB::transaction(function () {
            // ...
        });
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('my-custom-discount-ui')
            ->layout('adminhub::layouts.base');
    }
}
```
