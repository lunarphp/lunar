<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Customer;
use Lunar\Filament\Forms\Components\Concerns\AppliesToExistingSelect;
use Lunar\Filament\Forms\Components\Concerns\ExcludesAttachedRecords;

/**
 * Pick a Customer. Searches across first_name, last_name, company_name
 * and email — the multi-field strategy previously bespoke in
 * `CustomerLimitationRelationManager`.
 */
class CustomerSelect extends Select
{
    use AppliesToExistingSelect;
    use ExcludesAttachedRecords;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.customer.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.customer.placeholder'));
        $this->searchable();
        $this->getSearchResultsUsing(fn (string $search): array => $this->searchCustomers($search));
        $this->getOptionLabelUsing(function ($value): ?string {
            $record = $this->lunarModel()::find($value);

            return $record ? $this->optionLabel($record) : null;
        });
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Customer::class;
    }

    public function optionLabel(Model $record): string
    {
        $name = trim(($record->first_name ?? '').' '.($record->last_name ?? ''));

        if ($record->company_name) {
            return $name !== '' ? "{$name} — {$record->company_name}" : $record->company_name;
        }

        return $name !== '' ? $name : ($record->email ?? (string) $record->getKey());
    }

    /**
     * @return array<int|string, string>
     */
    protected function searchCustomers(string $search): array
    {
        $model = $this->lunarModel();
        $words = array_filter(explode(' ', $search));

        $query = $model::query()->where(function (Builder $query) use ($words): void {
            foreach ($words as $word) {
                $query->where(function (Builder $q) use ($word): void {
                    $like = "%{$word}%";
                    $q->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('company_name', 'like', $like);
                });
            }
        });

        $results = $query->take(50)->get();

        return $this->filterAttachedRecords($results)
            ->mapWithKeys(fn (Model $record): array => [
                $record->getKey() => $this->optionLabel($record),
            ])
            ->all();
    }
}
