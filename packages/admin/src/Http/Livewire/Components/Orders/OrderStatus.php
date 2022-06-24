<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class OrderStatus extends Component
{
    use Notifies;

    /**
     * Whether to show the status select modal.
     *
     * @var bool
     */
    public $showStatusSelect = false;

    /**
     * The order to edit.
     *
     * @var Order
     */
    public Order $order;

    /**
     * The new status to apply.
     *
     * @var string
     */
    public $newStatus = null;

    /**
     * The selected mailers to send.
     *
     * @var array
     */
    public array $selectedMailers = [];

    /**
     * The template we want to preview.
     *
     * @var string|null
     */
    public ?string $previewTemplate = null;

    /**
     * Any additional content for the email template.
     *
     * @var string
     */
    public ?string $additionalContent = null;

    /**
     * The email addresses to use for the mailers.
     *
     * @var array
     */
    public array $emailAddresses = [];

    /**
     * An additional custom email to send the notification to.
     *
     * @var string
     */
    public ?string $additionalEmail = null;

    /**
     * The phone numbers to use for the notifications.
     *
     * @var array
     */
    public array $phoneNumbers = [];

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function rules()
    {
        return [
            'newStatus' => 'nullable|string',
            'shouldSendNotification' => 'nullable|boolean',
            'emailAddresses' => 'array|min:1',
            'emailAddresses.*' => 'email|required',
            'additionalEmail' => 'nullable',
            'phoneNumbers' => 'array|nullable',
        ];
    }

    public function updatedNewStatus()
    {
        $this->selectedMailers = [];
    }

    /**
     * Return the configured statuses.
     *
     * @return array
     */
    public function getStatusesProperty()
    {
        return config('getcandy.orders.statuses', []);
    }

    /**
     * Return the available mailers for this order status.
     *
     * @return array
     */
    public function getAvailableMailersProperty()
    {
        return collect(
            $this->statuses[$this->newStatus]['mailers'] ?? []
        )->mapWithKeys(function ($mailer) {
            return [
                Str::snake(class_basename($mailer)) => [
                    'name' => Str::title(
                        Str::snake(class_basename($mailer), ' ')
                    ),
                    'class' => $mailer,
                ],
            ];
        });
    }

    /**
     * Return the available mailers for this order status.
     *
     * @return array
     */
    public function getAvailableNotificationsProperty()
    {
        return $this->statuses[$this->newStatus]['notifications'] ?? [];
    }

    public function getPreviewHtmlProperty()
    {
        $mailer = $this->availableMailers[$this->previewTemplate] ?? null;

        if (! $mailer) {
            return 'Unable to load preview';
        }

        return $this->buildMailer($mailer['class'])->render();
    }

    public function buildMailer($class)
    {
        $mailer = new $class;

        return $mailer
            ->with('order', $this->order)
            ->with('content', $this->additionalContent);
    }

    public function updateStatus()
    {
        foreach ($this->selectedMailers as $mailer) {
            $mailer = $this->availableMailers[$mailer] ?? null;

            if (empty($mailer['class'])) {
                continue;
            }

            $mailable = $this->buildMailer($mailer['class']);

            if ($this->additionalEmail) {
                $this->emailAddresses[] = $this->additionalEmail;
            }

            foreach ($this->emailAddresses as $email) {
                Mail::to($email)->queue($mailable);

                $storedPath = 'orders/activity/'.Str::random().'.html';

                $storedMailer = Storage::put(
                    $storedPath,
                    $mailable->render()
                );

                activity()
                ->causedBy(auth()->user())
                ->performedOn($this->order)
                ->event('email-notification')
                ->withProperties([
                    'template' => $storedPath,
                    'email' => $email,
                    'mailer' => $mailer['name'],
                ])->log('email-notification');
            }
        }

        $this->order->update([
            'status' => $this->newStatus,
        ]);

        $this->notify('Order status updated');
        $this->showStatusSelect = false;

        $this->emit('refreshOrder');
        $this->emit('activityUpdated');
    }

    public function getAvailableEmailAddressesProperty()
    {
        $billing = $this->order->billingAddress;
        $shipping = $this->order->shippingAddress;

        $emails = [];

        if ($billing?->contact_email == $shipping?->contact_email) {
            return [
                [
                    'type' => 'Billing & Shipping',
                    'address' => $billing->contact_email,
                ],
            ];
        }

        if ($billing?->contact_email) {
            $emails[] = [
                'type' => 'Billing',
                'address' => $billing->contact_email,
            ];
        }

        if ($shipping?->contact_email) {
            $emails[] = [
                'type' => 'Shipping',
                'address' => $shipping->contact_email,
            ];
        }

        return collect($emails)->unique('address');
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.status');
    }
}
