<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Price;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Detects whether a given `$receiver->col` PropertyFetch matches one of
 * the configured "money attribute" combinations contributed by spec 0012.
 *
 * Inheritance-aware: downstream subclasses of `Order`, `OrderLine`, etc.
 * still pick up the rewrite.
 */
final class MoneyAttributeResolver
{
    public function __construct(
        private readonly NodeTypeResolver $nodeTypeResolver,
    ) {}

    /**
     * Returns the matched column name when the property fetch targets a
     * money attribute on a configured class, otherwise `null`.
     *
     * @param  array<class-string, list<string>>  $map
     */
    public function resolve(PropertyFetch $propertyFetch, array $map): ?string
    {
        if (! $propertyFetch->name instanceof Identifier) {
            return null;
        }

        $attribute = $propertyFetch->name->toString();
        $receiverType = $this->nodeTypeResolver->getType($propertyFetch->var);

        foreach ($map as $class => $columns) {
            if (! in_array($attribute, $columns, true)) {
                continue;
            }

            if ((new ObjectType($class))->isSuperTypeOf($receiverType)->yes()) {
                return $attribute;
            }
        }

        return null;
    }
}
