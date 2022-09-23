<?php

namespace Lunar\Hub\Http\Livewire\Components\Collections;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Lunar\Hub\Http\Livewire\Traits\HasAvailability;
use Lunar\Hub\Http\Livewire\Traits\HasImages;
use Lunar\Hub\Http\Livewire\Traits\HasUrls;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithAttributes;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Product;
use Lunar\Models\Tag;

class CollectionShow extends Component
{
    use Notifies;
    use HasAvailability;
    use WithAttributes;
    use HasImages;
    use WithFileUploads;
    use HasUrls;
    use WithLanguages;

    /**
     * The collection we are currently editing.
     *
     * @var \Lunar\Models\Collection
     */
    public Collection $collection;

    /**
     * Define availability properties.
     *
     * @var array
     */
    public $availability = [];

    /**
     * The products attached to the collection.
     *
     * @var \Illuminate\Support\Collection
     */
    public \Illuminate\Support\Collection $products;

    protected function getListeners()
    {
        return array_merge([
            'product-search.selected' => 'addSelectedProducts',
        ], $this->getHasImagesListeners());
    }

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->products = $this->mapProducts(
            $this->collection->load('products.variants.basePrices.currency')->products
        );
        $this->syncAvailability();
    }

    /**
     * Get the collection attribute data.
     *
     * @return void
     */
    public function getAttributeDataProperty()
    {
        return $this->collection->attribute_data;
    }

    /**
     * Returns all available attributes.
     *
     * @return void
     */
    public function getAvailableAttributesProperty()
    {
        return Attribute::whereAttributeType(Collection::class)->orderBy('position')->get();
    }

    /**
     * Return the model with media.
     *
     * @return \Lunar\Models\Collection
     */
    public function getMediaModel()
    {
        return $this->collection;
    }

    /**
     * Return the model with URLs.
     *
     * @return \Lunar\Models\Collection
     */
    public function getHasUrlsModel()
    {
        return $this->collection;
    }

    /**
     * Return the default currency.
     *
     * @return \Lunar\Models\Currency
     */
    public function getDefaultCurrencyProperty()
    {
        return Currency::getDefault();
    }

    /**
     * Return the validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(
            [
                'collection.sort' => 'required',
            ],
            $this->withAttributesValidationRules(),
            $this->hasImagesValidationRules(),
            $this->hasUrlsValidationRules()
        );
    }

    /**
     * Add the selected products to the collection.
     *
     * @param  array  $ids
     * @return void
     */
    public function addSelectedProducts($ids)
    {
        $new = Product::whereIn('id', $ids)->withTrashed()->get()->map(function ($product) {
            return $this->mapProduct($product, true);
        });

        $this->products = collect($this->products)->merge($new);

        if ($this->collection->sort != 'custom') {
            [$column, $direction] = explode(':', $this->collection->sort);
            $this->refreshSorting($column, $direction);
        }
    }

    /**
     * Remove a product from the collection by it's id.
     *
     * @param  string|int  $id
     * @return void
     */
    public function removeProduct($id)
    {
        $index = $this->products->pluck('id')->search($id);

        $this->products->forget($index);
        $this->products = $this->products->values();
    }

    /**
     * Listener for when the collection sort type is updated.
     *
     * @param  string  $value
     * @return void
     */
    public function updatedCollectionSort($value)
    {
        if ($value == 'custom') {
            return $this->products = $this->products->sortBy('position')->values();
        }

        [$column, $direction] = explode(':', $value);

        $this->refreshSorting($column, $direction);
    }

    /**
     * Sort the products.
     *
     * @param  array  $payload
     * @return void
     */
    public function sortProducts(array $payload)
    {
        $newOrder = collect($payload['items']);

        $products = $this->products->toArray();

        foreach ($products as $key => $product) {
            $item = $newOrder->first(fn ($item) => $item['id'] == $product['id']);
            $products[$key]['position'] = $item['order'];
        }

        $this->products = collect($products)->sortBy('position')->values();
    }

    /**
     * Return the available tags.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableTagsProperty()
    {
        return Tag::get();
    }

    /**
     * Save the collection.
     *
     * @return void
     */
    public function save()
    {
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

        $this->collection->attribute_data = $this->prepareAttributeData();

        $this->collection->save();

        $channels = collect($this->availability['channels'])->mapWithKeys(function ($channel) {
            return [
                $channel['channel_id'] => [
                    'starts_at' => ! $channel['enabled'] ? null : $channel['starts_at'],
                    'ends_at'   => ! $channel['enabled'] ? null : $channel['ends_at'],
                    'enabled'   => $channel['enabled'],
                ],
            ];
        });

        $this->collection->channels()->sync($channels);

        DB::transaction(function () {
            $cgAvailability = collect($this->availability['customerGroups'])->mapWithKeys(function ($group) {
                $data = Arr::only($group, ['starts_at', 'ends_at']);

                $data['visible'] = in_array($group['status'], ['purchasable', 'visible']);
                $data['enabled'] = $group['status'] != 'hidden';

                return [
                    $group['customer_group_id'] => $data,
                ];
            });

            $this->collection->customerGroups()->sync($cgAvailability);
        });

        DB::transaction(function () {
            $this->collection->products()->sync(
                $this->products->mapWithKeys(function ($product, $index) {
                    return [
                        $product['id'] => [
                            'position' => $index + 1,
                        ],
                    ];
                })
            );
        });

        $this->updateImages();
        $this->saveUrls();

        $this->notify('Collection updated');
    }

    /**
     * Return the side menu links.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSideMenuProperty()
    {
        return collect([
            [
                'title'      => __('adminhub::menu.attributes'),
                'id'         => 'attributes',
                'has_errors' => $this->errorBag->hasAny([
                    'attributeMapping.*',
                ]),
            ],
            [
                'title'      => __('adminhub::menu.images'),
                'id'         => 'images',
                'has_errors' => $this->errorBag->hasAny([
                    'newImages.*',
                ]),
            ],
            [
                'title'      => __('adminhub::menu.availability'),
                'id'         => 'availability',
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
            [
                'title'      => __('adminhub::menu.urls'),
                'id'         => 'urls',
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
            [
                'title'      => __('adminhub::menu.products'),
                'id'         => 'products',
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
            [
                'title'      => __('adminhub::menu.collections'),
                'id'         => 'collections',
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
        ]);
    }

    /**
     * Returns any custom validation messages.
     *
     * @return array
     */
    protected function getValidationMessages()
    {
        return array_merge(
            [],
            $this->withAttributesValidationMessages()
        );
    }

    /**
     * Map products ready for display/sorting.
     *
     * @param  \Illuminate\Support\Collection  $products
     * @return \Illuminate\Support\Collection
     */
    protected function mapProducts(\Illuminate\Support\Collection $products)
    {
        return $products->map(function ($product) {
            return $this->mapProduct($product, false);
        });
    }

    /**
     * Refresh sorting based on column and direction.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return void
     */
    protected function refreshSorting($column, $direction)
    {
        $this->products = $this->products->sort(function ($current, $next) use ($column, $direction) {
            return $direction == 'desc' ?
                ($current[$column] < $next[$column]) :
                ($current[$column] > $next[$column]);
        })->values();
    }

    /**
     * Map a product into the array.
     *
     * @param  Product  $product
     * @param  bool  $pendingSave
     * @return array
     */
    protected function mapProduct(Product $product, $pendingSave = false)
    {
        $basePrice = $product->variants->map(function ($variant) {
            return $variant->basePrices->filter(function ($price) {
                return $price->currency_id == $this->defaultCurrency->id;
            });
        })->flatten()->first();

        return [
            'id'             => $product->id,
            'sort_key'       => Str::random(),
            'name'           => $product->translateAttribute('name'),
            'recently_added' => $pendingSave,
            'thumbnail'      => $product->thumbnail ? $product->thumbnail->getUrl('small') : null,
            'position'       => $product->pivot->position ?? 9999,
            'sku'            => $product->variants->map(function ($variant) {
                return $variant->sku;
            })->join(','),
            'base_price' => $basePrice->load('currency')->formatted,
        ];
    }

    /**
     * Syncs availability with the product.
     *
     * @return void
     */
    protected function syncAvailability()
    {
        $this->availability = [
            'channels'                                                              => $this->channels->mapWithKeys(function ($channel) {
                $collectionChannel = $this->collection->channels->first(fn ($assoc) => $assoc->id == $channel->id);

                return [
                    $channel->id => [
                        'channel_id'   => $channel->id,
                        'starts_at'    => $collectionChannel ? $collectionChannel->pivot->starts_at : null,
                        'ends_at'      => $collectionChannel ? $collectionChannel->pivot->ends_at : null,
                        'enabled'      => $collectionChannel ? $collectionChannel->pivot->enabled : false,
                        'scheduling'   => false,
                    ],
                ];
            }),
            'customerGroups' => $this->customerGroups->mapWithKeys(function ($group) {
                $collectionGroup = $this->collection->customerGroups->where('id', $group->id)->first();

                $pivot = $collectionGroup->pivot ?? null;

                $status = 'hidden';

                if ($pivot) {
                    if (! $pivot->visible && ! $pivot->enabled) {
                        $status = 'hidden';
                    } elseif ($pivot->visible) {
                        $status = 'visible';
                    }
                }

                return [
                    $group->id => [
                        'customer_group_id' => $group->id,
                        'scheduling'        => false,
                        'status'            => $status,
                        'starts_at'         => $pivot->starts_at ?? null,
                        'ends_at'           => $pivot->ends_at ?? null,
                    ],
                ];
            }),
        ];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.collections.show')
            ->layout('adminhub::layouts.app', [
                'title' => $this->collection->translateAttribute('name'),
            ]);
    }
}
