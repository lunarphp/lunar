<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Shipping;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * v1's `ShippingOption` `collect` flag becomes a pickup location (spec 0083):
 * `collect: true` collects from the default location, which is where v1
 * pickups were fulfilled from, and `$option->collect` becomes `isPickup()`.
 * Matched against both the v1 and v2 class strings so the rewrite applies
 * regardless of whether `RenameClassRector` has already run.
 */
final class RewriteShippingOptionCollectRector extends AbstractRector
{
    private const CLASSES = [
        'Lunar\\Core\\DataTypes\\ShippingOption',
        'Lunar\\DataTypes\\ShippingOption',
    ];

    public function __construct(
        private readonly ValueResolver $valueResolver,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites the ShippingOption collect flag to a pickup location and isPickup().',
            [new CodeSample(
                <<<'CODE_SAMPLE'
new ShippingOption(name: 'Pickup', collect: true);
$option->collect;
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
new ShippingOption(name: 'Pickup', pickupLocation: \Lunar\Core\Models\Location::getDefault());
$option->isPickup();
CODE_SAMPLE,
            )],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class, PropertyFetch::class];
    }

    /**
     * @param  New_|PropertyFetch  $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof PropertyFetch) {
            return $this->isName($node->name, 'collect') && $this->isShippingOption($node->var)
                ? new MethodCall($node->var, 'isPickup')
                : null;
        }

        if (! $node->class instanceof Name || ! $this->isShippingOption($node->class)) {
            return null;
        }

        $changed = false;

        foreach ($node->args as $index => $arg) {
            if (! $arg instanceof Arg || ! $arg->name instanceof Identifier || $arg->name->toString() !== 'collect') {
                continue;
            }

            $changed = true;

            if ($this->valueResolver->isFalse($arg->value)) {
                unset($node->args[$index]);

                continue;
            }

            $arg->name = new Identifier('pickupLocation');
            $arg->value = $this->valueResolver->isTrue($arg->value)
                ? $this->defaultLocation()
                : new Ternary($arg->value, $this->defaultLocation(), new ConstFetch(new Name('null')));
        }

        if ($changed) {
            $node->args = array_values($node->args);
        }

        return $changed ? $node : null;
    }

    protected function defaultLocation(): Expr
    {
        return new StaticCall(new FullyQualified('Lunar\\Core\\Models\\Location'), 'getDefault');
    }

    protected function isShippingOption(Node $node): bool
    {
        foreach (self::CLASSES as $shippingOptionClass) {
            if ($this->isObjectType($node, new ObjectType($shippingOptionClass))) {
                return true;
            }
        }

        return false;
    }
}
