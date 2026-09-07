<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Shipping;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Renames the `collect:` named argument to `pickup:` on `ShippingOption`
 * construction (spec 0077) — the constructor-promoted `$collect` flag became
 * `$pickup`. The parameter kept its position, so positional construction is
 * unaffected; the property fetch (`$option->collect`) is renamed by
 * `RenamePropertyRector` via `LunarSetList::V1_TO_V2_PROPERTY_RENAMES`.
 * Matched against both the v1 and v2 class strings so the rewrite applies
 * regardless of whether `RenameClassRector` has already run.
 */
final class RenameShippingOptionCollectArgRector extends AbstractRector
{
    private const CLASSES = [
        'Lunar\\Core\\DataTypes\\ShippingOption',
        'Lunar\\DataTypes\\ShippingOption',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Renames the collect: named argument to pickup: on ShippingOption construction.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
new ShippingOption(name: 'Pickup', collect: true);
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
new ShippingOption(name: 'Pickup', pickup: true);
CODE_SAMPLE,
            )],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param  New_  $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->class instanceof Name || ! $this->isShippingOption($node->class)) {
            return null;
        }

        $renamed = false;

        foreach ($node->getArgs() as $arg) {
            if ($arg->name instanceof Identifier && $arg->name->toString() === 'collect') {
                $arg->name = new Identifier('pickup');
                $renamed = true;
            }
        }

        return $renamed ? $node : null;
    }

    protected function isShippingOption(Name $class): bool
    {
        foreach (self::CLASSES as $shippingOptionClass) {
            if ($this->isObjectType($class, new ObjectType($shippingOptionClass))) {
                return true;
            }
        }

        return false;
    }
}
