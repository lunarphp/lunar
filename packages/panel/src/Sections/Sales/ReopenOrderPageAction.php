<?php

namespace Lunar\Panel\Sections\Sales;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Core\Models\Order;
use Lunar\Panel\Actions\PageAction;
use Lunar\Panel\Support\Position;

/**
 * Reopen a closed order from the order view's header ellipsis. Hidden for
 * cancelled orders — a cancellation is not reversed by reopening.
 */
class ReopenOrderPageAction extends PageAction
{
    public function key(): string
    {
        return 'reopen';
    }

    public function label(): string
    {
        return __('panel::orders.action_reopen');
    }

    public function icon(): ?string
    {
        return 'refresh';
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
        return __('panel::orders.confirm_reopen');
    }

    public function url(mixed $context = null): ?string
    {
        return $context instanceof Order ? route('panel.orders.reopen', $context) : null;
    }

    public function visible(mixed $context = null, ?Authenticatable $user = null): bool
    {
        return $context instanceof Order && $context->isClosed() && ! $context->isCancelled();
    }
}
