<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Forms\FormSlice;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A variant's images: one `variant_media:ids` field holding an ordered
 * selection from its product's media pool. The first id is the variant's
 * primary image.
 */
class ProductVariantMediaSlice extends FormSlice
{
    public const KEY = 'variant_media';

    public function model(): string
    {
        return ProductVariant::class;
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function fields(Model $record): array
    {
        return ['ids'];
    }

    public function currentValues(Model $record): array
    {
        /** @var ProductVariant $record */
        $ids = $record->exists
            ? $record->images()->orderBy('position')->pluck((new Media)->getQualifiedKeyName())->map(fn ($id): int => (int) $id)->all()
            : [];

        return ['ids' => $ids];
    }

    public function normalize(array $data): array
    {
        if (array_key_exists('ids', $data)) {
            $data['ids'] = array_values(array_map(
                fn (mixed $id): int => (int) $id,
                array_filter((array) $data['ids'], 'is_numeric'),
            ));
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var ProductVariant $record */
        return [
            'ids' => ['present', 'array'],
            'ids.*' => [
                'integer',
                'distinct',
                Rule::exists((new Media)->getTable(), 'id')
                    ->where('model_type', (new Product)->getMorphClass())
                    ->where('model_id', $record->product_id),
            ],
        ];
    }

    public function commit(Model $record, array $values): void
    {
        /** @var ProductVariant $record */
        $sync = collect($this->normalize($values)['ids'] ?? [])
            ->values()
            ->mapWithKeys(fn (int $id, int $index) => [$id => [
                'primary' => $index === 0,
                'position' => $index + 1,
            ]])
            ->all();

        $record->images()->sync($sync);
    }

    public function labels(): array
    {
        return ['ids' => 'panel::products.section_variant_media'];
    }
}
