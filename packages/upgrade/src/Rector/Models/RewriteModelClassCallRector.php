<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Models;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites `SomeModel::modelClass()` to `SomeModel::class` (spec 0041).
 *
 * Model class substitution was removed, so the `modelClass()` helper no longer
 * exists. It always resolved to the model's own class when nothing was
 * registered, so a direct `::class` reference is the equivalent.
 */
final class RewriteModelClassCallRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites `Model::modelClass()` to `Model::class`.',
            [new CodeSample(
                '$class = Product::modelClass();',
                '$class = Product::class;',
            )],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param  StaticCall  $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }

        if (! $node->name instanceof Identifier || $node->name->toString() !== 'modelClass') {
            return null;
        }

        if (! $node->class instanceof Name || $node->getArgs() !== []) {
            return null;
        }

        return new ClassConstFetch($node->class, 'class');
    }
}
