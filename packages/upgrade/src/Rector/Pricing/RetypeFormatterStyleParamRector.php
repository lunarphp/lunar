<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Pricing;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Retypes `string $formatterStyle` to `int $formatterStyle` on a consumer's
 * own price formatter.
 *
 * `NumberFormatter::CURRENCY` and its siblings are int constants, so the
 * `string` type was always wrong. v2 corrects it on
 * `PriceFormatterInterface`, which makes a consumer implementation still
 * declaring `string` a signature mismatch rather than a deprecation.
 */
final class RetypeFormatterStyleParamRector extends AbstractRector
{
    private const PARAM_NAME = 'formatterStyle';

    private const METHODS = ['formatted', 'unitFormatted', 'formatValue'];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Retypes the $formatterStyle parameter from string to int on price formatter methods.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
public function formatted(?string $locale = null, string $formatterStyle = NumberFormatter::CURRENCY): mixed
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
public function formatted(?string $locale = null, int $formatterStyle = NumberFormatter::CURRENCY): mixed
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
        if (! $node->name instanceof Identifier) {
            return null;
        }

        if (! in_array($node->name->toString(), self::METHODS, true)) {
            return null;
        }

        $changed = false;

        foreach ($node->params as $param) {
            if (! $param->var instanceof Node\Expr\Variable) {
                continue;
            }

            if ($param->var->name !== self::PARAM_NAME) {
                continue;
            }

            if (! $param->type instanceof Identifier || $param->type->toString() !== 'string') {
                continue;
            }

            $param->type = new Identifier('int');
            $changed = true;
        }

        return $changed ? $node : null;
    }
}
