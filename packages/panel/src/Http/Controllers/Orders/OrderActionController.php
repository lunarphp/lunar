<?php

namespace Lunar\Panel\Http\Controllers\Orders;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Lunar\Core\Actions\Orders\CancelOrder;
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Tag;
use Lunar\Panel\Http\Requests\Orders\CancelOrderRequest;
use Lunar\Panel\Http\Requests\Orders\CaptureOrderRequest;
use Lunar\Panel\Http\Requests\Orders\NotifyOrderRequest;
use Lunar\Panel\Http\Requests\Orders\OrderNoteRequest;
use Lunar\Panel\Http\Requests\Orders\OrderTagsRequest;
use Lunar\Panel\Http\Requests\Orders\RefundOrderRequest;

/**
 * Order mutations, each delegating to a core order verb behind its `canRun`
 * guard. A guard failure is a 403 — the UI only offers an action when core
 * would accept it, so reaching a blocked endpoint is an out-of-band request.
 */
class OrderActionController
{
    public function capture(CaptureOrderRequest $request, Order $order): RedirectResponse
    {
        abort_unless(CaptureOrder::canRun($order), 403);

        return $this->run($order, fn () => $order->capture(
            $request->integer('transaction_id'),
            $request->input('amount'),
        ), 'panel::orders.flash_captured');
    }

    public function refund(RefundOrderRequest $request, Order $order): RedirectResponse
    {
        abort_unless(RefundOrder::canRun($order), 403);

        // "Include shipping" is a full-or-none flag, not a client-submitted
        // amount — the shipping value comes from the order's own shipping
        // line so the refund total can't be spoofed from the request.
        $shippingLine = $order->lines()->whereType('shipping')->first();
        $shipping = $request->boolean('shipping') && $shippingLine
            ? $shippingLine->total / $order->currency->factor
            : 0;

        return $this->run($order, fn () => $order->refund(new RefundRequest(
            transactionId: $request->integer('transaction_id'),
            lines: $request->lines(),
            shipping: $shipping,
            adjustment: $request->input('adjustment') ?: 0,
            notes: $request->input('notes'),
            notify: $request->boolean('notify'),
        )), 'panel::orders.flash_refunded');
    }

    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        abort_unless(CancelOrder::canRun($order), 403);

        return $this->run($order, fn () => $order->cancel(
            $request->input('reason'),
            $request->input('note'),
            $request->boolean('notify'),
        ), 'panel::orders.flash_cancelled');
    }

    public function close(Order $order): RedirectResponse
    {
        abort_unless($order->isOpen(), 403);
        $order->close();

        return back()->with('success', __('panel::orders.flash_closed'));
    }

    public function reopen(Order $order): RedirectResponse
    {
        abort_unless($order->isClosed() && ! $order->isCancelled(), 403);
        $order->reopen();

        return back()->with('success', __('panel::orders.flash_reopened'));
    }

    public function notify(NotifyOrderRequest $request, Order $order): RedirectResponse
    {
        return $this->run($order, fn () => $order->notifyCustomer(
            $request->string('notification')->value(),
            $request->input('message'),
        ), 'panel::orders.flash_notified');
    }

    public function note(OrderNoteRequest $request, Order $order): RedirectResponse
    {
        $order->forceFill(['notes' => $request->input('notes')])->save();

        return back()->with('success', __('panel::orders.flash_note_saved'));
    }

    public function tags(OrderTagsRequest $request, Order $order): RedirectResponse
    {
        $ids = collect($request->input('tags', []))
            ->filter()
            ->map(fn (string $value) => Str::upper(trim($value)))
            ->unique()
            ->map(fn (string $value) => Tag::firstOrCreate(['value' => $value])->id);

        $order->tags()->sync($ids);

        return back()->with('success', __('panel::orders.flash_tags_saved'));
    }

    /**
     * Run a core order verb, translating its domain exception into a flash
     * error rather than a 500 — an amount that races another capture, a driver
     * decline, etc.
     */
    protected function run(Order $order, callable $callback, string $successKey): RedirectResponse
    {
        try {
            $callback();
        } catch (OrderActionException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __($successKey));
    }
}
