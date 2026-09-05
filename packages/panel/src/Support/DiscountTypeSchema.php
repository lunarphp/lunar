<?php

namespace Lunar\Panel\Support;

use Lunar\Core\Contracts\DiscountType;
use Lunar\Core\Facades\Discounts;
use Lunar\Panel\Contracts\DiscountTypeForm;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\Support\DiscountTypeForms\RawDataForm;

/**
 * Resolves the discount-type registry against the panel forms sections have
 * registered, so the Discounts section renders whatever types are installed
 * rather than a hardcoded list.
 *
 * A type with no registered form falls back to RawDataForm, which keeps an
 * unknown type editable instead of making it disappear from the picker.
 */
class DiscountTypeSchema
{
    /** @var array<class-string, DiscountTypeForm>|null */
    protected ?array $forms = null;

    /**
     * Every registered type as a prop bundle for the type picker and the
     * config block.
     *
     * @return array<int, array{class: class-string, label: string, component: string, buckets: array<int, string>}>
     */
    public function all(): array
    {
        return collect(Discounts::getTypes())
            ->map(fn (DiscountType $type) => $this->describe($type::class, $type->getName()))
            ->values()
            ->all();
    }

    /**
     * The prop bundle for one type. Falls back to the stored class name as the
     * label when the type is no longer registered, so an orphaned discount
     * still renders.
     *
     * @param  class-string  $discountType
     * @return array{class: class-string, label: string, component: string, buckets: array<int, string>}
     */
    public function describe(string $discountType, ?string $label = null): array
    {
        $form = $this->formFor($discountType);

        return [
            'class' => $discountType,
            'label' => $label ?? $this->labelFor($discountType),
            'component' => $form->component(),
            'buckets' => $form->targetBuckets(),
        ];
    }

    /**
     * The form for a type, or the raw JSON editor when none is registered.
     *
     * @param  class-string  $discountType
     */
    public function formFor(string $discountType): DiscountTypeForm
    {
        $this->forms ??= [];

        return $this->forms[$discountType] ??= $this->resolveForm($discountType);
    }

    /**
     * Whether the type is still registered. A discount can outlive the package
     * that registered its type.
     *
     * @param  class-string  $discountType
     */
    public function isRegistered(string $discountType): bool
    {
        return collect(Discounts::getTypes())
            ->contains(fn (DiscountType $type) => $type::class === $discountType);
    }

    /** @param class-string $discountType */
    protected function labelFor(string $discountType): string
    {
        $type = collect(Discounts::getTypes())
            ->first(fn (DiscountType $type) => $type::class === $discountType);

        return $type?->getName() ?? $discountType;
    }

    /** @param class-string $discountType */
    protected function resolveForm(string $discountType): DiscountTypeForm
    {
        $formClass = Panel::discountTypeForms()[$discountType] ?? RawDataForm::class;

        return app($formClass);
    }
}
