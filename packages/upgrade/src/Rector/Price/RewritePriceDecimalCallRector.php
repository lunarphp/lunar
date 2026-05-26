<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Price;

use Lunar\Core\Models\Order;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * Rewrites `$model->col->decimal(...args)` to `$model->decimal('col', ...args)`
 * for migrated money attributes (spec 0012). The per-attribute value object
 * is gone; formatting now lives on the model via the FormatsPrices trait.
 */
final class RewritePriceDecimalCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<class-string, list<string>>
     */
    private array $moneyAttributes = [];

    public function __construct(
        private readonly MoneyAttributeResolver $resolver,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites `$model->col->decimal(...)` to `$model->decimal(\'col\', ...)` for Lunar money attributes.',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$total = $order->total->decimal();
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$total = $order->decimal('total');
CODE_SAMPLE,
                [Order::class => ['total']],
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

        if (! $node->name instanceof Identifier || $node->name->toString() !== 'decimal') {
            return null;
        }

        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        $column = $this->resolver->resolve($node->var, $this->moneyAttributes);

        if ($column === null) {
            return null;
        }

        return new MethodCall(
            $node->var->var,
            new Identifier('decimal'),
            [new Arg(new String_($column)), ...$node->getArgs()],
        );
    }

    /**
     * @param  array<class-string, list<string>>  $configuration
     */
    public function configure(array $configuration): void
    {
        foreach ($configuration as $class => $attributes) {
            Assert::stringNotEmpty($class);
            Assert::allStringNotEmpty($attributes);
        }

        $this->moneyAttributes = $configuration;
    }
}
