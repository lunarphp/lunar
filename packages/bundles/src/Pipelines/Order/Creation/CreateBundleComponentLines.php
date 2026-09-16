<?php

namespace Lunar\Bundles\Pipelines\Order\Creation;

use ArrayObject;
use Closure;
use Illuminate\Support\Collection;
use Lunar\Bundles\Contracts\Actions\ResolvesBundleSelection;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Support\ResolvesCartCustomerGroups;
use Lunar\Bundles\ValueObjects\SelectedComponent;
use Lunar\Core\Contracts\PricingManager;
use Lunar\Core\Exceptions\MissingCurrencyPriceException;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Pricing\PriceCalculatorInterface;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

/**
 * Give every bundle line its component lines (spec 0085, section 2.6). The
 * parent keeps the money and becomes `type = bundle`, `requires_fulfilment =
 * false`; the components carry purchasable, quantity and fulfilment flags with
 * zero money, so fulfilment and stock commitment see the parts unchanged.
 * Idempotent: an `orderIdToUpdate` re-run replaces the component lines.
 */
class CreateBundleComponentLines
{
    use ResolvesCartCustomerGroups;

    public function __construct(
        protected ResolvesBundleSelection $resolver,
        protected PricingManager $pricing,
        protected PriceCalculatorInterface $calculator,
    ) {}

    /**
     * @param  Closure(Order): mixed  $next
     */
    public function handle(Order $order, Closure $next): mixed
    {
        $order->loadMissing(['lines.purchasable', 'currency', 'cart']);

        foreach ($order->lines->whereNull('parent_line_id') as $line) {
            $variant = $line->purchasable;

            if (! $variant instanceof ProductVariant) {
                continue;
            }

            /** @var ?Bundle $bundle */
            $bundle = $variant->loadMissing('bundle')->bundle;

            if (! $bundle) {
                continue;
            }

            $this->createComponentLines($order, $line, $bundle);
        }

        $order->unsetRelation('lines');

        return $next($order);
    }

    protected function createComponentLines(Order $order, OrderLine $parent, Bundle $bundle): void
    {
        $parent->forceFill([
            'type' => 'bundle',
            'requires_fulfilment' => false,
        ])->save();

        $parent->components()->get()->each(fn (OrderLine $component) => $component->delete());

        $meta = $parent->meta instanceof ArrayObject ? $parent->meta->getArrayCopy() : (array) $parent->meta;
        $selection = $this->resolver->execute($bundle, $meta);

        $bundle->loadMissing(['components.variant.product', 'components.variant.values']);

        $currency = $order->currency;
        $customerGroups = $this->customerGroupsFor($order->cart);
        $weights = [];
        $attributes = [];

        foreach ($selection->components as $index => $component) {
            /** @var SelectedComponent $component */
            $variant = $component->variant;
            $quantity = $component->quantity * $parent->quantity;

            $weights[$index] = $this->currentUnitPrice($variant, $customerGroups) * $quantity;

            $attributes[$index] = [
                'purchasable_type' => $variant->getMorphClass(),
                'purchasable_id' => $variant->getKey(),
                'type' => $variant->getType(),
                'requires_shipping' => $variant->isShippable(),
                'requires_fulfilment' => $variant->requiresFulfilment(),
                'description' => $variant->getDescription(),
                'option' => $variant->getOption(),
                'identifier' => $variant->getIdentifier(),
                'unit_price' => 0,
                'unit_quantity' => $variant->getUnitQuantity(),
                'quantity' => $quantity,
                'sub_total' => 0,
                'discount_total' => 0,
                'tax_breakdown' => new TaxBreakdown,
                'tax_total' => 0,
                'total' => 0,
                'notes' => null,
            ];
        }

        // Reporting only: the parent's total spread across the parts in
        // proportion to their current prices, exact to the minor unit.
        $allocated = $this->calculator->distribute((int) $parent->total, $weights, $currency);

        foreach ($attributes as $index => $lineAttributes) {
            $parent->components()->create($lineAttributes + [
                'order_id' => $order->getKey(),
                'meta' => ['allocated_total' => $allocated[$index] ?? 0],
            ]);
        }
    }

    /**
     * @param  Collection<int, CustomerGroup>  $customerGroups
     */
    protected function currentUnitPrice(ProductVariant $variant, $customerGroups): int
    {
        try {
            return (int) $this->pricing
                ->qty(1)
                ->customerGroups($customerGroups)
                ->for($variant)
                ->get()
                ->matched
                ->price;
        } catch (MissingCurrencyPriceException) {
            return 0;
        }
    }
}
