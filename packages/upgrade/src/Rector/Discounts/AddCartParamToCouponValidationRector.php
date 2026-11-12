<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Discounts;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Adds `?Cart $cart = null` to a consumer's `CouponValidator::validate()` and
 * `DiscountManager::validateCoupon()` (spec guest-discount-usage-limits).
 *
 * Both contracts gained the parameter so a per-customer limit can be checked
 * against a given cart. PHP rejects an implementation that declares fewer
 * parameters than its interface or parent. Matched against the v1 and v2
 * class strings so the rewrite applies whether or not `RenameClassRector` has
 * already run.
 */
final class AddCartParamToCouponValidationRector extends AbstractRector
{
    private const CART_CLASS = 'Lunar\\Core\\Models\\Cart';

    /**
     * Method name => the types whose implementations declare it.
     */
    private const METHODS = [
        'validate' => [
            'Lunar\\Core\\Contracts\\CouponValidator',
            'Lunar\\Base\\Validation\\CouponValidatorInterface',
            'Lunar\\Base\\Validation\\CouponValidator',
        ],
        'validateCoupon' => [
            'Lunar\\Core\\Contracts\\DiscountManager',
            'Lunar\\Base\\DiscountManagerInterface',
            'Lunar\\Managers\\DiscountManager',
        ],
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds the optional ?Cart $cart parameter to CouponValidator::validate() and DiscountManager::validateCoupon() implementations.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
class MyCouponValidator implements \Lunar\Core\Contracts\CouponValidator
{
    public function validate(string $coupon): bool
    {
        return true;
    }
}
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
class MyCouponValidator implements \Lunar\Core\Contracts\CouponValidator
{
    public function validate(string $coupon, ?\Lunar\Core\Models\Cart $cart = null): bool
    {
        return true;
    }
}
CODE_SAMPLE,
            )],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param  Class_  $node
     */
    public function refactor(Node $node): ?Node
    {
        $changed = false;

        foreach (self::METHODS as $methodName => $types) {
            $method = $node->getMethod($methodName);

            if (! $method instanceof ClassMethod || count($method->params) !== 1) {
                continue;
            }

            if (! $this->isAnyOf($node, $types)) {
                continue;
            }

            $method->params[] = new Param(
                new Variable('cart'),
                new ConstFetch(new Name('null')),
                new NullableType(new FullyQualified(self::CART_CLASS)),
            );

            $changed = true;
        }

        return $changed ? $node : null;
    }

    /**
     * @param  list<string>  $types
     */
    private function isAnyOf(Class_ $class, array $types): bool
    {
        $declared = array_filter([$class->extends, ...$class->implements]);

        foreach ($types as $type) {
            if ($this->isObjectType($class, new ObjectType($type))) {
                return true;
            }

            foreach ($declared as $name) {
                if ($this->isName($name, $type)) {
                    return true;
                }
            }
        }

        return false;
    }
}
