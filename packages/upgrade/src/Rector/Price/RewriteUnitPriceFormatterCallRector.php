<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Price;

use Lunar\Core\Models\Price;
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
 * Rewrites `$price->col->unitDecimal(...)` / `unitFormatted(...)` on the
 * catalogue `Lunar\Core\Models\Price` model (spec 0012) to
 * `$price->unitDecimal('col', ...)` / `$price->unitFormat('col', ...)`.
 * The new helpers divide by `priceable->unit_quantity`, matching v1
 * `Casts\Price::resolveUnitQuantity()` for catalogue rows.
 *
 * Calls on other models (`Order`, `OrderLine`, `Transaction`) are
 * intentionally **not** rewritten by this rule: their persisted money
 * columns are already per-single-unit (`CalculateLineSubtotal` does the
 * division eagerly), so `unitDecimal` / `unitFormatted` there was a
 * latent v1 bug. The upgrade docs flag those callsites as a manual
 * review point.
 *
 * Dynamic property accesses (`$price->{$col}->unitDecimal(...)`) and
 * non-Lunar receivers are also left untouched.
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
            'Rewrites `$price->col->unitDecimal(...)` / `unitFormatted(...)` on the catalogue Price model to `$price->unitDecimal(\'col\', ...)` / `$price->unitFormat(\'col\', ...)`.',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$unit = $catalogPrice->price->unitDecimal();
$display = $catalogPrice->price->unitFormatted();
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$unit = $catalogPrice->unitDecimal('price');
$display = $catalogPrice->unitFormat('price');
CODE_SAMPLE,
                [Price::class => ['price']],
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
