<?php

use Illuminate\Support\Str;
use Lunar\Opayo\DataTransferObjects\AuthPayloadParameters;

uses(\Lunar\Tests\Opayo\TestCase::class);

it('can return valid auth payload', function () {
    $params = new AuthPayloadParameters(
        transactionType: 'Payment',
        merchantSessionKey: '123456',
        cardIdentifier: 'TEST-CARD',
        vendorTxCode: Str::random(40),
        amount: 1234,
        currency: 'GBR',
        customerFirstName: 'Joe',
        customerLastName: 'Bloggs',
        billingAddressLineOne: '123 Billing Lane',
        billingAddressCity: 'Billing City',
        billingAddressPostcode: 'BL12 45T',
        billingAddressCountryIso: 'GBR',
        customerMobilePhone: '123456',
        notificationURL: '/',
        browserLanguage: 'SK',
        challengeWindowSize: '1024',
        browserIP: '127.0.0.1',
        browserAcceptHeader: 'ACCEPT',
        browserJavascriptEnabled: true,
        browserUserAgent: 'BROWSER-USER-AGENT',
        browserJavaEnabled: true,
        browserColorDepth: '123',
        browserScreenHeight: '667',
        browserScreenWidth: '1024',
        browserTZ: 'GMT',
        saveCard: false,
        reusable: false,
    );

    $payload = \Lunar\Opayo\Facades\Opayo::getAuthPayload($params);

    expect($payload['transactionType'])
        ->toBe($params->transactionType)
        ->and($payload['paymentMethod']['card']['merchantSessionKey'])
        ->toBe($params->merchantSessionKey)
        ->and($payload['paymentMethod']['card']['cardIdentifier'])
        ->toBe($params->cardIdentifier)
        ->and($payload['vendorTxCode'])
        ->toBe($params->vendorTxCode)
        ->and($payload['amount'])
        ->toBe($params->amount)
        ->and($payload['currency'])
        ->toBe($params->currency)
        ->and($payload['customerFirstName'])
        ->toBe($params->customerFirstName)
        ->and($payload['customerLastName'])
        ->toBe($params->customerLastName)
        // Billing Details
        ->and($payload['billingAddress']['address1'])
        ->toBe($params->billingAddressLineOne)
        ->and($payload['billingAddress']['city'])
        ->toBe($params->billingAddressCity)
        ->and($payload['billingAddress']['postalCode'])
        ->toBe($params->billingAddressPostcode)
        ->and($payload['billingAddress']['country'])
        ->toBe($params->billingAddressCountryIso)
        // Strong Customer Auth
        ->and($payload['strongCustomerAuthentication']['customerMobilePhone'])
        ->toBe($params->customerMobilePhone)
        ->and($payload['strongCustomerAuthentication']['browserLanguage'])
        ->toBe($params->browserLanguage)
        ->and($payload['strongCustomerAuthentication']['challengeWindowSize'])
        ->toBe($params->challengeWindowSize)
        ->and($payload['strongCustomerAuthentication']['browserIP'])
        ->toBe($params->browserIP)
        ->and($payload['strongCustomerAuthentication']['notificationURL'])
        ->toBe($params->notificationURL)
        ->and($payload['strongCustomerAuthentication']['browserAcceptHeader'])
        ->toBe($params->browserAcceptHeader)
        ->and($payload['strongCustomerAuthentication']['browserJavascriptEnabled'])
        ->toBe($params->browserJavascriptEnabled)
        ->and($payload['strongCustomerAuthentication']['browserUserAgent'])
        ->toBe($params->browserUserAgent)
        ->and($payload['strongCustomerAuthentication']['browserJavaEnabled'])
        ->toBe($params->browserJavaEnabled)
        ->and($payload['strongCustomerAuthentication']['browserColorDepth'])
        ->toBe($params->browserColorDepth)
        ->and($payload['strongCustomerAuthentication']['browserScreenHeight'])
        ->toBe($params->browserScreenHeight)
        ->and($payload['strongCustomerAuthentication']['browserScreenWidth'])
        ->toBe($params->browserScreenWidth)
        ->and($payload['strongCustomerAuthentication']['browserTZ'])
        ->toBe($params->browserTZ);
});

it('does not try and save or reuse payments by default', function () {
    $params = new AuthPayloadParameters(
        transactionType: 'Payment',
        merchantSessionKey: '123456',
        cardIdentifier: 'TEST-CARD',
        vendorTxCode: Str::random(40),
        amount: 1234,
        currency: 'GBR',
        customerFirstName: 'Joe',
        customerLastName: 'Bloggs',
        billingAddressLineOne: '123 Billing Lane',
        billingAddressCity: 'Billing City',
        billingAddressPostcode: 'BL12 45T',
        billingAddressCountryIso: 'GBR',
        customerMobilePhone: '123456',
        notificationURL: '/',
        browserLanguage: 'SK',
        challengeWindowSize: '1024',
        browserIP: '127.0.0.1',
        browserAcceptHeader: 'ACCEPT',
        browserJavascriptEnabled: true,
        browserUserAgent: 'BROWSER-USER-AGENT',
        browserJavaEnabled: true,
        browserColorDepth: '123',
        browserScreenHeight: '667',
        browserScreenWidth: '1024',
        browserTZ: 'GMT',
        saveCard: false,
        reusable: false,
        authCode: null,
    );

    $payload = \Lunar\Opayo\Facades\Opayo::getAuthPayload($params);

    expect($payload['paymentMethod']['card'])->not->toHaveKey('save')
        ->and($payload['strongCustomerAuthentication'])
        ->not
        ->toHaveKey('threeDSRequestorPriorAuthenticationInfo');
});

it('can allow saved cards', function () {
    $params = new AuthPayloadParameters(
        transactionType: 'Payment',
        merchantSessionKey: '123456',
        cardIdentifier: 'TEST-CARD',
        vendorTxCode: Str::random(40),
        amount: 1234,
        currency: 'GBR',
        customerFirstName: 'Joe',
        customerLastName: 'Bloggs',
        billingAddressLineOne: '123 Billing Lane',
        billingAddressCity: 'Billing City',
        billingAddressPostcode: 'BL12 45T',
        billingAddressCountryIso: 'GBR',
        customerMobilePhone: '123456',
        notificationURL: '/',
        browserLanguage: 'SK',
        challengeWindowSize: '1024',
        browserIP: '127.0.0.1',
        browserAcceptHeader: 'ACCEPT',
        browserJavascriptEnabled: true,
        browserUserAgent: 'BROWSER-USER-AGENT',
        browserJavaEnabled: true,
        browserColorDepth: '123',
        browserScreenHeight: '667',
        browserScreenWidth: '1024',
        browserTZ: 'GMT',
        saveCard: true,
        reusable: false,
    );

    $payload = \Lunar\Opayo\Facades\Opayo::getAuthPayload($params);

    expect($payload['paymentMethod']['card']['save'])->toBeTrue()
        ->and($payload['credentialType'])->toEqual([
            'cofUsage' => 'First',
            'initiatedType' => 'CIT',
            'mitType' => 'Unscheduled',
        ]);
});

it('can allow card reuse in the payload', function () {
    $params = new AuthPayloadParameters(
        transactionType: 'Payment',
        merchantSessionKey: '123456',
        cardIdentifier: 'TEST-CARD',
        vendorTxCode: Str::random(40),
        amount: 1234,
        currency: 'GBR',
        customerFirstName: 'Joe',
        customerLastName: 'Bloggs',
        billingAddressLineOne: '123 Billing Lane',
        billingAddressCity: 'Billing City',
        billingAddressPostcode: 'BL12 45T',
        billingAddressCountryIso: 'GBR',
        customerMobilePhone: '123456',
        notificationURL: '/',
        browserLanguage: 'SK',
        challengeWindowSize: '1024',
        browserIP: '127.0.0.1',
        browserAcceptHeader: 'ACCEPT',
        browserJavascriptEnabled: true,
        browserUserAgent: 'BROWSER-USER-AGENT',
        browserJavaEnabled: true,
        browserColorDepth: '123',
        browserScreenHeight: '667',
        browserScreenWidth: '1024',
        browserTZ: 'GMT',
        saveCard: false,
        reusable: true,
        authCode: 'AUTH-CODE',
    );

    $payload = \Lunar\Opayo\Facades\Opayo::getAuthPayload($params);

    expect($payload['paymentMethod']['card']['reusable'])->toBeTrue()
        ->and($payload['credentialType'])->toEqual([
            'cofUsage' => 'Subsequent',
            'initiatedType' => 'CIT',
            'mitType' => 'Unscheduled',
        ])->and($payload['strongCustomerAuthentication']['threeDSRequestorPriorAuthenticationInfo']['threeDSReqPriorRef'])
        ->toBe($params->authCode);
});
