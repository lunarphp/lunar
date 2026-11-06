<?php

namespace LunarPanelExample\Drafts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Forms\FormSlice;

/**
 * Adds a loyalty tier to the customer form. Registered through
 * ExampleSection::formExtensions(), so the panel places it under
 * `addon:example-addon:` and the LoyaltyCard component binds to it with
 * useFormSlice('example-addon'). On the customer edit page the value
 * autosaves, restores, conflicts and commits with the customer's own fields
 * through the edit draft; this class only ever sees its bare `tier` field.
 *
 * The tier is kept in the customer's meta column so the example stays
 * schema-free. A real add-on would persist to its own table through its
 * own action.
 */
class LoyaltyTierSlice extends FormSlice
{
    public const TIERS = ['bronze', 'silver', 'gold'];

    public function __construct(protected UpdatesCustomer $updatesCustomer) {}

    public function model(): string
    {
        return Customer::class;
    }

    public function key(): string
    {
        return 'example-addon';
    }

    public function fields(Model $record): array
    {
        return ['tier'];
    }

    public function currentValues(Model $record): array
    {
        /** @var Customer $record */
        return ['tier' => $record->meta['loyalty_tier'] ?? null];
    }

    public function normalize(array $data): array
    {
        // The select submits '' for "no tier"; the stored value is null.
        if (array_key_exists('tier', $data) && $data['tier'] === '') {
            $data['tier'] = null;
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        return ['tier' => ['nullable', Rule::in(self::TIERS)]];
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Customer $record */
        $meta = $record->meta?->getArrayCopy() ?? [];
        $meta['loyalty_tier'] = $values['tier'] ?? null;

        $this->updatesCustomer->execute($record, ['meta' => $meta]);
    }

    public function labels(): array
    {
        return ['tier' => 'example-addon::example.loyalty_tier'];
    }
}
