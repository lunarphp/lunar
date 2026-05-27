<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Catalogue;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * Rewrites `$model->translateAttribute('name')` / `$model->attr('name')` to
 * `$model->translate('name')` for the catalogue fields promoted from
 * `attribute_data` to dedicated columns in spec 0018 (`name`, `description`).
 *
 * Only calls whose first argument is a string literal in the configured field
 * list are rewritten — `translateAttribute()` / `attr()` remain valid for every
 * other (still attribute-backed) handle. Any further arguments (e.g. an explicit
 * locale) are preserved.
 */
final class RewriteTranslatedFieldCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var list<string>
     */
    private array $fields = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites `$model->translateAttribute(\'name\')` / `$model->attr(\'name\')` to `$model->translate(\'name\')` for promoted catalogue fields.',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$name = $product->translateAttribute('name');
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$name = $product->translate('name');
CODE_SAMPLE,
                ['name', 'description'],
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

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if (! in_array($node->name->toString(), ['translateAttribute', 'attr'], true)) {
            return null;
        }

        $args = $node->getArgs();

        if (! isset($args[0]) || ! $args[0]->value instanceof String_) {
            return null;
        }

        if (! in_array($args[0]->value->value, $this->fields, true)) {
            return null;
        }

        return new MethodCall($node->var, new Identifier('translate'), $args);
    }

    /**
     * @param  list<string>  $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allStringNotEmpty($configuration);

        $this->fields = $configuration;
    }
}
