<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Orders;

use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Models\Order;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites `$order->refund($transactionId, $amount, $notes)` to
 * `$order->refund(new RefundRequest(transactionId: ..., adjustment: ..., notes: ...))`
 * (spec 0028) — `Order::refund()` moved from three positional scalars to a
 * structured `RefundRequest`. An amount-only refund (no line allocation) is
 * expressed as the whole amount riding on `adjustment`, so this rewrite is
 * behaviourally exact, not just type-compatible.
 */
final class RewriteOrderRefundCallRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites the three positional arguments of Order::refund() into a RefundRequest.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
$order->refund($transactionId, $amount, $notes);
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$order->refund(new \Lunar\Core\DataObjects\RefundRequest(transactionId: $transactionId, adjustment: $amount, notes: $notes));
CODE_SAMPLE,
            )],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param  MethodCall  $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }

        if (! $node->name instanceof Identifier || $node->name->toString() !== 'refund') {
            return null;
        }

        $args = $node->getArgs();

        // Already migrated (single arg) or an unrecognised shape.
        if (count($args) < 2 || count($args) > 3) {
            return null;
        }

        foreach ($args as $arg) {
            if ($arg->name !== null) {
                // Already using named arguments — leave alone.
                return null;
            }
        }

        $receiverType = $this->nodeTypeResolver->getType($node->var);

        if (! (new ObjectType(Order::class))->isSuperTypeOf($receiverType)->yes()) {
            return null;
        }

        $requestArgs = [
            new Arg($args[0]->value, name: new Identifier('transactionId')),
            new Arg($args[1]->value, name: new Identifier('adjustment')),
        ];

        if (isset($args[2])) {
            $requestArgs[] = new Arg($args[2]->value, name: new Identifier('notes'));
        }

        $node->args = [
            new Arg(new New_(new FullyQualified(RefundRequest::class), $requestArgs)),
        ];

        return $node;
    }
}
