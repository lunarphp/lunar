<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Media;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Retypes getThumbnailImage() from string to ?string and replaces an empty
 * string fallback with null.
 *
 * v2's HasThumbnailImage contract returns null when a record has no
 * thumbnail. A consumer implementation that still declares string, or still
 * returns '', no longer matches that contract.
 */
final class RetypeGetThumbnailImageRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Retypes getThumbnailImage() from string to ?string and returns null instead of an empty string.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
public function getThumbnailImage(): string
{
    return $this->thumbnail?->getUrl('small') ?? '';
}
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
public function getThumbnailImage(): ?string
{
    return $this->thumbnail?->getUrl('small') ?? null;
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
        return [ClassMethod::class];
    }

    /**
     * @param  ClassMethod  $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'getThumbnailImage')) {
            return null;
        }

        $changed = false;

        if ($this->isStringType($node->returnType)) {
            $node->returnType = new NullableType(new Identifier('string'));
            $changed = true;
        }

        $this->traverseNodesWithCallable($node->stmts ?? [], function (Node $subNode) use (&$changed): ?int {
            if (! $subNode instanceof Return_ || $subNode->expr === null) {
                return null;
            }

            if ($subNode->expr instanceof String_ && $subNode->expr->value === '') {
                $subNode->expr = new ConstFetch(new Name('null'));
                $changed = true;

                return null;
            }

            if (
                $subNode->expr instanceof Coalesce
                && $subNode->expr->right instanceof String_
                && $subNode->expr->right->value === ''
            ) {
                $subNode->expr->right = new ConstFetch(new Name('null'));
                $changed = true;
            }

            return null;
        });

        return $changed ? $node : null;
    }

    private function isStringType(?Node $type): bool
    {
        return ($type instanceof Identifier || $type instanceof Name) && $type->toString() === 'string';
    }
}
