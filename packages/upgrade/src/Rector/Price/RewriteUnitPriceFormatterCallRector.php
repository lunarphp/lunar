<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Price;

use Lunar\Core\Models\OrderLine;
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
 * Rewrites `$model->col->unitDecimal(...)` / `unitFormatted(...)` on
 * migrated money attributes (spec 0012) to the new `unitDecimal('col', ...)`
 * / `unitFormat('col', ...)` helpers contributed by the `FormatsPrices`
 * trait. The trait reads `unit_quantity` from the model (or its loaded
 * `priceable` relation) and forwards it to the formatter, restoring the
 * v1 ergonomics without per-attribute value objects.
 *
 * Dynamic property accesses (`$model->{$col}->unitDecimal(...)`) and
 * non-Lunar receivers are left untouched; downstream owners need to swap
 * them by hand. The upgrade docs flag this as a manual step.
 */
final class RewriteUnitPriceFormatterCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    private const METHOD_MAP = [
        'unitDecimal' => 'unitDecimal',
        'unitFormatted' => 'unitFormat',
    ];

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
            'Rewrites `$model->col->unitDecimal(...)` / `unitFormatted(...)` to the `FormatsPrices` `unitDecimal(\'col\', ...)` / `unitFormat(\'col\', ...)` helpers.',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$unit = $line->unit_price->unitDecimal();
$display = $line->unit_price->unitFormatted();
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$unit = $line->unitDecimal('unit_price');
$display = $line->unitFormat('unit_price');
CODE_SAMPLE,
                [OrderLine::class => ['unit_price']],
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

        $sourceMethod = $node->name->toString();

        if (! isset(self::METHOD_MAP[$sourceMethod])) {
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
            new Identifier(self::METHOD_MAP[$sourceMethod]),
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
