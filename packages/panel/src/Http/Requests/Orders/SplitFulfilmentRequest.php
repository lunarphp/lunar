<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Models\Fulfilment;

class SplitFulfilmentRequest extends FormRequest
{
    /**
     * Guard before validation — the move rules only make sense against a
     * still-outstanding fulfilment.
     */
    public function authorize(): bool
    {
        /** @var Fulfilment $fulfilment */
        $fulfilment = $this->route('fulfilment');

        return SplitFulfilment::canRun($fulfilment);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'moves' => ['required', 'array', 'min:1'],
            'moves.*' => ['integer', 'min:0'],
        ];
    }

    /**
     * Every move must reference a line allocated to this fulfilment and stay
     * within its allocation; at least one unit must move, and at least one
     * must stay (moving everything is a merge, not a split).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Fulfilment $fulfilment */
            $fulfilment = $this->route('fulfilment');
            $allocated = $fulfilment->lines->pluck('quantity', 'order_line_id');
            $moves = $this->moves();

            foreach ($moves as $orderLineId => $quantity) {
                $max = $allocated->get($orderLineId);

                if ($max === null || $quantity > $max) {
                    $validator->errors()->add('moves', __('panel::orders.split_moves_invalid'));

                    return;
                }
            }

            $total = array_sum($moves);

            if ($total < 1) {
                $validator->errors()->add('moves', __('panel::orders.split_nothing_selected'));
            } elseif ($total >= (int) $allocated->sum()) {
                $validator->errors()->add('moves', __('panel::orders.split_leaves_nothing'));
            }
        });
    }

    /**
     * The non-zero quantities to move out, keyed by order line id.
     *
     * @return array<int, int>
     */
    public function moves(): array
    {
        return collect($this->input('moves', []))
            ->map(fn ($quantity) => (int) $quantity)
            ->filter(fn (int $quantity) => $quantity > 0)
            ->all();
    }
}
