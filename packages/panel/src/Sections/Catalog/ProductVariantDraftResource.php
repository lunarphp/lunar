<?php

namespace Lunar\Panel\Sections\Catalog;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductVariant;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\VariantFields;

class ProductVariantDraftResource extends DraftableResource
{
    public function __construct(
        protected UpdatesProductVariant $updatesProductVariant,
        protected VariantFields $variantFields,
    ) {}

    public function model(): string
    {
        return ProductVariant::class;
    }

    public function fields(): array
    {
        return $this->variantFields->fields();
    }

    public function currentValues(Model $record): array
    {
        /** @var ProductVariant $record */
        return $this->variantFields->values($record);
    }

    public function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (! str_starts_with($key, AttributeSchema::PREFIX)) {
                $data[$key] = $this->variantFields->normalizeValue($key, $value);
            }
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var ProductVariant $record */
        return $this->variantFields->rules($record);
    }

    public function commit(Model $record, array $values): void
    {
        /** @var ProductVariant $record */
        $this->updatesProductVariant->execute(
            $record,
            $this->variantFields->commitPayload($record, $values),
        );
    }

    public function labels(): array
    {
        return $this->variantFields->labels();
    }
}
