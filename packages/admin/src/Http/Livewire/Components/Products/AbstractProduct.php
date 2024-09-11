<?php

namespace Lunar\Hub\Http\Livewire\Components\Products;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Lunar\Facades\DB;
use Lunar\Hub\Http\Livewire\Traits\CanExtendValidation;
use Lunar\Hub\Http\Livewire\Traits\HasAvailability;
use Lunar\Hub\Http\Livewire\Traits\HasDimensions;
use Lunar\Hub\Http\Livewire\Traits\HasImages;
use Lunar\Hub\Http\Livewire\Traits\HasPrices;
use Lunar\Hub\Http\Livewire\Traits\HasSlots;
use Lunar\Hub\Http\Livewire\Traits\HasTags;
use Lunar\Hub\Http\Livewire\Traits\HasUrls;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\SearchesProducts;
use Lunar\Hub\Http\Livewire\Traits\WithAttributes;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Hub\Jobs\Products\GenerateVariants;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Brand;
use Lunar\Models\Collection as ModelsCollection;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;

abstract class AbstractProduct extends Component
{
    use CanExtendValidation;
    use HasAvailability;
    use HasDimensions;
    use HasImages;
    use HasPrices;
    use HasSlots;
    use HasTags;
    use HasUrls;
    use Notifies;
    use SearchesProducts;
    use WithAttributes;
    use WithFileUploads;
    use WithLanguages;

    /**
     * The current product we are editing.
     */
    public Product $product;

    /**
     * The current variant we're editing.
     */
    public ProductVariant $variant;

    /**
     * The custom brand to add.
     *
     * @var string
     */
    public ?string $brand = null;

    /**
     * Whether to use a custom brand.
     */
    public bool $useNewBrand = false;

    /**
     * The options we want to use for the product.
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
     */
    public bool $optionsPanelVisible = false;

    /**
     * Whether to show the delete confirmation modal.
     *
     * @var bool
     */
    public $showDeleteConfirm = false;

    /**
     * Whether to show the delete confirmation modal.
     *
     * @var bool
     */
    public $showRestoreConfirm = false;

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
     */
    public Collection $associations;

    /**
     * Associations that need removing.
     */
    public array $associationsToRemove = [];

    protected function getListeners()
    {
        return array_merge(
            [
                'updatedAttributes',
                'useProductOptions' => 'setOptions',
                'productOptionCreated' => 'resetOptionView',
                'option-manager.selectedValues' => 'setOptionValues',
                'urlSaved' => 'refreshUrls',
                'productSearch.selected' => 'updateAssociations',
                'collectionSearch.selected' => 'selectCollections',
                'productOptionSelectorPanelToggled' => 'setVariantsEnabled',
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
            $this->withAttributesValidationMessages(),
            $this->getExtendedValidationMessages(),
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
            'product.status' => 'required|string',
            'product.product_type_id' => 'required',
            'product.brand_id' => 'nullable',
            'brand' => 'nullable',
            'collections' => 'nullable|array',
            'variant.tax_ref' => 'nullable|string|max:255',
            'associations.*.type' => 'required|string',
            'variant.sku' => get_validation('products', 'sku', [
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

        if (config('lunar-hub.products.require_brand', true)) {
            $baseRules['product.brand_id'] = 'required_without:brand';
            $baseRules['brand'] = 'required_without:product.brand_id|unique:'.Brand::class.',name';
        }

        if ($this->getVariantsCount() <= 1) {
            $baseRules = array_merge(
                $baseRules,
                $this->hasPriceValidationRules(),
                [
                    'variant.stock' => 'required|min:0|numeric|max:10000000',
                    'variant.backorder' => 'required|min:0|numeric|max:10000000',
                    'variant.purchasable' => 'string|required',
                    'variant.length_value' => 'numeric|nullable',
                    'variant.length_unit' => 'string|nullable',
                    'variant.tax_class_id' => 'required',
                    'variant.width_value' => 'numeric|nullable',
                    'variant.width_unit' => 'string|nullable',
                    'variant.height_value' => 'numeric|nullable',
                    'variant.height_unit' => 'string|nullable',
                    'variant.weight_value' => 'numeric|nullable',
                    'variant.weight_unit' => 'string|nullable',
                    'variant.volume_value' => 'numeric|nullable',
                    'variant.volume_unit' => 'string|nullable',
                    'variant.shippable' => 'boolean|nullable',
                    'variant.tax_ref' => 'nullable|string|max:255',
                    'variant.unit_quantity' => 'required|numeric|min:1|max:10000000',
                ]
            );
        }

        return array_merge(
            $baseRules,
            $this->hasImagesValidationRules(),
            $this->hasUrlsValidationRules(! $this->product->id),
            $this->withAttributesValidationRules(),
            $this->getExtendedValidationRules([
                'product' => $this->product,
            ]),
        );
    }

    /**
     * Define the validation attributes.
     *
     * @return array
     */
    protected function validationAttributes()
    {
        $attributes = [
            'tieredPrices.*.tier' => lang(key: 'global.lower_limit', lower: true),
        ];

        return array_merge(
            $attributes,
            $this->getUrlsValidationAttributes()
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
     * Set whether variants should be enabled.
     *
     * @param  bool  $val
     * @return void
     */
    public function setVariantsEnabled($val)
    {
        $this->variantsEnabled = $val;
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
            });
        })->validate(null, $this->getValidationMessages());

        $this->validateUrls();
        $isNew = ! $this->product->id;

        DB::transaction(function () use ($isNew) {
            $data = $this->prepareAttributeData();
            $variantData = $this->prepareAttributeData($this->variantAttributes);

            $this->product->brand_id = $this->product->brand_id ?: null;

            if ($this->brand) {
                $brand = Brand::create([
                    'name' => $this->brand,
                ]);
                $this->product->brand_id = $brand->id;
                $this->brand = null;
                $this->useNewBrand = false;
            }
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

            if (! $this->variantsEnabled && $this->getVariantsCount()) {
                $variantToKeep = $this->product->variants()->first();
                $variantToKeep->values()->detach();

                $variantsToRemove = $this->product->variants->filter(function ($variant) use ($variantToKeep) {
                    return $variant->id != $variantToKeep->id;
                });

                DB::transaction(function () use ($variantsToRemove) {
                    foreach ($variantsToRemove as $variant) {
                        $variant->values()->detach();
                        $variant->prices()->delete();
                        $variant->forceDelete();
                    }
                });
            }

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
                        'starts_at' => ! $channel['enabled'] ? null : $channel['starts_at'],
                        'ends_at' => ! $channel['enabled'] ? null : $channel['ends_at'],
                        'enabled' => $channel['enabled'],
                    ],
                ];
            });

            $cgAvailability = collect($this->availability['customerGroups'])->mapWithKeys(function ($group) {
                $data = Arr::only($group, ['starts_at', 'ends_at']);

                $data['purchasable'] = $group['status'] == 'purchasable';
                $data['visible'] = in_array($group['status'], ['purchasable', 'visible']);
                $data['enabled'] = $group['status'] != 'hidden';

                return [
                    $group['customer_group_id'] => $data,
                ];
            });

            $this->product->customerGroups()->sync($cgAvailability);

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

                ProductAssociation::firstOrCreate([
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
            $this->syncAssociations();

            $this->dispatchBrowserEvent('remove-images');

            $this->variant = $this->product->variants->first();

            $this->notify(__('adminhub::notifications.products.saved'));
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
        $variant->prices()->delete();
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
        return config('lunar-hub.products.disable_variants', false);
    }

    /**
     * Syncs availability with the product.
     *
     * @return void
     */
    protected function syncAvailability()
    {
        $this->availability = [
            'channels' => $this->channels->mapWithKeys(function ($channel) {
                $productChannel = $this->product->channels->first(fn ($assoc) => $assoc->id == $channel->id);

                return [
                    $channel->id => [
                        'channel_id' => $channel->id,
                        'starts_at' => $productChannel ? $productChannel->pivot->starts_at : null,
                        'ends_at' => $productChannel ? $productChannel->pivot->ends_at : null,
                        'enabled' => $productChannel ? $productChannel->pivot->enabled : false,
                        'scheduling' => false,
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
                        'scheduling' => false,
                        'status' => $status,
                        'starts_at' => $pivot->starts_at ?? null,
                        'ends_at' => $pivot->ends_at ?? null,
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
                    'breadcrumb' => $collection->breadcrumb,
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
                'group_name' => $collection->group->name,
                'name' => $collection->translateAttribute('name'),
                'thumbnail' => optional($collection->thumbnail)->getUrl(),
                'position' => optional($collection->pivot)->position,
                'breadcrumb' => $collection->breadcrumb,
            ];
        });

        $this->collections = $this->collections->count()
            ? $this->collections->merge($selectedCollections)
            : $selectedCollections;
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
                if (! $assoc->target) {
                    return;
                }

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
            })
            ->filter();
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
                'is_temp' => true,
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
        $association = $this->associations[$index];

        if (isset($association['is_temp'])) {
            $this->associations->forget($index);
        } else {
            $this->associationsToRemove[] = $this->associations[$index]['id'];
            $this->associations->forget($index);
        }

        $this->emit('updatedExistingProductAssociations', $this->associatedProductIds);
    }

    /**
     * The associated product ids.
     *
     * @return \Illuminate\Support\Collection
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
                    'model' => $group,
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
                'title' => __('adminhub::menu.product.basic-information'),
                'id' => 'basic-information',
                'has_errors' => $this->errorBag->hasAny([
                    'product.brand_id',
                    'product.product_type_id',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.attributes'),
                'id' => 'attributes',
                'has_errors' => $this->errorBag->hasAny([
                    'attributeMapping.*',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.images'),
                'id' => 'images',
                'has_errors' => $this->errorBag->hasAny([
                    'newImages.*',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.availability'),
                'id' => 'availability',
                'has_errors' => $this->errorBag->hasAny([
                    'availability',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.variants'),
                'id' => 'variants',
                'hidden' => $this->variantsDisabled,
                'has_errors' => $this->errorBag->hasAny([]),
            ],
            [
                'title' => __('adminhub::menu.product.pricing'),
                'id' => 'pricing',
                'hidden' => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                    'variant.min_quantity',
                    'basePrices.*',
                    'customerGroupPrices.*',
                    'tieredPrices.*',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.identifiers'),
                'id' => 'identifiers',
                'hidden' => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                    'variant.sku',
                    'variant.gtin',
                    'variant.mpn',
                    'variant.ean',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.inventory'),
                'id' => 'inventory',
                'error_check' => [],
                'hidden' => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                    'variant.stock',
                    'variant.backorder',
                    'variant.purchasable',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.shipping'),
                'id' => 'shipping',
                'hidden' => $this->getVariantsCount() > 1,
                'has_errors' => $this->errorBag->hasAny([
                    'variant.shippable',
                    'variant.length_value',
                    'variant.length_unit',
                    'variant.width_value',
                    'variant.width_unit',
                    'variant.height_value',
                    'variant.height_unit',
                    'variant.weight_value',
                    'variant.weight_unit',
                    'variant.volume_value',
                    'variant.volume_unit',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.urls'),
                'id' => 'urls',
                'has_errors' => $this->errorBag->hasAny([
                    'urls',
                    'urls.*',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.associations'),
                'id' => 'associations',
                'hidden' => false,
                'has_errors' => $this->errorBag->hasAny([
                    'associations',
                ]),
            ],
            [
                'title' => __('adminhub::menu.product.collections'),
                'id' => 'collections',
                'hidden' => false,
                'has_errors' => $this->errorBag->hasAny([
                    'collections',
                ]),
            ],
        ])->reject(fn ($item) => ($item['hidden'] ?? false));
    }

    /**
     * Returns the model with pricing.
     *
     * @return \Lunar\Models\ProductVariant
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
     * @return \Lunar\Models\Product
     */
    protected function getMediaModel()
    {
        return $this->product;
    }

    /**
     * Returns the model which has slots associated.
     *
     * @return \Lunar\Models\Product
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
