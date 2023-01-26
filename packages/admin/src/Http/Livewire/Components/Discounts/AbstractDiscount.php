<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Lunar\Facades\Discounts;
use Lunar\Hub\Editing\DiscountTypes;
use Lunar\Hub\Http\Livewire\Traits\HasAvailability;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Brand;
use Lunar\Models\Collection as ModelsCollection;
use Lunar\Models\Currency;
use Lunar\Models\Discount;
use Lunar\Models\Product;

abstract class AbstractDiscount extends Component
{
    use WithLanguages;
    use Notifies;
    use HasAvailability;

    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * The brands to restrict the coupon for.
     *
     * @var array
     */
    public Collection $selectedBrands;

    /**
     * The collections to restrict the coupon for.
     *
     * @var array
     */
    public Collection $selectedCollections;
    
    /**
     * The products to restrict the coupon for.
     *
     * @var array
     */
    public Collection $selectedProducts;

    /**
     * The selected conditions
     *
     * @var array
     */
    public array $selectedConditions = [];

    /**
     * The selected rewards.
     *
     * @var array
     */
    public array $selectedRewards = [];

    /**
     * The current currency for editing
     *
     * @var Currency
     */
    public Currency $currency;

    /**
     * Define availability properties.
     *
     * @var array
     */
    public $availability = [];

    /**
     * Returns the currencies computed property.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrenciesProperty()
    {
        return Currency::get();
    }

    /**
     * {@inheritDoc}
     */
    public function dehydrate()
    {
        $this->emit('parentComponentErrorBag', $this->getErrorBag());
    }

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'discountData.updated' => 'syncDiscountData',
        'discount.conditions' => 'syncConditions',
        'discount.rewards' => 'syncRewards',
        'discount.purchasables' => 'syncPurchasables',
        'brandSearch.selected' => 'selectBrands',
        'collectionSearch.selected' => 'selectCollections',
        'productSearch.selected' => 'selectProducts',
    ];

    public function mount()
    {
        $this->currency = Currency::getDefault();

        $this->selectedBrands = $this->discount->brands->map(fn ($brand) => $this->mapBrandToArray($brand));
        $this->selectedCollections = $this->discount->collections->map(fn ($collection) => $this->mapCollectionToArray($collection));
        $this->selectedProducts = $this->discount->purchasableLimitations()
            ->wherePurchasableType(Product::class)
            ->get()
            ->map(function ($limitation) {
                return $this->mapProductToArray($limitation->purchasable);
            });

        $this->selectedBrands = $this->discount->brands->map(fn ($brand) => $this->mapBrandToArray($brand)) ?? collect();
        $this->selectedCollections = $this->discount->collections->map(fn ($collection) => $this->mapCollectionToArray($collection)) ?? collect();
        
        $this->selectedConditions = $this->discount->purchasableConditions()
            ->wherePurchasableType(Product::class)
            ->pluck('purchasable_id')->values()->toArray();

        $this->selectedRewards = $this->discount->purchasableRewards()
            ->wherePurchasableType(Product::class)
            ->pluck('purchasable_id')->values()->toArray();

        $this->syncAvailability();
    }

    public function syncConditions($conditions)
    {
        $this->selectedConditions = $conditions;
    }

    public function getValidationMessages()
    {
        return $this->getDiscountComponent()->getValidationMessages();
    }

    /**
     * Get the collection attribute data.
     *
     * @return void
     */
    public function getAttributeDataProperty()
    {
        return $this->discount->attribute_data;
    }

    /**
     * Set the currency using the provided id.
     *
     * @param  int|string  $currencyId
     * @return void
     */
    public function setCurrency($currencyId)
    {
        $this->currency = $this->currencies->first(fn ($currency) => $currency->id == $currencyId);
    }

    /**
     * Return the available discount types.
     *
     * @return array
     */
    public function getDiscountTypesProperty()
    {
        return Discounts::getTypes();
    }

    /**
     * Return the component for the selected discount type.
     *
     * @return Component
     */
    public function getDiscountComponent()
    {
        return (new DiscountTypes)->getComponent($this->discount->type);
    }

    /**
     * Sync the discount data with what's provided.
     *
     * @param  array  $data
     * @return void
     */
    public function syncDiscountData(array $data)
    {
        $this->discount->data = array_merge(
            $this->discount->data,
            $data
        );
    }

    /**
     * Select brands given an array of IDs
     *
     * @param  array  $ids
     * @return void
     */
    public function selectBrands(array $ids)
    {
        $selectedBrands = Brand::findMany($ids)->map(fn ($brand) => $this->mapBrandToArray($brand));

        $this->selectedBrands = $this->selectedBrands->count()
            ? $this->selectedBrands->merge($selectedBrands)
            : $selectedBrands;
    }

    /**
     * Select collections given an array of IDs
     *
     * @param  array  $ids
     * @return void
     */
    public function selectCollections(array $ids)
    {
        $selectedCollections = ModelsCollection::findMany($ids)->map(fn ($collection) => $this->mapCollectionToArray($collection));

        $this->selectedCollections = $this->selectedCollections->count()
            ? $this->selectedCollections->merge($selectedCollections)
            : $selectedCollections;
    }
    
    /**
     * Select products given an array of IDs
     *
     * @param  array  $ids
     * @return void
     */
    public function selectProducts(array $ids)
    {
        $selectedProducts = Product::findMany($ids)->map(fn ($brand) => $this->mapProductToArray($brand));

        $this->selectedProducts = $this->selectedProducts->count()
            ? $this->selectedProducts->merge($selectedProducts)
            : $selectedProducts;
    }

    public function syncRewards(array $ids)
    {
        $this->selectedRewards = $ids;
    }

    public function syncAvailability()
    {
        $this->availability = [
            'channels' => $this->channels->mapWithKeys(function ($channel) {
                $discountChannel = $this->discount->channels->first(fn ($assoc) => $assoc->id == $channel->id);

                return [
                    $channel->id => [
                        'channel_id' => $channel->id,
                        'starts_at' => $discountChannel ? $discountChannel->pivot->starts_at : null,
                        'ends_at' => $discountChannel ? $discountChannel->pivot->ends_at : null,
                        'enabled' => $discountChannel ? $discountChannel->pivot->enabled : false,
                        'scheduling' => false,
                    ],
                ];
            }),
            'customerGroups' => $this->customerGroups->mapWithKeys(function ($group) {
                $discountGroup = $this->discount->customerGroups->where('id', $group->id)->first();

                $pivot = $discountGroup->pivot ?? null;

                return [
                    $group->id => [
                        'customer_group_id' => $group->id,
                        'scheduling' => false,
                        'enabled' => $pivot?->enabled ?? false,
                        'status' => 'hidden',
                        'starts_at' => $pivot?->starts_at ?? null,
                        'ends_at' => $pivot?->ends_at ?? null,
                    ],
                ];
            }),
        ];
    }

    /**
     * Remove the brand by it's index.
     *
     * @param  int|string  $index
     * @return void
     */
    public function removeBrand($index)
    {
        $this->selectedBrands->forget($index);
    }

    /**
     * Remove the collection by it's index.
     *
     * @param  int|string  $index
     * @return void
     */
    public function removeCollection($index)
    {
        $this->selectedCollections->forget($index);
    }

    /**
     * Remove the product by it's index.
     *
     * @param  int|string  $index
     * @return void
     */
    public function removeProduct($index)
    {
        $this->selectedProducts->forget($index);
    }

    /**
     * Save the discount.
     *
     * @return RedirectResponse
     */
    public function save()
    {
        $redirect = ! $this->discount->id;

        $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->count()) {
                    $this->notify(
                        __('adminhub::validation.generic'),
                        level: 'error'
                    );
                }
            });
        })->validate(null, $this->getValidationMessages());

        DB::transaction(function () {
            $this->discount->max_uses = $this->discount->max_uses ?: null;
            $this->discount->save();

            $this->discount->brands()->sync(
                $this->selectedBrands->pluck('id')
            );

            $channels = collect($this->availability['channels'])->mapWithKeys(function ($channel) {
                return [
                    $channel['channel_id'] => [
                        'starts_at' => ! $channel['enabled'] ? null : $channel['starts_at'],
                        'ends_at' => ! $channel['enabled'] ? null : $channel['ends_at'],
                        'enabled' => $channel['enabled'],
                    ],
                ];
            });

            $cgAvailability = collect($this->availability['customerGroups'])->mapWithKeys(function ($group) {
                $data = Arr::only($group, ['starts_at', 'ends_at']);

                $data['visible'] = in_array($group['status'], ['purchasable', 'visible']);
                $data['enabled'] = $group['enabled'];

                return [
                    $group['customer_group_id'] => $data,
                ];
            });

            $this->discount->customerGroups()->sync($cgAvailability);

            $this->discount->channels()->sync($channels);

            $this->discount->collections()->sync(
                $this->selectedCollections->pluck('id')->toArray()

            );
            
            $this->discount->purchasableLimitations()
                ->whereNotIn('purchasable_id', $this->selectedProducts->pluck('id'))
                ->delete();

            foreach ($this->selectedProducts as $product) {
                $this->discount->purchasableLimitations()->firstOrCreate([
                    'discount_id' => $this->discount->id,
                    'type' => 'limitation',
                    'purchasable_type' => Product::class,
                    'purchasable_id' => $product['id'],
                ]);
            }
        });

        $this->emit('discount.saved', $this->discount->id);

        $this->notify(
            __('adminhub::notifications.discount.saved')
        );

        if ($redirect) {
            redirect()->route('hub.discounts.show', $this->discount->id);
        }
    }

    public function getSideMenuProperty()
    {
        return collect([
            [
                'title' => __('adminhub::menu.product.basic-information'),
                'id' => 'basic-information',
                'has_errors' => $this->errorBag->hasAny([
                    'discount.name',
                    'discount.handle',
                    'discount.starts_at',
                    'discount.ends_at',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.availability'),
                'id' => 'availability',
                'has_errors' => false,
            ],
            [
                'title' => 'Limitations',
                'id' => 'limitations',
                'has_errors' => false,
            ],
            [
                'title' => 'Conditions',
                'id' => 'conditions',
                'has_errors' => $this->errorBag->hasAny([
                    'minPrices.*.price',
                    'discount.max_uses',
                ]),
            ],
            [
                'title' => 'Discount Type',
                'id' => 'type',
                'has_errors' => $this->errorBag->hasAny(array_merge(
                    $this->getDiscountComponent()->rules(),
                    ['selectedConditions', 'selectedRewards']
                )),
            ],
        ]);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.show')
            ->layout('adminhub::layouts.app');
    }

    /**
     * Return the data we need from a brand
     *
     * @return array
     */
    private function mapBrandToArray($brand)
    {
        return [
            'id' => $brand->id,
            'name' => $brand->name,
        ];
    }

    /**
     * Return the data we need from a collection
     *
     * @return array
     */
    private function mapCollectionToArray($collection)
    {
        return [
            'id' => $collection->id,
            'group_id' => $collection->collection_group_id,
            'group_name' => $collection->group->name,
            'name' => $collection->translateAttribute('name'),
            'thumbnail' => optional($collection->thumbnail)->getUrl(),
            'position' => optional($collection->pivot)->position,
            'breadcrumb' => $collection->breadcrumb,
        ];
    }
    
    /**
     * Return the data we need from a product
     *
     * @return array
     */
    private function mapProductToArray($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->translateAttribute('name'),
            'thumbnail' => optional($product->thumbnail)->getUrl('small'),
        ];
    }
}
