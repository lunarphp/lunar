# Securing Your Site

## Overview

When you move to production, you will want to make sure you have taken all the steps possible to secure your site and the data that powers it. Lunar takes this very seriously and always do our best to follow industry practices.

## Reporting Vulnerabilities

If you find a security issue with Lunar, please reach out to us privately on Discord or via email [security@lunarphp.io](mailto:security@lunarphp.io) so we can address it and patch with a new release as soon as possible.

## Securing Laravel

As a Laravel developer, we are sure you are well versed in their security practices and how to lock down your app. But, if you need a refresher here are some useful links:

- [Deployment](https://laravel.com/docs/deployment)
- [Encryption](https://laravel.com/docs/encryption)
- [Hashing](https://laravel.com/docs/hashing)

## Securing search

Depending on which search driver you are using will depend on how you lock down the data that Lunar indexes in a production environment. To provide a rich search experience in the hub. We currently index the following models, which may contain sensitive information.

### What is sensitive information?

We classify sensitive information as data that contains information about your customers or orders, whether identifiable or not. Information such as addresses, emails, names is likely to be indexed for use in the hub.

### Lunar's indexes

Index names are relative to the `SCOUT_PREFIX` env variable.

|Model|Index|Contains Sensitive Information|
|:-|:-|:-|
`\Lunar\Models\Product`|`products`|❌|
`\Lunar\Models\Collection`|`collections`|❌|
`\Lunar\Models\ProductOption`|`product_options`|❌|
`\Lunar\Models\Customer`|`customers`|✅|
`\Lunar\Models\Order`|`orders`|✅|


## Securing Meilisearch

In a production environment, you must set an API key to allow access to the search endpoints that Meilisearch provides. It is recommended to have two API keys, one to perform admin tasks such as indexing documents (read/write) and one solely for reading.

[Run Meilisearch in production](https://docs.meilisearch.com/learn/cookbooks/running_production.html)

### Using a service like Ploi

If you are using Ploi, which is a fantastic service for managing servers and deploying code, they offer the ability to spin up Meilisearch servers from their UI and they give you the ability to set different API keys with various restrictions. Perfect if you are not confident in doing the nitty-gritty server stuff yourself.

[Read more about it here](https://ploi.io/features/meilisearch-server)

## Securing Algolia

Algolia has a lot of security features out-of-the-box. They also have some extra steps you should take to lock things down even further. There is an article dedicated to the topic which you should read.

[Algolia Security Best Practices](https://www.algolia.com/doc/guides/security/security-best-practices/)