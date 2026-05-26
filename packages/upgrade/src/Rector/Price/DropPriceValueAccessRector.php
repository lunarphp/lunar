<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Price;

use Lunar\Core\Models\Order;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * Rewrites `$model->col->value` to `$model->col` for migrated money
 * attributes (spec 0012). The v1 `PriceDataType::$value` accessor
 * disappears; the cast now returns a raw integer.
 */
final class DropPriceValueAccessRector extends AbstractRector implements ConfigurableRectorInterface
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
            'Drops `->value` on Lunar money attributes now that the cast returns a raw int.',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$total = $order->total->value;
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$total = $order->total;
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
        return [PropertyFetch::class];
    }

    /**
     * @param  PropertyFetch  $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->name instanceof Identifier || $node->name->toString() !== 'value') {
            return null;
        }

        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        if ($this->resolver->resolve($node->var, $this->moneyAttributes) === null) {
            return null;
        }

        return $node->var;
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
