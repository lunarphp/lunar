<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\Variants;

use GetCandy\Hub\Http\Livewire\Traits\HasDimensions;
use GetCandy\Hub\Http\Livewire\Traits\HasImages;
use GetCandy\Hub\Http\Livewire\Traits\HasPrices;
use GetCandy\Hub\Http\Livewire\Traits\HasSlots;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Http\Livewire\Traits\WithAttributes;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Hub\Jobs\Products\GenerateVariants;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Livewire\TemporaryUploadedFile;
use Spatie\Activitylog\Facades\LogBatch;

class VariantShow extends Component
{
    use WithFileUploads;
    use Notifies;
    use HasPrices;
    use WithLanguages;
    use WithAttributes;
    use HasDimensions;
    use HasSlots;
    use HasImages;

    /**
     * Instance of the parent product.
     *
     * @var \GetCandy\Models\Product
     */
    public Product $product;

    /**
     * Instance of the product variant.
     *
     * @var \GetCandy\Models\ProductVariant
     */
    public ProductVariant $variant;

    /**
     * The new image we want to use for the variant.
     *
     * @var null|\Spatie\MediaLibrary\MediaCollections\Models\Media|\Livewire\TemporaryUploadedFile
     */
    public $image = null;

    /**
     * The image we want to select from product images.
     *
     * @var null|string
     */
    public $imageToSelect = null;

    /**
     * Determines whether the image select modal is visible.
     *
     * @var bool
     */
    public $showImageSelectModal = false;

    /**
     * Whether the image should be removed on save.
     *
     * @var bool
     */
    public $removeImage = false;

    /**
     * Whether or not to show the delete confirm modal.
     *
     * @var bool
     */
    public $showDeleteConfirm = false;

    /**
     * Whether to show the add variant panel.
     *
     * @var bool
     */
    public $showAddVariant = false;

    /**
     * The new values to generate the variant.
     *
     * @var array
     */
    public array $newValues = [];

    /**
     * {@inheritDoc}
     */
    public function mountHasImages()
    {
        $this->images = $this->variant->images->map(function ($media) {
            return [
                'id'        => $media->id,
                'sort_key'  => Str::random(),
                'thumbnail' => $media->getFullUrl('medium'),
                'original'  => $media->getFullUrl(),
                'preview'   => false,
                'caption'   => $media->getCustomProperty('caption'),
                'primary'   => $media->pivot->primary,
                'position'  => $media->getCustomProperty('position', 1),
            ];
        })->sortBy('position')->values()->toArray();
    }

    /**
     * Define the listeners.
     *
     * @var array
     */
    protected function getListeners()
    {
        return array_merge([
            'option-value-create-modal.value-created' => 'refreshAndSelectOption',
        ],
            $this->getHasSlotsListeners(),
            $this->getHasImagesListeners()
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
            $this->hasPriceValidationMessages()
        );
    }

    /**
     * Called when the component is dehydrated.
     *
     * @return void
     */
    public function dehydrate()
    {
        if ($this->errorBag->count()) {
            $this->notify(
                __('adminhub::validation.generic'),
                level: 'error'
            );
        }
    }

    /**
     * Register the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return array_merge([
            'newValues'             => 'array',
            'variant.stock'         => 'numeric|max:10000000',
            'variant.tax_class_id'  => 'required',
            'variant.length_value'  => 'numeric|nullable',
            'variant.length_unit'   => 'string|nullable',
            'variant.width_value'   => 'numeric|nullable',
            'variant.width_unit'    => 'string|nullable',
            'variant.height_value'  => 'numeric|nullable',
            'variant.height_unit'   => 'string|nullable',
            'variant.weight_value'  => 'numeric|nullable',
            'variant.weight_unit'   => 'string|nullable',
            'variant.volume_value'  => 'numeric|nullable',
            'variant.volume_unit'   => 'string|nullable',
            'variant.shippable'     => 'boolean|nullable',
            'variant.backorder'     => 'numeric|max:10000000',
            'variant.tax_ref'         => 'nullable|string|max:255',
            'variant.purchasable'   => 'string|required',
            'variant.unit_quantity' => 'required|numeric|min:1|max:10000000',
            'variant.sku'           => get_validation('products', 'sku', [
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
        ], $this->hasPriceValidationRules(), $this->hasImagesValidationRules());
    }

    /**
     * Method to handle variant saving.
     *
     * @return void
     */
    public function save()
    {
        $this->validate(null, $this->getValidationMessages());

        if (! $this->manualVolume) {
            $this->variant->volume_unit = null;
            $this->variant->volume_value = null;
        }

        $data = $this->prepareAttributeData();
        $this->variant->attribute_data = $data;

        $this->variant->save();
        $this->savePricing();
        $this->updateImages();

        $this->updateSlots();

        $this->notify('Variant updated');
    }

    public function updateImages()
    {
        DB::transaction(function () {
            LogBatch::startBatch();

            $owner = $this->variant->product;

            $imagesToSync = [];

            foreach ($this->images as $key => $image) {
                if (empty($image['id'])) {
                    $file = TemporaryUploadedFile::createFromLivewire(
                        $image['filename']
                    );
                    $media = $owner->addMedia($file->getRealPath())
                        ->toMediaCollection('products');

                    activity()
                    ->performedOn($this->variant)
                    ->withProperties(['media' => $media->toArray()])
                    ->event('added_image')
                    ->useLog('getcandy')
                    ->log('added_image');

                    // Add ID for future and processing now.
                    $this->images[$key]['id'] = $media->id;
                    $image['id'] = $media->id;
                }

                $media = app(config('media-library.media_model'))::find($image['id']);

                $media->setCustomProperty('caption', $image['caption']);
                $media->setCustomProperty('primary', false);
                $media->setCustomProperty('position', $image['position']);
                $media->save();

                $imagesToSync[$media->id] = [
                    'primary' => $image['primary']
                ];
            }


            $this->variant->images()->sync($imagesToSync);

            LogBatch::endBatch();
        });
    }

    /**
     * Delete the variant.
     *
     * @return void
     */
    public function delete()
    {
        DB::transaction(function () {
            $this->variant->values()->detach();
            $this->variant->forceDelete();
        });

        $this->notify(__('adminhub::notifications.variants.deleted'), 'hub.products.show', [
            'product' => $this->product,
        ]);
    }

    /**
     * Method to select image from a product to use.
     *
     * @return void
     */
    public function selectImage()
    {
        $this->image = $this->product
            ->media
            ->first(fn ($image) => $image->id == $this->imageToSelect);

        $this->showImageSelectModal = false;
        $this->imageToSelect = null;
    }

    /**
     * Refresh and select option.
     *
     * @param  array  $event
     * @return void
     */
    public function refreshAndSelectOption($event)
    {
        $this->newValues[$event['option']] = $event['value'];
    }

    protected function getPricedModel()
    {
        return $this->variant;
    }

    public function getHasDimensionsModel()
    {
        return $this->variant;
    }

    /**
     * Return the computed customer groups.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerGroupsProperty()
    {
        return CustomerGroup::get();
    }

    /**
     * Get the current variant options we have available.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function variantOptions()
    {
        return ProductOption::whereIn(
            'id',
            $this->variant->values->pluck('product_option_id')->toArray()
        )->with('values')->get();
    }

    /**
     * Get the variant option values which have already been assigned/generated.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAssignedVariantValuesProperty()
    {
        return $this->variant->product->variants->map(function ($variant) {
            return $variant->values;
        })->map(function ($values) {
            return $values->mapWithKeys(function ($value) {
                return [$value->product_option_id => $value->id];
            });
        });
    }

    /**
     * Generate variants based on new values selected.
     *
     * @return void
     */
    public function generateVariants()
    {
        $rules = [];
        $messages = [];

        foreach ($this->variantOptions() as $option) {
            $rules['newValues.'.$option->id] = 'required';
            $messages['newValues.'.$option->id.'.required'] = __('adminhub::validation.variant_option_required');
        }

        $this->validate($rules, $messages);

        foreach ($this->assignedVariantValues as $values) {
            $existing = [];
            foreach ($this->newValues as $optionId => $valueId) {
                if (($values[$optionId] ?? null) == $valueId) {
                    $existing[] = $optionId;
                }
            }
            // If the existing and new values match, then we must
            // already have this iteration.
            if (count($existing) == count($this->newValues)) {
                session()->flash('variant_exists', true);

                return;
            }
        }

        GenerateVariants::dispatch($this->product, $this->newValues, true);

        $this->notify(
            __('adminhub::notifications.variants.created')
        );
        $this->showAddVariant = false;

        $this->newValues = [];

        $this->product = $this->variant->product->refresh();
    }

    public function getAttributeDataProperty()
    {
        return $this->variant->attribute_data;
    }

    public function getAvailableAttributesProperty()
    {
        return ProductType::find(
            $this->variant->product->product_type_id
        )->variantAttributes->sortBy('position')->values();
    }

    /**
     * {@inheritDoc}
     */
    public function getMediaModel()
    {
        return $this->variant;
    }

    /**
     * Returns the model which has slots associated.
     *
     * @return \GetCandy\Models\ProductVariant
     */
    protected function getSlotModel()
    {
        return $this->variant;
    }

    /**
     * Returns the contexts for any slots.
     *
     * @return array
     */
    protected function getSlotContexts()
    {
        return ['productvariant.show'];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.variants.show')
            ->layout('adminhub::layouts.base');
    }
}
