<?php

namespace Lunar\Panel\Support\DiscountTypeForms;

use Lunar\Core\Models\Currency;
use Lunar\Panel\Contracts\DiscountTypeForm;

/**
 * BuyXGetY works off quantities rather than money, and reads its products from
 * the condition and reward buckets rather than the limitation ones the
 * line-targeting types use.
 */
class BuyXGetYForm implements DiscountTypeForm
{
    public function component(): string
    {
        return 'BuyXGetYForm';
    }

    public function targetBuckets(): array
    {
        return ['condition', 'reward'];
    }

    public function toForm(array $data): array
    {
        return [
            'min_qty' => (int) ($data['min_qty'] ?? 1),
            'reward_qty' => (int) ($data['reward_qty'] ?? 1),
            'max_reward_qty' => isset($data['max_reward_qty']) ? (int) $data['max_reward_qty'] : null,
            'automatically_add_rewards' => (bool) ($data['automatically_add_rewards'] ?? false),
        ];
    }

    public function toStorage(array $data): array
    {
        return [
            'min_qty' => (int) ($data['min_qty'] ?? 1),
            'reward_qty' => (int) ($data['reward_qty'] ?? 1),
            // Null rather than zero: getRewardQuantity() treats a falsy cap as
            // "uncapped", and null says that plainly.
            'max_reward_qty' => filled($data['max_reward_qty'] ?? null) ? (int) $data['max_reward_qty'] : null,
            'automatically_add_rewards' => (bool) ($data['automatically_add_rewards'] ?? false),
        ];
    }

    public function rules(): array
    {
        return [
            'min_qty' => ['required', 'integer', 'min:1'],
            'reward_qty' => ['required', 'integer', 'min:1'],
            'max_reward_qty' => ['nullable', 'integer', 'min:1'],
            'automatically_add_rewards' => ['boolean'],
        ];
    }

    public function summary(array $data, ?Currency $currency): ?string
    {
        $minQty = (int) ($data['min_qty'] ?? 0);
        $rewardQty = (int) ($data['reward_qty'] ?? 0);

        if (! $minQty || ! $rewardQty) {
            return null;
        }

        return __('panel::discounts.summary_buy_x_get_y', ['buy' => $minQty, 'get' => $rewardQty]);
    }
}
