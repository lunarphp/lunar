<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector\Actions;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * Rewrites `SomeAction::run(...$args)` to
 * `app(SomeContract::class)->execute(...$args)` (spec 0016).
 *
 * The `AbstractAction::run()` / `::make()` shortcuts were removed when the
 * action layer moved to constructor injection. Each action now implements a
 * contract bound in the container; callers resolve the contract and call
 * `execute()`. The configuration maps each concrete action class-string to
 * its contract class-string.
 */
final class RewriteActionRunCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<class-string, class-string>
     */
    private array $actionContracts = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rewrites `Action::run(...)` to `app(Contract::class)->execute(...)` for Lunar actions.',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$order = CreateOrder::run($cart);
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
$order = app(CreatesOrder::class)->execute($cart);
CODE_SAMPLE,
                [
                    'Lunar\Core\Actions\Carts\CreateOrder' => 'Lunar\Core\Contracts\Actions\Carts\CreatesOrder',
                ],
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

        if (! $node->name instanceof Identifier || $node->name->toString() !== 'run') {
            return null;
        }

        if (! $node->class instanceof Name) {
            return null;
        }

        $contract = $this->actionContracts[$this->getName($node->class)] ?? null;

        if ($contract === null) {
            return null;
        }

        $resolve = new FuncCall(
            new Name('app'),
            [new Arg(new ClassConstFetch(new FullyQualified($contract), 'class'))],
        );

        return new MethodCall($resolve, new Identifier('execute'), $node->getArgs());
    }

    /**
     * @param  array<class-string, class-string>  $configuration
     */
    public function configure(array $configuration): void
    {
        foreach ($configuration as $concrete => $contract) {
            Assert::stringNotEmpty($concrete);
            Assert::stringNotEmpty($contract);
        }

        $this->actionContracts = $configuration;
    }
}
