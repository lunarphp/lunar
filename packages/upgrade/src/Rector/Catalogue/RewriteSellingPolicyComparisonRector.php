<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Catalogue;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Spec 0082 replaced a variant's selling mode (`purchasable` in v1,
 * `selling_policy` in the v2 alphas) with the `continue_selling` and
 * `sell_against_incoming` flags. Rewrites an equality check of the mode
 * against one of its values, as a string or a `SellingPolicy` case, into the
 * matching flag expression. Other uses of the mode are left for manual review.
 */
final class RewriteSellingPolicyComparisonRector extends AbstractRector
{
    private const VARIANT_CLASSES = [
        'Lunar\\Core\\Models\\ProductVariant',
        'Lunar\\Models\\ProductVariant',
    ];

    private const PROPERTIES = ['purchasable', 'selling_policy'];

    private const ENUM_CLASS = 'Lunar\\Core\\Enums\\SellingPolicy';

    /**
     * @var array<string, string>
     */
    private const MODES = [
        'always' => 'always',
        'in_stock' => 'in_stock',
        'in_stock_or_on_backorder' => 'backorder',
        'in_stock_or_backorder' => 'backorder',
        'Always' => 'always',
        'InStock' => 'in_stock',
        'InStockOrOnBackorder' => 'backorder',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites selling-policy comparisons on a ProductVariant into the continue_selling and sell_against_incoming flags.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
$variant->purchasable === 'always';
$variant->purchasable === 'in_stock_or_on_backorder';
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$variant->continue_selling;
!$variant->continue_selling && $variant->sell_against_incoming;
CODE_SAMPLE,
            )],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, Equal::class, NotIdentical::class, NotEqual::class];
    }

    /**
     * @param  Identical|Equal|NotIdentical|NotEqual  $node
     */
    public function refactor(Node $node): ?Node
    {
        [$fetch, $mode] = $this->match($node->left, $node->right) ?? $this->match($node->right, $node->left) ?? [null, null];

        if (! $fetch || ! $mode) {
            return null;
        }

        $continueSelling = new PropertyFetch($fetch->var, 'continue_selling');
        $sellAgainstIncoming = new PropertyFetch($fetch->var, 'sell_against_incoming');

        $expression = match ($mode) {
            'always' => $continueSelling,
            'in_stock' => new BooleanAnd(new BooleanNot($continueSelling), new BooleanNot($sellAgainstIncoming)),
            'backorder' => new BooleanAnd(new BooleanNot($continueSelling), $sellAgainstIncoming),
        };

        return $node instanceof NotIdentical || $node instanceof NotEqual
            ? new BooleanNot($expression)
            : $expression;
    }

    /**
     * @return array{0: PropertyFetch, 1: string}|null
     */
    private function match(Expr $fetch, Expr $value): ?array
    {
        if (! $fetch instanceof PropertyFetch
            || ! $fetch->name instanceof Identifier
            || ! in_array($fetch->name->toString(), self::PROPERTIES, true)
            || ! $this->isVariant($fetch->var)) {
            return null;
        }

        $mode = match (true) {
            $value instanceof String_ => self::MODES[$value->value] ?? null,
            $value instanceof ClassConstFetch
                && $value->class instanceof Name
                && $this->isName($value->class, self::ENUM_CLASS)
                && $value->name instanceof Identifier => self::MODES[$value->name->toString()] ?? null,
            default => null,
        };

        return $mode ? [$fetch, $mode] : null;
    }

    private function isVariant(Expr $expr): bool
    {
        foreach (self::VARIANT_CLASSES as $variantClass) {
            if ($this->isObjectType($expr, new ObjectType($variantClass))) {
                return true;
            }
        }

        return false;
    }
}
