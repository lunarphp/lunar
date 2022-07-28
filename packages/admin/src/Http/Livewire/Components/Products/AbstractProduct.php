<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products;

use GetCandy\Hub\Http\Livewire\Traits\HasAvailability;
use GetCandy\Hub\Http\Livewire\Traits\HasDimensions;
use GetCandy\Hub\Http\Livewire\Traits\HasImages;
use GetCandy\Hub\Http\Livewire\Traits\HasPrices;
use GetCandy\Hub\Http\Livewire\Traits\HasSlots;
use GetCandy\Hub\Http\Livewire\Traits\HasTags;
use GetCandy\Hub\Http\Livewire\Traits\HasUrls;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\SearchesProducts;
use GetCandy\Hub\Http\Livewire\Traits\WithAttributes;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Hub\Jobs\Products\GenerateVariants;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Collection as ModelsCollection;
use GetCandy\Models\Product;
use GetCandy\Models\ProductAssociation;
use GetCandy\Models\ProductOption;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

abstract class AbstractProduct extends Component
{
    use Notifies;
    use WithFileUploads;
    use HasImages;
    use HasAvailability;
    use SearchesProducts;
    use WithAttributes;
    use WithLanguages;
    use HasPrices;
    use HasDimensions;
    use HasUrls;
    use HasTags;
    use HasSlots;

    /**
     * The current product we are editing.
     *
     * @var Product
     */
    public Product $product;

    /**
     * The current variant we're editing.
     *
     * @var ProductVariant
     */
    public ProductVariant $variant;

    /**
     * The options we want to use for the product.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $options;

    /**
     * The selected values based on product options.
     *
     * @var array
     */
    public $optionValues = [];

    /**
     * Determine whether variants are enabled.
     *
     * @var bool
     */
    public $variantsEnabled = true;

    /**
     * The current view when adding/selecting options.
     *
     * @var string
     */
    public $optionView = 'select';

    /**
     * Determines whether the options panel should be on show.
     *
     * @var bool
     */
    public bool $optionsPanelVisible = false;

    /**
     * Whether to show the delete confirmation modal.
     *
     * @var bool
     */
    public $showDeleteConfirm = false;

    /**
     * Define availability properties.
     *
     * @var array
     */
    public $availability = [];

    /**
     * The associated product collections.
     *
     * @var array
     */
    public Collection $collections;

    /**
     * An array of collections to detach from the product.
     *
     * @var array
     */
    public Collection $collectionsToDetach;

    /**
     * The product variant attributes.
     *
     * @var \Illuminate\Support\Collection
     */
    public $variantAttributes;

    /**
     * Whether to show inverse associations.
     *
     * @var bool
     */
    public $showInverseAssociations = false;

    /**
     * The base association type to use.
     *
     * @var string
     */
    public $associationType = 'cross-sell';

    /**
     * The current product associations.
     *
     * @var Collection
     */
    public Collection $associations;

    /**
     * Associations that need removing.
     *
     * @var array
     */
    public array $associationsToRemove = [];

    protected function getListeners()
    {
        return array_merge([
            'useProductOptions'             => 'setOptions',
            'productOptionCreated'          => 'resetOptionView',
            'option-manager.selectedValues' => 'setOptionValues',
            'urlSaved'                      => 'refreshUrls',
            'product-search.selected'       => 'updateAssociations',
            'collectionSearch.selected'     => 'selectCollections',
        ],
            $this->getHasImagesListeners(),
            $this->getHasSlotsListeners()
        );
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
            $this->hasPriceValidationMessages(),
            $this->withAttributesValidationMessages()
        );
    }

    /**
     * Define the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $baseRules = [
            'product.status'          => 'required|string',
            'product.brand'           => 'nullable|string|max:255',
            'product.product_type_id' => 'required',
            'collections'             => 'nullable|array',
            'variant.tax_ref'         => 'nullable|string|max:255',
            'associations.*.type'     => 'required|string',
            'variant.sku'             => get_validation('products', 'sku', [
                'alpha_dash',
                'max:255',
            ], $this->variant),
            'variant.gtin' => get_validation('products', 'gtin', [
                'string',
                'max:255',
            ], $this->variant),
            'variant.mpn' => get_validation('products', 'mpn', [
                'string',
                'max:255',
            ], $this->variant),
            'variant.ean' => get_validation('products', 'ean', [
                'string',
                'max:255',
            ], $this->variant),
        ];

        if ($this->getVariantsCount() <= 1) {
            $baseRules = array_merge(
                $baseRules,
                $this->hasPriceValidationRules(),
                [
                    'variant.stock'         => 'numeric|max:10000000',
                    'variant.backorder'     => 'numeric|max:10000000',
                    'variant.purchasable'   => 'string|required',
                    'variant.length_value'  => 'numeric|nullable',
                    'variant.length_unit'   => 'string|nullable',
                    'variant.tax_class_id'  => 'required',
                    'variant.width_value'   => 'numeric|nullable',
                    'variant.width_unit'    => 'string|nullable',
                    'variant.height_value'  => 'numeric|nullable',
                    'variant.height_unit'   => 'string|nullable',
                    'variant.weight_value'  => 'numeric|nullable',
                    'variant.weight_unit'   => 'string|nullable',
                    'variant.volume_value'  => 'numeric|nullable',
                    'variant.volume_unit'   => 'string|nullable',
                    'variant.shippable'     => 'boolean|nullable',
                    'variant.tax_ref'         => 'nullable|string|max:255',
                    'variant.unit_quantity' => 'required|numeric|min:1|max:10000000',
                ]
            );
        }

        return array_merge(
            $baseRules,
            $this->hasImagesValidationRules(),
            $this->withAttributesValidationRules(),
            $this->hasUrlsValidationRules(! $this->product->id),
            $this->withAttributesValidationRules()
        );
    }

    /**
     * Set the options to be whatever we pass through.
     *
     * @param  array  $optionIds
     * @return void
     */
    public function setOptions($optionIds)
    {
        $this->options = ProductOption::findMany($optionIds);
        $this->emit('products.options.updated', $optionIds);
        $this->optionsPanelVisible = false;
    }

    /**
     * Set option values.
     *
     * @param  array  $values
     * @return void
     */
    public function setOptionValues($values)
    {
        $this->optionValues = $values;
    }

    /**
     * Remove an option by it's given position in the collection.
     *
     * @param  int  $key
     * @return void
     */
    public function removeOption($key)
    {
        $option = $this->options[$key];

        $remainingValues = collect($this->optionValues)->diff($option->values->pluck('id'));

        $this->optionValues = $remainingValues->values();

        unset($this->options[$key]);
    }

    /**
     * Universal method to handle saving the product.
     *
     * @return void|\Symfony\Component\HttpFoundation\Response
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
                // dd(1);
            });
        })->validate(null, $this->getValidationMessages());

        $isNew = ! $this->product->id;

        DB::transaction(function () use ($isNew) {
            $data = $this->prepareAttributeData();
            $variantData = $this->prepareAttributeData($this->variantAttributes);

            $this->product->attribute_data = $data;

            $this->product->save();

            if (($this->getVariantsCount() <= 1) || $isNew) {
                if (! $this->variant->product_id) {
                    $this->variant->product_id = $this->product->id;
                }

                if (! $this->manualVolume) {
                    $this->variant->volume_unit = null;
                    $this->variant->volume_value = null;
                }

                $this->variant->attribute_data = $variantData;

                $this->variant->save();

                if ($isNew) {
                    $this->savePricing();
                }
            }

            // We generating variants?
            $generateVariants = (bool) count($this->optionValues) && ! $this->variantsDisabled;

            if ($generateVariants) {
                GenerateVariants::dispatch($this->product, $this->optionValues);
            }

            if (! $generateVariants && $this->product->variants->count() <= 1 && ! $isNew) {
                // Only save pricing if we're not generating new variants.
                $this->savePricing();
            }

            $this->saveUrls();

            $this->product->syncTags(
                collect($this->tags)
            );

            $this->updateImages($this->product);

            $channels = collect($this->availability['channels'])->mapWithKeys(function ($channel) {
                return [
                    $channel['channel_id'] => [
                        'starts_at'    => ! $channel['enabled'] ? null : $channel['starts_at'],
                        'ends_at'      => ! $channel['enabled'] ? null : $channel['ends_at'],
                        'enabled'      => $channel['enabled'],
                    ],
                ];
            });

            $gcAvailability = collect($this->availability['customerGroups'])->mapWithKeys(function ($group) {
                $data = Arr::only($group, ['starts_at', 'ends_at']);

                $data['purchasable'] = $group['status'] == 'purchasable';
                $data['visible'] = in_array($group['status'], ['purchasable', 'visible']);
                $data['enabled'] = $group['status'] != 'hidden';

                return [
                    $group['customer_group_id'] => $data,
                ];
            });

            $this->product->customerGroups()->sync($gcAvailability);

            $this->product->channels()->sync($channels);

            if (count($this->associationsToRemove)) {
                ProductAssociation::whereIn('id', $this->associationsToRemove)->delete();
            }

            $this->associations->each(function ($assoc) {
                if (! empty($assoc['id'])) {
                    ProductAssociation::find($assoc['id'])->update([
                        'type' => $assoc['type'],
                    ]);

                    return;
                }

                ProductAssociation::create([
                    'product_target_id' => $assoc['inverse'] ? $this->product->id : $assoc['target_id'],
                    'product_parent_id' => $assoc['inverse'] ? $assoc['target_id'] : $this->product->id,
                    'type' => $assoc['type'],
                ]);
            });

            $this->product->collections()->detach(
                $this->collectionsToDetach->pluck('id')
            );

            $this->collections->each(function ($collection) {
                $this->product->collections()
                    ->syncWithoutDetaching(
                        $collection['id'],
                        ['position' => $collection['position']]
                    );
            });

            $this->updateSlots();

            $this->product->refresh();

            $this->variantsEnabled = $this->getVariantsCount() > 1;

            $this->syncAvailability();

            $this->dispatchBrowserEvent('remove-images');

            $this->variant = $this->product->variants->first();

            $this->notify('Product Saved');
        });

        if ($isNew) {
            return redirect()->route('hub.products.show', [
                'product' => $this->product->id,
            ]);
        }
    }

    /**
     * Method to return variants count.
     *
     * @return int
     */
    public function getVariantsCount()
    {
        return $this->product->variants->count();
    }

    /**
     * Remove a variant.
     *
     * @param  int  $variantId
     * @return void
     */
    public function deleteVariant($variantId)
    {
        if ($this->getVariantsCount() == 1) {
            $this->notify(
                __('adminhub::notifications.variants.minimum_reached'),
                level: 'error'
            );

            return;
        }
        $variant = ProductVariant::find($variantId);
        $variant->values()->detach();
        $variant->delete();
        $this->product->refresh();
    }

    public function getExistingTagsProperty(): array
    {
        return $this->product->tags->pluck('value')->toArray();
    }

    /**
     * Returns whether variants should be disabled.
     *
     * @return void
     */
    public function getVariantsDisabledProperty()
    {
        return config('getcandy-hub.products.disable_variants', false);
    }

    /**
     * Syncs availability with the product.
     *
     * @return void
     */
    protected function syncAvailability()
    {
        $this->availability = [
            'channels'                                                        => $this->channels->mapWithKeys(function ($channel) {
                $productChannel = $this->product->channels->first(fn ($assoc) => $assoc->id == $channel->id);

                return [
                    $channel->id => [
                        'channel_id'   => $channel->id,
                        'starts_at'    => $productChannel ? $productChannel->pivot->starts_at : null,
                        'ends_at'      => $productChannel ? $productChannel->pivot->ends_at : null,
                        'enabled'      => $productChannel ? $productChannel->pivot->enabled : false,
                        'scheduling'   => false,
                    ],
                ];
            }),
            'customerGroups' => $this->customerGroups->mapWithKeys(function ($group) {
                $productGroup = $this->product->customerGroups->where('id', $group->id)->first();

                $pivot = $productGroup->pivot ?? null;

                $status = 'hidden';

                if ($pivot) {
                    if ($pivot->purchasable) {
                        $status = 'purchasable';
                    } elseif (! $pivot->visible && ! $pivot->enabled) {
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

    protected function syncCollections()
    {
        $this->collections = $this->product->collections()
            ->with(['group', 'thumbnail'])
            ->get()
            ->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'group_id' => $collection->collection_group_id,
                    'group_name' => $collection->group->name,
                    'name' => $collection->translateAttribute('name'),
                    'thumbnail' => optional($collection->thumbnail)->getUrl(),
                    'position' => $collection->pivot->position,
                    'breadcrumb' => $collection->ancestors->map(function ($ancestor) {
                        return $ancestor->translateAttribute('name');
                    }),
                ];
            });

        $this->collectionsToDetach = collect();
    }

    /**
     * Remove the collection by it's index.
     *
     * @param  int|string  $index
     * @return void
     */
    public function removeCollection($index)
    {
        $this->collectionsToDetach->push(
            $this->collections[$index]
        );
        $this->collections->forget($index);
    }

    /**
     * Map and add the selected collections.
     *
     * @param  array  $collectionIds
     * @return void
     */
    public function selectCollections($collectionIds)
    {
        $selectedCollections = ModelsCollection::findMany($collectionIds)->map(function ($collection) {
            return [
                'id' => $collection->id,
                'group_id' => $collection->collection_group_id,
                'name' => $collection->translateAttribute('name'),
                'thumbnail' => optional($collection->thumbnail)->getUrl(),
                'position' => optional($collection->pivot)->position,
                'breadcrumb' => $collection->ancestors->map(function ($ancestor) {
                    return $ancestor->translateAttribute('name');
                })->join(' > '),
            ];
        });

        $this->collections = $this->collections->count() ?
            $this->collections->merge($selectedCollections) :
            $selectedCollections;
    }

    /**
     * Sync initial product associations.
     *
     * @return void
     */
    public function syncAssociations()
    {
        $this->associations = $this->product->associations
            ->merge($this->product->inverseAssociations)
            ->map(function ($assoc) {
                $inverse = $assoc->target->id == $this->product->id;

                $product = $inverse ? $assoc->parent : $assoc->target;

                return [
                    'id' => $assoc->id,
                    'inverse' => $inverse,
                    'target_id' => $product->id,
                    'thumbnail' => optional($product->thumbnail)->getUrl('small'),
                    'name' => $product->translateAttribute('name'),
                    'type' => $assoc->type,
                ];
            });
    }

    /**
     * Update the associations.
     *
     * @param  array  $selectedIds
     * @return void
     */
    public function updateAssociations($selectedIds)
    {
        $selectedProducts = Product::findMany($selectedIds)->map(function ($product) {
            return [
                'inverse' => (bool) $this->showInverseAssociations,
                'target_id' => $product->id,
                'thumbnail' => optional($product->thumbnail)->getUrl('small'),
                'name' => $product->translateAttribute('name'),
                'type' => $this->associationType,
            ];
        });

        $this->associations = $this->associations->count() ?
            $this->associations->merge($selectedProducts) :
            $selectedProducts;

        $this->emit('updatedExistingProductAssociations', $this->associatedProductIds);
    }

    /**
     * Open the association browser with a given type.
     *
     * @param  string  $type
     * @return void
     */
    public function openAssociationBrowser($type)
    {
        $this->associationType = $type;
        $this->emit('showBrowser', 'product-associations');
    }

    /**
     * Remove an association.
     *
     * @param  int  $index
     * @return void
     */
    public function removeAssociation($index)
    {
        $this->associationsToRemove[] = $this->associations[$index]['id'];

        $this->associations->forget($index);
    }

    /**
     * The associated product ids.
     *
     * @return void
     */
    public function getAssociatedProductIdsProperty()
    {
        return collect(
            $this->associations->map(fn ($assoc) => ['id' => $assoc['target_id']])
        );
    }

    /**
     * Returns the attribute data.
     *
     * @return array
     */
    public function getAttributeDataProperty()
    {
        return $this->product->attribute_data;
    }

    /**
     * Resets the option view to the default.
     *
     * @return void
     */
    public function resetOptionView()
    {
        $this->optionView = 'select';
    }

    /**
     * Returns all available attributes.
     *
     * @return void
     */
    public function getAvailableAttributesProperty()
    {
        return ProductType::find(
            $this->product->product_type_id
        )->productAttributes->sortBy('position')->values();
    }

    /**
     * Returns all available variant attributes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableVariantAttributesProperty()
    {
        return ProductType::find(
            $this->product->product_type_id
        )->variantAttributes->sortBy('position')->values();
    }

    /**
     * Return attribute groups available for variants.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getVariantAttributeGroupsProperty()
    {
        $groupIds = $this->variantAttributes->pluck('group_id')->unique();

        return AttributeGroup::whereIn('id', $groupIds)
            ->orderBy('position')
            ->get()->map(function ($group) {
                return [
                    'model'  => $group,
                    'fields' => $this->variantAttributes->filter(fn ($att) => $att['group_id'] == $group->id),
                ];
            });
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
                'title'      => __('adminhub::menu.product.basic-information'),
                'id'         => 'basic-information',
                'has_errors' => $this->errorBag->hasAny([
                    'product.brand',
                    'product.product_type_id',
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.attributes'),
                'id'         => 'attributes',
                'has_errors' => $this->errorBag->hasAny([
                    'attributeMapping.*',
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.images'),
                'id'         => 'images',
                'has_errors' => $this->errorBag->hasAny([
                    'newImages.*',
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.availability'),
                'id'         => 'availability',
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.variants'),
                'id'         => 'variants',
                'hidden'     => $this->variantsDisabled,
                'has_errors' => $this->errorBag->hasAny([]),
            ],
            [
                'title'      => __('adminhub::menu.product.pricing'),
                'id'         => 'pricing',
                'hidden'     => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                    'variant.min_quantity',
                    'basePrices.*',
                    'customerGroupPrices.*',
                    'tieredPrices.*',
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.identifiers'),
                'id'         => 'identifiers',
                'hidden'     => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                    'variant.sku',
                    'variant.gtin',
                    'variant.mpn',
                    'variant.ean',
                ]),
            ],
            [
                'title'       => __('adminhub::menu.product.inventory'),
                'id'          => 'inventory',
                'error_check' => [],
                'has_errors'  => $this->errorBag->hasAny([
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.shipping'),
                'id'         => 'shipping',
                'hidden'     => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.urls'),
                'id'         => 'urls',
                'hidden'     => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                    'urls',
                    'urls.*',
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.associations'),
                'id'         => 'associations',
                'hidden'     => false,
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
            [
                'title'      => __('adminhub::menu.product.collections'),
                'id'         => 'collections',
                'hidden'     => false,
                'has_errors' => $this->errorBag->hasAny([
                ]),
            ],
        ])->reject(fn ($item) => ($item['hidden'] ?? false));
    }

    /**
     * Returns the model with pricing.
     *
     * @return \GetCandy\Models\ProductVariant
     */
    protected function getPricedModel()
    {
        return $this->product->variants->first() ?: $this->variant;
    }

    protected function getHasUrlsModel()
    {
        return $this->product;
    }

    public function getHasDimensionsModel()
    {
        return $this->variant;
    }

    /**
     * Returns the model which has media associated.
     *
     * @return \GetCandy\Models\Product
     */
    protected function getMediaModel()
    {
        return $this->product;
    }

    /**
     * Returns the model which has slots associated.
     *
     * @return \GetCandy\Models\Product
     */
    protected function getSlotModel()
    {
        return $this->product;
    }

    /**
     * Returns the contexts for any slots.
     *
     * @return array
     */
    protected function getSlotContexts()
    {
        return ['product.all'];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    abstract public function render();
}
