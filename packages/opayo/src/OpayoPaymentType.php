<?php

namespace Lunar\Opayo;

use Illuminate\Support\Str;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Lunar\Opayo\DataTransferObjects\AuthPayloadParameters;
use Lunar\Opayo\Facades\Opayo;
use Lunar\Opayo\Models\OpayoToken;
use Lunar\Opayo\Responses\PaymentAuthorize;
use Lunar\Opayo\Responses\ThreeDSecureResponse;
use Lunar\PaymentTypes\AbstractPayment;

class OpayoPaymentType extends AbstractPayment
{
    /**
     * The policy when capturing payments.
     *
     * @var string
     */
    protected $policy;

    /**
     * Initialise the payment type.
     */
    public function __construct()
    {
        $this->policy = config('lunar.opayo.policy', 'automatic');
    }

    /**
     * Authorize the payment for processing.
     *
     * @return \Lunar\Base\DataTransferObjects\PaymentAuthorize
     */
    public function authorize(): PaymentAuthorize|ThreeDSecureResponse
    {
        if (! $this->order) {
            if (! $this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        $this->order->updateQuietly([
            'customer_reference' => $this->data['customer_reference'] ?? $this->order->customer_reference,
            'notes' => $this->data['notes'] ?? $this->order->notes,
        ]);

        if ($this->order->placed_at) {

            // Something's gone wrong!
            $failedResponse = new PaymentAuthorize(
                success: false,
                status: Opayo::ALREADY_PLACED,
                message: 'This order has already been placed',
                orderId: $this->order->id,
                paymentType: 'opayo',
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        $transactionType = 'Payment';

        if ($this->policy != 'automatic') {
            $transactionType = 'Deferred';
        }

        $payload = $this->getAuthPayload($transactionType);

        $response = Opayo::api()->post('transactions', $payload);

        if (! $response->successful()) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: 'An unknown error occured',
                orderId: $this->order->id,
                paymentType: 'opayo',
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        $response = $response->object();

        if ($response->status == '3DAuth') {
            return new ThreeDSecureResponse(
                success: true,
                status: Opayo::THREED_AUTH,
                acsUrl: $response->acsUrl,
                acsTransId: $response->acsTransId ?? null,
                dsTransId: $response->dsTransId ?? null,
                cReq: $response->cReq ?? null,
                paReq: $response->paReq ?? null,
                transactionId: $response->transactionId,
            );
        }

        $successful = $response->status == 'Ok';

        if ($successful && $response->paymentMethod?->card?->reusable) {
            $this->saveCard(
                $this->order,
                $response->paymentMethod?->card,
                $response->acsTransId ?? null,
            );
        }

        $this->storeTransaction(
            transaction: $response,
            success: $successful
        );

        $status = $this->data['status'] ?? null;

        if ($successful) {
            $this->order->update([
                'status' => $status ?? ($this->config['authorized'] ?? null),
                'placed_at' => now(),
            ]);
        }

        $response = new PaymentAuthorize(
            success: $successful,
            status: $successful ? Opayo::AUTH_SUCCESSFUL : Opayo::AUTH_FAILED,
            orderId: $this->order->id,
            paymentType: 'opayo',
        );

        PaymentAttemptEvent::dispatch($response);

        return $response;
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        $response = Opayo::api()->post("transactions/{$transaction->reference}/instructions", [
            'instructionType' => 'release',
            'amount' => $amount,
        ]);

        $data = $response->object();

        if (! $response->successful() || isset($data->code)) {
            return new PaymentCapture(
                success: false,
                message: $data->description ?? 'An unknown error occured'
            );
        }

        $transaction->order->transactions()->create([
            'parent_transaction_id' => $transaction->id,
            'success' => true,
            'type' => 'capture',
            'driver' => 'opayo',
            'amount' => $amount,
            'reference' => $transaction->reference,
            'status' => $transaction->status,
            'notes' => null,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
            'captured_at' => now()->parse($data->date),
        ]);

        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     *
     * @param  string|null  $notes
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        $response = Opayo::api()->post('transactions', [
            'transactionType' => 'Refund',
            'vendorTxCode' => Str::random(40),
            'referenceTransactionId' => $transaction->reference,
            'description' => $notes ?: 'Refund',
            'amount' => $amount,
        ]);

        $data = $response->object();

        if (! $response->successful() || isset($data->code)) {
            return new PaymentRefund(
                success: false,
                message: $data->description ?? 'An unknown error occured'
            );
        }

        $transaction->order->transactions()->create([
            'parent_transaction_id' => $transaction->id,
            'success' => true,
            'type' => 'refund',
            'driver' => 'opayo',
            'amount' => $amount,
            'reference' => $data->transactionId,
            'status' => $transaction->status,
            'notes' => $notes,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
            'captured_at' => now(),
        ]);

        return new PaymentRefund(
            success: true
        );
    }

    /**
     * Handle the Three D Secure response.
     */
    public function threedsecure()
    {
        if (! $this->order) {
            if (! $this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        $path = ($this->data['cres'] ?? false) ? '3d-secure-challenge' : '3d-secure';

        $payload = [];

        if ($paRes = $this->data['pares'] ?? null) {
            $payload['paRes'] = $paRes;
        }

        if ($cres = $this->data['cres'] ?? null) {
            $payload['cRes'] = $cres;
        }

        $response = Opayo::api()->post('transactions/'.$this->data['transaction_id'].'/'.$path, $payload);

        if (! $response->successful()) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                orderId: $this->order->id,
                paymentType: 'opayo',
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        $data = $response->object();

        if (($data->statusCode ?? null) == '4026') {
            $this->order->transactions()->create([
                'success' => false,
                'type' => 'capture',
                'driver' => 'opayo',
                'amount' => $data->amount?->totalAmount ?: 0,
                'reference' => $data->transactionId,
                'status' => $data->status,
                'notes' => $data->statusDetail,
                'card_type' => 'unknown',
            ]);

            $threedFailure = new PaymentAuthorize(
                success: false,
                status: Opayo::THREED_SECURE_FAILED,
                orderId: $this->order->id,
                paymentType: 'opayo',
            );

            PaymentAttemptEvent::dispatch($threedFailure);

            return $threedFailure;
        }

        if (! empty($data->status) && $data->status == 'NotAuthenticated') {
            $threedFailure = new PaymentAuthorize(
                success: false,
                status: Opayo::THREED_SECURE_FAILED,
                orderId: $this->order->id,
                paymentType: 'opayo',
            );

            PaymentAttemptEvent::dispatch($threedFailure);

            return $threedFailure;
        }

        $transaction = Opayo::getTransaction($this->data['transaction_id']);

        $successful = $transaction->status == 'Ok';

        if ($successful && $data->paymentMethod?->card?->reusable) {
            $this->saveCard(
                $this->order,
                $data->paymentMethod?->card,
                $data->acsTransId ?? null,
            );
        }

        $this->storeTransaction(
            transaction: $transaction,
            success: $successful
        );

        $status = $this->data['status'] ?? null;

        if ($successful) {
            $this->order->update([
                'status' => $status ?? ($this->config['authorized'] ?? null),
                'placed_at' => now(),
            ]);
        }

        $response = new PaymentAuthorize(
            success: $successful,
            status: $successful ? Opayo::AUTH_SUCCESSFUL : Opayo::AUTH_FAILED,
            orderId: $this->order->id,
        );

        PaymentAttemptEvent::dispatch($response);

        return $response;
    }

    /**
     * Stores a transaction against the order.
     *
     * @param  stdclass  $transaction
     * @param  bool  $success
     * @return void
     */
    protected function storeTransaction($transaction, $success = false)
    {
        $data = [
            'success' => $success,
            'type' => $transaction->transactionType == 'Payment' ? 'capture' : 'intent',
            'driver' => 'opayo',
            'amount' => $transaction->amount->totalAmount,
            'reference' => $transaction->transactionId,
            'status' => $transaction->status,
            'notes' => $transaction->statusDetail,
            'card_type' => $transaction->paymentMethod->card->cardType,
            'last_four' => $transaction->paymentMethod->card->lastFourDigits,
            'captured_at' => $success ? ($transaction->transactionType == 'Payment' ? now() : null) : null,
            'meta' => [
                'threedSecure' => [
                    'status' => $transaction->avsCvcCheck->status,
                    'address' => $transaction->avsCvcCheck->address,
                    'postalCode' => $transaction->avsCvcCheck->postalCode,
                    'securityCode' => $transaction->avsCvcCheck->securityCode,
                ],
            ],
        ];
        $this->order->transactions()->create($data);
    }

    /**
     * Get the payload for authorizing a payment
     *
     * @return array
     */
    protected function getAuthPayload(string $type = 'Payment')
    {
        $billingAddress = $this->order->billingAddress;

        $payload = new AuthPayloadParameters(
            transactionType: $type,
            merchantSessionKey: $this->data['merchant_key'],
            cardIdentifier: $this->data['card_identifier'],
            vendorTxCode: Str::random(40),
            amount: $this->order->total->value,
            currency: $this->order->currency_code,
            customerFirstName: $billingAddress->first_name,
            customerLastName: $billingAddress->last_name,
            billingAddressLineOne: $billingAddress->line_one,
            billingAddressCity: $billingAddress->city,
            billingAddressPostcode: $billingAddress->postcode,
            billingAddressCountryIso: $billingAddress->country->iso2,
            customerMobilePhone: $billingAddress->contact_phone,
            notificationURL: route('opayo.threed.response'),
            browserLanguage: $this->data['browserLanguage'] ?? null,
            challengeWindowSize: $this->data['challengeWindowSize'] ?? null,
            browserIP: $this->data['browserIP'] ?? null,
            browserAcceptHeader: $this->data['browserAcceptHeader'] ?? null,
            browserJavascriptEnabled: true,
            browserUserAgent: $this->data['browserUserAgent'] ?? null,
            browserJavaEnabled: (bool) ($this->data['browserJavaEnabled'] ?? null),
            browserColorDepth: $this->data['browserColorDepth'] ?? null,
            browserScreenHeight: $this->data['browserScreenHeight'] ?? null,
            browserScreenWidth: $this->data['browserScreenWidth'] ?? null,
            browserTZ: $this->data['browserTZ'] ?? null,
            saveCard: $this->data['saveCard'] ?? false,
            reusable: $this->data['reusable'] ?? false,
        );

        if ($payload->reusable) {
            $reusedCard = OpayoToken::whereToken($payload->cardIdentifier)->first();

            $payload->authCode = $reusedCard?->auth_code;
        }

        return Opayo::getAuthPayload($payload);
    }

    /**
     * @param  \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed|string  $policy
     */
    public function setPolicy(mixed $policy): void
    {
        $this->policy = $policy;
    }

    private function saveCard(Order $order, object $details, string $authCode = null)
    {
        if (! $order->user_id) {
            return;
        }
        OpayoToken::where('last_four', '=', $details->lastFourDigits)
            ->where('user_id', '=', $order->user_id)->delete();

        $payment = new OpayoToken();
        $payment->user_id = $this->order->user_id;
        $payment->card_type = strtolower($details->cardType);
        $payment->last_four = $details->lastFourDigits;
        $payment->expires_at = \Carbon\Carbon::createFromFormat('my', $details->expiryDate)->endOfMonth();
        $payment->token = $details->cardIdentifier;
        $payment->auth_code = $authCode;
        $payment->save();
    }
}
