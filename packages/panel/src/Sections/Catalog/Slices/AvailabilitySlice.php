<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Models\Product;
use Lunar\Panel\Drafts\Concerns\NormalizesDraftValues;
use Lunar\Panel\Forms\FormSlice;
use Lunar\Panel\Support\AvailabilitySchema;

/**
 * One side of a product's availability (its channel rows or its
 * customer-group rows), one field per row id. Each side is its own slice
 * because each owns its own namespace; both commit through the product
 * update action's pivot maps.
 */
abstract class AvailabilitySlice extends FormSlice
{
    use NormalizesDraftValues;

    protected AvailabilitySchema $availabilitySchema;

    public function __construct(
        protected UpdatesProduct $updatesProduct,
        AvailabilitySchema $availabilitySchema,
    ) {
        // Product customer-group rows carry the pivot's extra purchasable flag.
        $this->availabilitySchema = $availabilitySchema->withPurchasable();
    }

    /** The schema prefix this slice's rows carry, e.g. `channel:`. */
    abstract protected function side(): string;

    public function model(): string
    {
        return Product::class;
    }

    public function key(): string
    {
        return rtrim($this->side(), ':');
    }

    public function fields(Model $record): array
    {
        return $this->stripPrefixFromList($this->availabilitySchema->fields($this->side()), $this->side());
    }

    public function currentValues(Model $record): array
    {
        return $this->stripPrefix($this->availabilitySchema->values($record, $this->side()), $this->side());
    }

    public function normalize(array $data): array
    {
        return array_map(fn (mixed $value) => $this->availabilitySchema->normalizeValue((array) $value), $data);
    }

    public function rules(Model $record): array
    {
        return $this->stripPrefix($this->availabilitySchema->rules($this->side()), $this->side());
    }

    public function labels(): array
    {
        return $this->stripPrefix($this->availabilitySchema->labels($this->side()), $this->side());
    }
}
