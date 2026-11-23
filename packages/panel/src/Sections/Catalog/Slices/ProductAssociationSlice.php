<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Panel\Forms\FormSlice;

/**
 * A product's associations, one field per registered association type
 * (`association:cross-sell`), each an ordered list of target product ids.
 * Linking, reordering and unlinking are draft edits: they land with the rest
 * of the product on Save and conflict per type.
 */
class ProductAssociationSlice extends FormSlice
{
    public const KEY = 'association';

    /** Lang keys for the built-in types; a registered custom type uses its enum label. */
    protected const LABELS = [
        'alternate' => 'panel::products.assoc_related',
        'cross-sell' => 'panel::products.assoc_cross_sell',
        'up-sell' => 'panel::products.assoc_up_sell',
    ];

    public function model(): string
    {
        return Product::class;
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function fields(Model $record): array
    {
        return array_map('strval', array_keys(ProductAssociation::getTypes()));
    }

    public function currentValues(Model $record): array
    {
        /** @var Product $record */
        $targets = $record->exists
            ? $record->associations()->get(['type', 'product_target_id'])
                ->groupBy(fn (ProductAssociation $association): string => (string) $association->type)
            : collect();

        $values = [];

        foreach ($this->fields($record) as $type) {
            $values[$type] = $this->idList($targets->get($type, collect())->pluck('product_target_id')->all());
        }

        return $values;
    }

    public function normalize(array $data): array
    {
        return array_map(fn (mixed $ids): array => $this->idList((array) $ids), $data);
    }

    public function rules(Model $record): array
    {
        $rules = [];

        foreach ($this->fields($record) as $type) {
            $rules[$type] = ['nullable', 'array'];
            $rules[$type.'.*'] = [
                'integer',
                'distinct',
                Rule::exists((new Product)->getTable(), 'id'),
                ...($record->exists ? [Rule::notIn([$record->getKey()])] : []),
            ];
        }

        return $rules;
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        foreach ($this->normalize($values) as $type => $ids) {
            $record->syncAssociations((string) $type, $ids);
        }
    }

    public function labels(): array
    {
        return collect(ProductAssociation::getTypes())
            ->mapWithKeys(fn (string $label, string $type): array => [$type => self::LABELS[$type] ?? $label])
            ->all();
    }

    /**
     * Integer ids in their given order, duplicates dropped.
     *
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function idList(array $ids): array
    {
        return array_values(array_unique(array_map('intval', $ids)));
    }
}
