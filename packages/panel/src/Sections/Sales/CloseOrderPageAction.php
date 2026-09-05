<?php

namespace Lunar\Panel\Sections\Sales;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Core\Models\Order;
use Lunar\Panel\Actions\PageAction;
use Lunar\Panel\Support\Position;

/**
 * Archive (close) an open order from the order view's header ellipsis.
 */
class CloseOrderPageAction extends PageAction
{
    public function key(): string
    {
        return 'close';
    }

    public function label(): string
    {
        return __('panel::orders.action_close');
    }

    public function icon(): ?string
    {
        return 'check';
    }

    public function position(): Position
    {
        return Position::priority(30);
    }

    public function method(): string
    {
        return 'post';
    }

    public function confirmationMessage(): ?string
    {
        return __('panel::orders.confirm_close');
    }

    public function url(mixed $context = null): ?string
    {
        return $context instanceof Order ? route('panel.orders.close', $context) : null;
    }

    public function visible(mixed $context = null, ?Authenticatable $user = null): bool
    {
        return $context instanceof Order && $context->isOpen();
    }
}
