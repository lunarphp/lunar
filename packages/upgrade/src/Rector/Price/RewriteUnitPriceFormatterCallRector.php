<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Price;

use Lunar\Core\Models\OrderLine;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * Rewrites `$model->col->unitDecimal(...args)` / `unitFormatted(...args)`
 * on migrated money attributes (spec 0012) into an explicit container
 * resolution of `PriceFormatterInterface`. The per-attribute value object
 * is gone, and the new `FormatsPrices` trait deliberately does not expose
 * unit helpers — unit pricing is a formatter concern, not a model concern.
 *
 * Dynamic property accesses (`$model->{$column}->unitDecimal(...)`) and
 * non-Lunar receivers are left untouched; downstream owners need to swap
 * them by hand. The upgrade docs flag this as a manual step.
 */
final class RewriteUnitPriceFormatterCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    private const FORMATTER_INTERFACE = 'Lunar\\Core\\Pricing\\PriceFormatterInterface';

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
            'Rewrites `$model->col->unitDecimal(...)` / `unitFormatted(...)` to an explicit `PriceFormatterInterface` resolution.',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$unit = $line->unit_price->unitDecimal($line->unit_quantity);
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$unit = app(\Lunar\Core\Pricing\PriceFormatterInterface::class, [
    'value' => (int) $line->unit_price,
    'currency' => $line->resolveCurrency(),
])->unitDecimal($line->unit_quantity);
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

        $method = $node->name->toString();

        if ($method !== 'unitDecimal' && $method !== 'unitFormatted') {
            return null;
        }

        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        $column = $this->resolver->resolve($node->var, $this->moneyAttributes);

        if ($column === null) {
            return null;
        }

        $receiver = $node->var->var;

        $factory = new FuncCall(
            new FullyQualified('app'),
            [
                new Arg(new ClassConstFetch(new FullyQualified(self::FORMATTER_INTERFACE), 'class')),
                new Arg(new Array_([
                    new ArrayItem(new Int_($node->var), new String_('value')),
                    new ArrayItem(
                        new MethodCall($receiver, new Identifier('resolveCurrency')),
                        new String_('currency'),
                    ),
                ])),
            ],
        );

        return new MethodCall($factory, new Identifier($method), $node->getArgs());
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
