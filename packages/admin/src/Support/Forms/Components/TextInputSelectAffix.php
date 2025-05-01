<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\HasComponents;

class TextInputSelectAffix extends TextInput
{
    use HasComponents;

    protected string $view = 'lunarpanel::forms.components.text-input-select-affix';

    protected ?\Closure $selectComponentClosure = null;

    protected ?Select $selectComponent = null;

    protected string $position = 'suffix';

    public function select(\Closure|Select $closure): TextInputSelectAffix
    {
        if ($closure instanceof Select) {
            $this->selectComponentClosure = fn () => $closure;

            return $this;
        }
        $this->selectComponentClosure = $closure;

        return $this;
    }

    public function position(string $position = 'suffix'): TextInputSelectAffix
    {
        $this->position = $position;

        return $this;
    }

    public function hasSelect(): bool
    {
        return $this->selectComponentClosure !== null;
    }

    public function dehydrateValidationAttributes(array &$attributes): void
    {
        $attributes[$this->getStatePath()] = $this->getValidationAttribute();
        $attributes[$this->getSelectComponent()->getComponents()[0]->getStatePath()] = $this->evaluate($this->selectComponent->validationAttribute) ?? $this->evaluate($this->selectComponent->getLabel());
    }

    public function dehydrateValidationRules(array &$rules): void
    {
        $statePath = $this->getStatePath();

        if (count($componentRules = $this->getValidationRules())) {
            $rules[$statePath] = $componentRules;
        }

        $statePathSelect = $this->getSelectComponent()->getComponents()[0]->getStatePath();

        if (count($selectComponentRules = $this->selectComponent->getValidationRules())) {
            $rules[$statePathSelect] = $selectComponentRules;
        }
    }

    public function hydrateState(?array &$hydratedDefaultState, bool $andCallHydrationHooks = true): void
    {
        parent::hydrateState($hydratedDefaultState, $andCallHydrationHooks);
        if ($this->hasSelect()) {
            $this->getSelectComponent()->hydrateState($hydratedDefaultState, $andCallHydrationHooks);
        }
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getSelectComponent(): ComponentContainer
    {
        $evaluated = $this->evaluate($this->selectComponentClosure);

        if (! $evaluated instanceof Select) {
            throw new \RuntimeException('Passed component must be of type Select');
        }

        $this->selectComponent = $evaluated->hiddenLabel()
            ->extraAttributes(array_merge($evaluated->getExtraAttributes(), [
                'style' => 'border: none !important; background: transparent;--tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color)--tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(0px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);',
            ]));
        $path = explode('.', $this->getStatePath());
        unset($path[count($path) - 1]);

        return ComponentContainer::make($this->getLivewire())
            ->statePath(implode('.', $path))
            ->components([$this->selectComponent]);
    }
}
