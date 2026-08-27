<?php

namespace Lunar\Panel\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\Models\Order;

class RefundOrderRequest extends FormRequest
{
    /**
     * Guard before validation — an empty-selection payload would otherwise
     * fail validation before the "nothing to refund" guard is reached.
     */
    public function authorize(): bool
    {
        /** @var Order $order */
        $order = $this->route('order');

        return RefundOrder::canRun($order);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'integer'],
            'lines' => ['array'],
            'lines.*.order_line_id' => ['required', 'integer'],
            'lines.*.quantity' => ['required', 'integer', 'min:0'],
            'shipping' => ['boolean'],
            'adjustment' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'notify' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $lines = collect($this->input('lines', []))->filter(fn (array $line) => (int) ($line['quantity'] ?? 0) > 0);
            $adjustment = (float) $this->input('adjustment', 0);

            if ($lines->isEmpty() && ! $this->boolean('shipping') && $adjustment <= 0) {
                $validator->errors()->add('lines', __('panel::orders.refund_nothing_selected'));
            }
        });
    }

    /**
     * @return array<int, array{order_line_id: int, quantity: int}>
     */
    public function lines(): array
    {
        return collect($this->input('lines', []))
            ->filter(fn (array $line) => (int) ($line['quantity'] ?? 0) > 0)
            ->map(fn (array $line) => [
                'order_line_id' => (int) $line['order_line_id'],
                'quantity' => (int) $line['quantity'],
            ])
            ->values()
            ->all();
    }
}
