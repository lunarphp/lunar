<?php

namespace GetCandy\Discounts\Http\Livewire;

use GetCandy\Discounts\Models\Discount;
use GetCandy\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountsIndex extends Component
{
    use WithPagination;

    public function getDiscountsProperty()
    {
        return Discount::paginate(25);
    }

    public function getStatus($discount)
    {
        $label = 'Inactive';

        if ($discount->starts_at->isPast()) {
            if ($discount->ends_at) {
                if ($discount->ends_at->isPast()) {
                    $label = __('discounts::index.statuses.expired', [
                        'date' => $discount->ends_at->format('jS M Y h:ma'),
                    ]);
                } else {
                    $label = __('discounts::index.statuses.expiring', [
                        'date' => $discount->ends_at->format('jS M Y h:ma'),
                    ]);
                }
            } else {
                $label = __('discounts::index.statuses.active');
            }
        } else {
            $label = __('discounts::index.statuses.starting', [
                'date' => $discount->starts_at->format('jS M Y h:ma'),
            ]);
        }

        return <<<EOT
            <span class="text-green-500">$label</span>
        EOT;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('discounts::discounts.index')
            ->layout('adminhub::layouts.app');
    }
}
