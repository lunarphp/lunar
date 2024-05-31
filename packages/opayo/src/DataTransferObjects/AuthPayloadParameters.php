<?php

namespace Lunar\Opayo\DataTransferObjects;

class AuthPayloadParameters
{
    public function __construct(
        public string $transactionType,
        public string $merchantSessionKey,
        public string $cardIdentifier,
        public string $vendorTxCode,
        public int $amount,
        public string $currency,
        public string $customerFirstName,
        public string $customerLastName,
        public string $billingAddressLineOne,
        public string $billingAddressLineTwo,
        public string $billingAddressLineThree,
        public string $billingAddressCity,
        public string $billingAddressPostcode,
        public string $billingAddressCountryIso,
        public ?string $customerMobilePhone,
        public ?string $recipientFirstName,
        public ?string $recipientLastName,
        public ?string $shippingAddressLineOne,
        public ?string $shippingAddressLineTwo,
        public ?string $shippingAddressLineThree,
        public ?string $shippingAddressCity,
        public ?string $shippingAddressPostcode,
        public ?string $shippingAddressCountryIso,
        public string $notificationURL,
        public ?string $browserLanguage,
        public ?string $challengeWindowSize,
        public ?string $browserIP,
        public ?string $browserAcceptHeader,
        public bool $browserJavascriptEnabled,
        public ?string $browserUserAgent,
        public bool $browserJavaEnabled,
        public ?string $browserColorDepth,
        public ?string $browserScreenHeight,
        public ?string $browserScreenWidth,
        public ?string $browserTZ,
        public bool $saveCard = false,
        public bool $reusable = false,
        public ?string $authCode = null
    ) {
    }
}
