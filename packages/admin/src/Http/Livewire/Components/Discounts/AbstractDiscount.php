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

    public Collection $collections;

    /**
     * The brands to restrict the coupon for.
     *
     * @var array
     */
    public array $selectedBrands = [];

    public array $selectedCollections = [];

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
        'discount.purchasables' => 'syncPurchasables',
        'collectionTreeSelect.updated' => 'selectCollections',
    ];

    public function mount()
    {
        $this->currency = Currency::getDefault();
        $this->selectedBrands = $this->discount->brands->pluck('id')->toArray();
        $this->selectedCollections = $this->discount->collections->pluck('id')->toArray();
        $this->syncAvailability();
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
        $this->discount->data = $data;
    }

    /**
     * Select collections given an array of IDs
     *
     * @param  array  $ids
     * @return void
     */
    public function selectCollections(array $ids)
    {
        $this->selectedCollections = $ids;
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
                // $productGroup = $this->product->customerGroups->where('id', $group->id)->first();

                // $pivot = $productGroup->pivot ?? null;

                $pivot = null;

                return [
                    $group->id => [
                        'customer_group_id' => $group->id,
                        'scheduling' => false,
                        'enabled' => false,
                        'status' => 'hidden',
                        'starts_at' => $pivot?->starts_at ?? null,
                        'ends_at' => $pivot?->ends_at ?? null,
                    ],
                ];
            }),
        ];
    }

    /**
     * Remove the collection by it's index.
     *
     * @param  int|string  $index
     * @return void
     */
    public function removeCollection($index)
    {
        $this->collections->forget($index);
    }

    /**
     * Return a list of available countries.
     *
     * @return Collection
     */
    public function getBrandsProperty()
    {
        return Brand::orderBy('name')->get();
    }

    /**
     * Return the category tree.
     *
     * @return Collection
     */
    public function getCollectionTreeProperty()
    {
        return ModelsCollection::get()->toTree();
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
            $this->discount->save();

            $this->discount->brands()->sync(
                $this->selectedBrands
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
                $this->selectedCollections
            );
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
                'has_errors' => false,
            ],
            [
                'title' => 'Discount Type',
                'id' => 'type',
                'has_errors' => false,
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
}
