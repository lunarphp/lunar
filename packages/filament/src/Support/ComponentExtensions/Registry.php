<?php

namespace Lunar\Filament\Support\ComponentExtensions;

use Lunar\Core\Contracts\DiscountType;
use Lunar\Filament\Contracts\DiscountFormType;

class Registry
{
    /** @var array<class-string, array<int, object>> */
    protected array $extensions = [];

    /** @var array<class-string<DiscountType>, class-string<DiscountFormType>> */
    protected array $discountForms = [];

    /**
     * Register one or more extension instances against bridge target classes.
     *
     * Accepts the same shape as `LunarPanel::extensions([…])`:
     *   [TargetClass::class => [new ExtensionA, new ExtensionB]]
     * or
     *   [TargetClass::class => new ExtensionA]
     */
    public function register(array $extensions): static
    {
        foreach ($extensions as $target => $instances) {
            $instances = is_array($instances) ? $instances : [$instances];

            $this->extensions[$target] ??= [];

            foreach ($instances as $instance) {
                $this->extensions[$target][] = $instance;
            }
        }

        return $this;
    }

    /**
     * @return array<class-string, array<int, object>>
     */
    public function all(): array
    {
        return $this->extensions;
    }

    /**
     * @return array<int, object>
     */
    public function for(string $target): array
    {
        return $this->extensions[$target] ?? [];
    }

    /**
     * Invoke `$hookName(...$args)` on every extension registered against `$target`.
     *
     * Each extension receives the current `$args` and its return value replaces
     * `$args[0]` for the next extension in the stack. When no extension defines
     * the hook, the original `$args[0]` passes through unchanged.
     */
    public function callHook(string $target, ?object $caller, string $hookName, mixed ...$args): mixed
    {
        foreach ($this->for($target) as $extension) {
            if (! method_exists($extension, $hookName)) {
                continue;
            }

            if (method_exists($extension, 'setCaller')) {
                $extension->setCaller($caller);
            }

            $args[0] = $extension->{$hookName}(...$args);
        }

        return $args[0] ?? null;
    }

    /**
     * Map a discount type to a separate DiscountFormType class, for types that
     * cannot implement the contract themselves without taking on a Filament
     * dependency (a package whose admin surface is optional, for instance).
     *
     * @param  class-string<DiscountType>  $discountType
     * @param  class-string<DiscountFormType>  $formClass
     */
    public function discountForm(string $discountType, string $formClass): static
    {
        $this->discountForms[$discountType] = $formClass;

        return $this;
    }

    /**
     * @return array<class-string<DiscountType>, class-string<DiscountFormType>>
     */
    public function discountForms(): array
    {
        return $this->discountForms;
    }

    /**
     * The Filament form for a discount type: the type itself when it implements
     * the contract, otherwise the class mapped through discountForm(), or null
     * when the type contributes no form.
     */
    public function discountFormFor(DiscountType $discountType): ?DiscountFormType
    {
        if ($discountType instanceof DiscountFormType) {
            return $discountType;
        }

        $formClass = $this->discountForms[$discountType::class] ?? null;

        return $formClass ? app($formClass) : null;
    }
}
