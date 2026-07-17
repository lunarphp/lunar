<?php

namespace Lunar\Panel\Sections\Sales;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Http\Requests\Customers\CustomerRequest;

class CustomerDraftResource extends DraftableResource
{
    /**
     * Text columns that are nullable in the database but bound to inputs that
     * submit an empty string; normalised to null so equality against the
     * stored value holds.
     */
    private const NULLABLE_TEXT_FIELDS = ['title', 'company_name', 'tax_identifier', 'account_ref'];

    public function __construct(protected UpdatesCustomer $updatesCustomer) {}

    public function model(): string
    {
        return Customer::class;
    }

    public function fields(): array
    {
        return [
            'title',
            'first_name',
            'last_name',
            'company_name',
            'tax_identifier',
            'account_ref',
            'customer_group_ids',
        ];
    }

    public function currentValues(Model $record): array
    {
        /** @var Customer $record */
        return [
            'title' => $record->title,
            'first_name' => $record->first_name,
            'last_name' => $record->last_name,
            'company_name' => $record->company_name,
            'tax_identifier' => $record->tax_identifier,
            'account_ref' => $record->account_ref,
            'customer_group_ids' => $this->sortedIds($record->customerGroups()->allRelatedIds()->all()),
        ];
    }

    public function normalize(array $data): array
    {
        foreach (self::NULLABLE_TEXT_FIELDS as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if (array_key_exists('customer_group_ids', $data)) {
            $data['customer_group_ids'] = $this->sortedIds((array) $data['customer_group_ids']);
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        return (new CustomerRequest)->rules();
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Customer $record */
        $this->updatesCustomer->execute(
            $record,
            collect($values)->except('customer_group_ids')->all(),
            $values['customer_group_ids'] ?? [],
        );
    }

    public function labels(): array
    {
        return [
            'title' => 'panel::customers.field_title',
            'first_name' => 'panel::customers.field_first_name',
            'last_name' => 'panel::customers.field_last_name',
            'company_name' => 'panel::customers.field_company_name',
            'tax_identifier' => 'panel::customers.field_tax_identifier',
            'account_ref' => 'panel::customers.field_account_ref',
            'customer_group_ids' => 'panel::customers.customer_groups',
        ];
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function sortedIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        sort($ids);

        return $ids;
    }
}
