<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Rector;

use Lunar\Upgrade\Rector\Models\RewriteModelClassCallRector;
use Lunar\Upgrade\Rector\Orders\RewriteOrderRefundCallRector;
use Lunar\Upgrade\Rector\Pricing\RetypeFormatterStyleParamRector;

/**
 * Catalog of Rector renames contributed by v2 breaking specs.
 *
 * Specs append their entries here when landing. The package's
 * `config/rector.php` plugs these into RenameClassRector for users.
 */
final class LunarSetList
{
    /**
     * Class-string renames driving the v1.x → v2.x migration.
     *
     * Includes every class shipped by `lunarphp/core` (spec 0002).
     *
     * @var array<string, string>
     */
    public const V1_TO_V2_CLASS_RENAMES = [
        'Lunar\\Actions\\Carts\\AddAddress' => 'Lunar\\Core\\Actions\\Carts\\AddAddress',
        'Lunar\\Actions\\Carts\\AddOrUpdatePurchasable' => 'Lunar\\Core\\Actions\\Carts\\AddOrUpdatePurchasable',
        'Lunar\\Actions\\Carts\\AssociateUser' => 'Lunar\\Core\\Actions\\Carts\\AssociateUser',
        'Lunar\\Actions\\Carts\\CalculateLine' => 'Lunar\\Core\\Actions\\Carts\\CalculateLine',
        'Lunar\\Actions\\Carts\\CalculateLineSubtotal' => 'Lunar\\Core\\Actions\\Carts\\CalculateLineSubtotal',
        'Lunar\\Actions\\Carts\\CreateOrder' => 'Lunar\\Core\\Actions\\Carts\\CreateOrder',
        'Lunar\\Actions\\Carts\\GenerateFingerprint' => 'Lunar\\Core\\Actions\\Carts\\GenerateFingerprint',
        'Lunar\\Actions\\Carts\\GetExistingCartLine' => 'Lunar\\Core\\Actions\\Carts\\GetExistingCartLine',
        'Lunar\\Actions\\Carts\\MergeCart' => 'Lunar\\Core\\Actions\\Carts\\MergeCart',
        'Lunar\\Actions\\Carts\\RemovePurchasable' => 'Lunar\\Core\\Actions\\Carts\\RemovePurchasable',
        'Lunar\\Actions\\Carts\\SetShippingOption' => 'Lunar\\Core\\Actions\\Carts\\SetShippingOption',
        'Lunar\\Actions\\Carts\\UpdateCartLine' => 'Lunar\\Core\\Actions\\Carts\\UpdateCartLine',
        'Lunar\\Actions\\Collections\\SortProducts' => 'Lunar\\Core\\Actions\\Collections\\SortProducts',
        'Lunar\\Actions\\Collections\\SortProductsByPrice' => 'Lunar\\Core\\Actions\\Collections\\SortProductsByPrice',
        'Lunar\\Actions\\Collections\\SortProductsBySku' => 'Lunar\\Core\\Actions\\Collections\\SortProductsBySku',
        'Lunar\\Actions\\Currencies\\CreateCurrencyPrices' => 'Lunar\\Core\\Actions\\Currencies\\CreateCurrencyPrices',
        'Lunar\\Actions\\Orders\\GenerateOrderReference' => 'Lunar\\Core\\Actions\\Orders\\GenerateOrderReference',
        'Lunar\\Actions\\Taxes\\GetTaxZone' => 'Lunar\\Core\\Actions\\Taxes\\GetTaxZone',
        'Lunar\\Actions\\Taxes\\GetTaxZoneCountry' => 'Lunar\\Core\\Actions\\Taxes\\GetTaxZoneCountry',
        'Lunar\\Actions\\Taxes\\GetTaxZonePostcode' => 'Lunar\\Core\\Actions\\Taxes\\GetTaxZonePostcode',
        'Lunar\\Actions\\Taxes\\GetTaxZoneState' => 'Lunar\\Core\\Actions\\Taxes\\GetTaxZoneState',
        'Lunar\\Addons\\Manifest' => 'Lunar\\Core\\Addons\\Manifest',
        'Lunar\\Base\\Addressable' => 'Lunar\\Core\\Contracts\\Addressable',
        'Lunar\\Base\\AttributeManifest' => 'Lunar\\Core\\Manifests\\AttributeManifest',
        'Lunar\\Base\\AttributeManifestInterface' => 'Lunar\\Core\\Contracts\\AttributeManifest',
        'Lunar\\Base\\BaseModel' => 'Lunar\\Core\\Models\\Base',
        'Lunar\\Base\\CartLineModifier' => 'Lunar\\Core\\Modifiers\\CartLineModifier',
        'Lunar\\Base\\CartLineModifiers' => 'Lunar\\Core\\Modifiers\\CartLineModifiers',
        'Lunar\\Base\\CartModifier' => 'Lunar\\Core\\Modifiers\\CartModifier',
        'Lunar\\Base\\CartModifiers' => 'Lunar\\Core\\Modifiers\\CartModifiers',
        'Lunar\\Base\\CartSessionInterface' => 'Lunar\\Core\\Contracts\\CartSession',
        'Lunar\\Base\\Casts\\AsAttributeData' => 'Lunar\\Core\\Casts\\AsAttributeData',
        'Lunar\\Base\\Casts\\CouponString' => 'Lunar\\Core\\Casts\\CouponString',
        'Lunar\\Base\\Casts\\DiscountBreakdown' => 'Lunar\\Core\\Casts\\DiscountBreakdown',
        'Lunar\\Base\\Casts\\Price' => 'Lunar\\Core\\Base\\Casts\\Price',
        'Lunar\\Base\\Casts\\ShippingBreakdown' => 'Lunar\\Core\\Casts\\ShippingBreakdown',
        'Lunar\\Base\\Casts\\TaxBreakdown' => 'Lunar\\Core\\Casts\\TaxBreakdown',
        'Lunar\\Base\\DataTransferObjects\\CartDiscount' => 'Lunar\\Core\\DataObjects\\CartDiscount',
        'Lunar\\Base\\DataTransferObjects\\PaymentAuthorize' => 'Lunar\\Core\\DataObjects\\PaymentAuthorize',
        'Lunar\\Base\\DataTransferObjects\\PaymentCapture' => 'Lunar\\Core\\DataObjects\\PaymentCapture',
        'Lunar\\Base\\DataTransferObjects\\PaymentCheck' => 'Lunar\\Core\\DataObjects\\PaymentCheck',
        'Lunar\\Base\\DataTransferObjects\\PaymentChecks' => 'Lunar\\Core\\DataObjects\\PaymentChecks',
        'Lunar\\Base\\DataTransferObjects\\PaymentRefund' => 'Lunar\\Core\\DataObjects\\PaymentRefund',
        'Lunar\\Base\\DataTransferObjects\\PricingResponse' => 'Lunar\\Core\\DataObjects\\PricingResponse',
        'Lunar\\Base\\DiscountManagerInterface' => 'Lunar\\Core\\Contracts\\DiscountManager',
        'Lunar\\Base\\DiscountTypeInterface' => 'Lunar\\Core\\Contracts\\DiscountType',
        'Lunar\\Base\\Enums\\Concerns\\ProvidesProductAssociationType' => 'Lunar\\Core\\Enums\\Concerns\\ProvidesProductAssociationType',
        'Lunar\\Base\\Enums\\ProductAssociation' => 'Lunar\\Core\\Enums\\ProductAssociation',
        'Lunar\\Base\\FieldType' => 'Lunar\\Core\\Contracts\\FieldType',
        'Lunar\\Base\\FieldTypeManifest' => 'Lunar\\Core\\Manifests\\FieldTypeManifest',
        'Lunar\\Base\\FieldTypeManifestInterface' => 'Lunar\\Core\\Contracts\\FieldTypeManifest',
        'Lunar\\Base\\HasThumbnailImage' => 'Lunar\\Core\\Contracts\\HasThumbnailImage',
        'Lunar\\Base\\LunarUser' => 'Lunar\\Core\\Contracts\\LunarUser',
        'Lunar\\Base\\MediaDefinitionsInterface' => 'Lunar\\Core\\Contracts\\MediaDefinitions',
        'Lunar\\Base\\Migration' => 'Lunar\\Core\\Database\\Migration',
        'Lunar\\Base\\ModelManifest' => 'Lunar\\Core\\Manifests\\ModelManifest',
        'Lunar\\Base\\ModelManifestInterface' => 'Lunar\\Core\\Contracts\\ModelManifest',
        'Lunar\\Base\\OrderModifier' => 'Lunar\\Core\\Modifiers\\OrderModifier',
        'Lunar\\Base\\OrderModifiers' => 'Lunar\\Core\\Modifiers\\OrderModifiers',
        'Lunar\\Base\\OrderReferenceGenerator' => 'Lunar\\Core\\Orders\\ReferenceGenerator',
        'Lunar\\Base\\OrderReferenceGeneratorInterface' => 'Lunar\\Core\\Contracts\\OrderReferenceGenerator',
        'Lunar\\Base\\PaymentManagerInterface' => 'Lunar\\Core\\Contracts\\PaymentManager',
        'Lunar\\Base\\PaymentTypeInterface' => 'Lunar\\Core\\Contracts\\PaymentType',
        'Lunar\\Base\\PricingManagerInterface' => 'Lunar\\Core\\Contracts\\PricingManager',
        'Lunar\\Base\\ProvidesTelemetryInsights' => 'Lunar\\Core\\Contracts\\ProvidesTelemetryInsights',
        'Lunar\\Base\\Purchasable' => 'Lunar\\Core\\Contracts\\Purchasable',
        'Lunar\\Base\\ShippingManifest' => 'Lunar\\Core\\Manifests\\ShippingManifest',
        'Lunar\\Base\\ShippingManifestInterface' => 'Lunar\\Core\\Contracts\\ShippingManifest',
        'Lunar\\Base\\ShippingModifier' => 'Lunar\\Core\\Modifiers\\ShippingModifier',
        'Lunar\\Base\\ShippingModifiers' => 'Lunar\\Core\\Modifiers\\ShippingModifiers',
        'Lunar\\Base\\StandardMediaDefinitions' => 'Lunar\\Core\\Media\\StandardDefinitions',
        'Lunar\\Base\\StorefrontSessionInterface' => 'Lunar\\Core\\Contracts\\StorefrontSession',
        'Lunar\\Base\\TaxDriver' => 'Lunar\\Core\\Drivers\\TaxDriver',
        'Lunar\\Base\\TaxManagerInterface' => 'Lunar\\Core\\Contracts\\TaxManager',
        'Lunar\\Base\\TelemetryInsights' => 'Lunar\\Core\\Telemetry\\Insights',
        'Lunar\\Base\\TelemetryService' => 'Lunar\\Core\\Telemetry\\TelemetryService',
        'Lunar\\Base\\TelemetryServiceInterface' => 'Lunar\\Core\\Contracts\\TelemetryService',
        'Lunar\\Base\\Traits\\CachesProperties' => 'Lunar\\Core\\Models\\Concerns\\CachesProperties',
        'Lunar\\Base\\Traits\\CanScheduleAvailability' => 'Lunar\\Core\\Models\\Concerns\\CanScheduleAvailability',
        'Lunar\\Base\\Traits\\HasAttributes' => 'Lunar\\Core\\Models\\Concerns\\HasAttributeData',
        'Lunar\\Base\\Traits\\HasChannels' => 'Lunar\\Core\\Models\\Concerns\\HasChannels',
        'Lunar\\Base\\Traits\\HasCustomerGroups' => 'Lunar\\Core\\Models\\Concerns\\HasCustomerGroups',
        'Lunar\\Base\\Traits\\HasDefaultRecord' => 'Lunar\\Core\\Models\\Concerns\\HasDefaultRecord',
        'Lunar\\Base\\Traits\\HasDimensions' => 'Lunar\\Core\\Models\\Concerns\\HasDimensions',
        'Lunar\\Base\\Traits\\HasMacros' => 'Lunar\\Core\\Models\\Concerns\\HasMacros',
        'Lunar\\Base\\Traits\\HasMedia' => 'Lunar\\Core\\Models\\Concerns\\HasMedia',
        'Lunar\\Base\\Traits\\HasPersonalDetails' => 'Lunar\\Core\\Models\\Concerns\\HasPersonalDetails',
        'Lunar\\Base\\Traits\\HasPrices' => 'Lunar\\Core\\Models\\Concerns\\HasPrices',
        'Lunar\\Base\\Traits\\HasTags' => 'Lunar\\Core\\Models\\Concerns\\HasTags',
        'Lunar\\Base\\Traits\\HasTranslations' => 'Lunar\\Core\\Models\\Concerns\\HasTranslations',
        'Lunar\\Base\\Traits\\HasUrls' => 'Lunar\\Core\\Models\\Concerns\\HasUrls',
        'Lunar\\Base\\Traits\\LogsActivity' => 'Lunar\\Core\\Models\\Concerns\\LogsActivity',
        'Lunar\\Base\\Traits\\LunarUser' => 'Lunar\\Core\\Models\\Concerns\\IsLunarUser',
        'Lunar\\Base\\Traits\\Searchable' => 'Lunar\\Core\\Models\\Concerns\\Searchable',
        'Lunar\\Base\\Validation\\CouponValidator' => 'Lunar\\Core\\Validation\\CouponValidator',
        'Lunar\\Base\\Validation\\CouponValidatorInterface' => 'Lunar\\Core\\Contracts\\CouponValidator',
        'Lunar\\Base\\ValueObjects\\Cart\\DiscountBreakdown' => 'Lunar\\Core\\ValueObjects\\Cart\\DiscountBreakdown',
        'Lunar\\Base\\ValueObjects\\Cart\\DiscountBreakdownLine' => 'Lunar\\Core\\ValueObjects\\Cart\\DiscountBreakdownLine',
        'Lunar\\Base\\ValueObjects\\Cart\\FreeItem' => 'Lunar\\Core\\ValueObjects\\Cart\\FreeItem',
        'Lunar\\Base\\ValueObjects\\Cart\\Promotion' => 'Lunar\\Core\\ValueObjects\\Cart\\Promotion',
        'Lunar\\Base\\ValueObjects\\Cart\\ShippingBreakdown' => 'Lunar\\Core\\ValueObjects\\Cart\\ShippingBreakdown',
        'Lunar\\Base\\ValueObjects\\Cart\\ShippingBreakdownItem' => 'Lunar\\Core\\ValueObjects\\Cart\\ShippingBreakdownItem',
        'Lunar\\Base\\ValueObjects\\Cart\\TaxBreakdown' => 'Lunar\\Core\\ValueObjects\\Cart\\TaxBreakdown',
        'Lunar\\Base\\ValueObjects\\Cart\\TaxBreakdownAmount' => 'Lunar\\Core\\ValueObjects\\Cart\\TaxBreakdownAmount',
        'Lunar\\Console\\Commands\\AddonsDiscover' => 'Lunar\\Core\\Console\\Commands\\AddonsDiscover',
        'Lunar\\Console\\Commands\\Import\\AddressData' => 'Lunar\\Core\\Console\\Commands\\Import\\AddressData',
        'Lunar\\Console\\Commands\\Orders\\SyncNewCustomerOrders' => 'Lunar\\Core\\Console\\Commands\\Orders\\SyncNewCustomerOrders',
        'Lunar\\Console\\Commands\\PruneCarts' => 'Lunar\\Core\\Console\\Commands\\PruneCarts',
        'Lunar\\Console\\Commands\\ScoutIndexerCommand' => 'Lunar\\Core\\Console\\Commands\\ScoutIndexerCommand',
        'Lunar\\Console\\InstallLunar' => 'Lunar\\Core\\Console\\InstallLunar',
        'Lunar\\DataTypes\\Price' => 'Lunar\\Core\\DataTypes\\Price',
        'Lunar\\DataTypes\\ShippingOption' => 'Lunar\\Core\\DataTypes\\ShippingOption',
        'Lunar\\Database\\Factories\\AddressFactory' => 'Lunar\\Core\\Database\\Factories\\AddressFactory',
        'Lunar\\Database\\Factories\\AttributeFactory' => 'Lunar\\Core\\Database\\Factories\\AttributeFactory',
        'Lunar\\Database\\Factories\\AttributeGroupFactory' => 'Lunar\\Core\\Database\\Factories\\AttributeGroupFactory',
        'Lunar\\Database\\Factories\\BaseFactory' => 'Lunar\\Core\\Database\\Factories\\BaseFactory',
        'Lunar\\Database\\Factories\\BrandFactory' => 'Lunar\\Core\\Database\\Factories\\BrandFactory',
        'Lunar\\Database\\Factories\\CartAddressFactory' => 'Lunar\\Core\\Database\\Factories\\CartAddressFactory',
        'Lunar\\Database\\Factories\\CartFactory' => 'Lunar\\Core\\Database\\Factories\\CartFactory',
        'Lunar\\Database\\Factories\\CartLineFactory' => 'Lunar\\Core\\Database\\Factories\\CartLineFactory',
        'Lunar\\Database\\Factories\\ChannelFactory' => 'Lunar\\Core\\Database\\Factories\\ChannelFactory',
        'Lunar\\Database\\Factories\\CollectionFactory' => 'Lunar\\Core\\Database\\Factories\\CollectionFactory',
        'Lunar\\Database\\Factories\\CollectionGroupFactory' => 'Lunar\\Core\\Database\\Factories\\CollectionGroupFactory',
        'Lunar\\Database\\Factories\\CountryFactory' => 'Lunar\\Core\\Database\\Factories\\CountryFactory',
        'Lunar\\Database\\Factories\\CurrencyFactory' => 'Lunar\\Core\\Database\\Factories\\CurrencyFactory',
        'Lunar\\Database\\Factories\\CustomerFactory' => 'Lunar\\Core\\Database\\Factories\\CustomerFactory',
        'Lunar\\Database\\Factories\\CustomerGroupFactory' => 'Lunar\\Core\\Database\\Factories\\CustomerGroupFactory',
        'Lunar\\Database\\Factories\\DiscountFactory' => 'Lunar\\Core\\Database\\Factories\\DiscountFactory',
        'Lunar\\Database\\Factories\\DiscountableFactory' => 'Lunar\\Core\\Database\\Factories\\DiscountableFactory',
        'Lunar\\Database\\Factories\\LanguageFactory' => 'Lunar\\Core\\Database\\Factories\\LanguageFactory',
        'Lunar\\Database\\Factories\\OrderAddressFactory' => 'Lunar\\Core\\Database\\Factories\\OrderAddressFactory',
        'Lunar\\Database\\Factories\\OrderFactory' => 'Lunar\\Core\\Database\\Factories\\OrderFactory',
        'Lunar\\Database\\Factories\\OrderLineFactory' => 'Lunar\\Core\\Database\\Factories\\OrderLineFactory',
        'Lunar\\Database\\Factories\\PriceFactory' => 'Lunar\\Core\\Database\\Factories\\PriceFactory',
        'Lunar\\Database\\Factories\\ProductAssociationFactory' => 'Lunar\\Core\\Database\\Factories\\ProductAssociationFactory',
        'Lunar\\Database\\Factories\\ProductFactory' => 'Lunar\\Core\\Database\\Factories\\ProductFactory',
        'Lunar\\Database\\Factories\\ProductOptionFactory' => 'Lunar\\Core\\Database\\Factories\\ProductOptionFactory',
        'Lunar\\Database\\Factories\\ProductOptionValueFactory' => 'Lunar\\Core\\Database\\Factories\\ProductOptionValueFactory',
        'Lunar\\Database\\Factories\\ProductTypeFactory' => 'Lunar\\Core\\Database\\Factories\\ProductTypeFactory',
        'Lunar\\Database\\Factories\\ProductVariantFactory' => 'Lunar\\Core\\Database\\Factories\\ProductVariantFactory',
        'Lunar\\Database\\Factories\\StateFactory' => 'Lunar\\Core\\Database\\Factories\\StateFactory',
        'Lunar\\Database\\Factories\\TagFactory' => 'Lunar\\Core\\Database\\Factories\\TagFactory',
        'Lunar\\Database\\Factories\\TaxClassFactory' => 'Lunar\\Core\\Database\\Factories\\TaxClassFactory',
        'Lunar\\Database\\Factories\\TaxRateAmountFactory' => 'Lunar\\Core\\Database\\Factories\\TaxRateAmountFactory',
        'Lunar\\Database\\Factories\\TaxRateFactory' => 'Lunar\\Core\\Database\\Factories\\TaxRateFactory',
        'Lunar\\Database\\Factories\\TaxZoneCountryFactory' => 'Lunar\\Core\\Database\\Factories\\TaxZoneCountryFactory',
        'Lunar\\Database\\Factories\\TaxZoneCustomerGroupFactory' => 'Lunar\\Core\\Database\\Factories\\TaxZoneCustomerGroupFactory',
        'Lunar\\Database\\Factories\\TaxZoneFactory' => 'Lunar\\Core\\Database\\Factories\\TaxZoneFactory',
        'Lunar\\Database\\Factories\\TaxZonePostcodeFactory' => 'Lunar\\Core\\Database\\Factories\\TaxZonePostcodeFactory',
        'Lunar\\Database\\Factories\\TaxZoneStateFactory' => 'Lunar\\Core\\Database\\Factories\\TaxZoneStateFactory',
        'Lunar\\Database\\Factories\\TransactionFactory' => 'Lunar\\Core\\Database\\Factories\\TransactionFactory',
        'Lunar\\Database\\Factories\\UrlFactory' => 'Lunar\\Core\\Database\\Factories\\UrlFactory',
        'Lunar\\Database\\Seeders\\DemoSeeder' => 'Lunar\\Core\\Database\\Seeders\\DemoSeeder',
        'Lunar\\Database\\Seeders\\TestingSeeder' => 'Lunar\\Core\\Database\\Seeders\\TestingSeeder',
        'Lunar\\DiscountTypes\\AbstractDiscountType' => 'Lunar\\Core\\DiscountTypes\\AbstractDiscountType',
        'Lunar\\DiscountTypes\\BuyXGetY' => 'Lunar\\Core\\DiscountTypes\\BuyXGetY',
        'Lunar\\Drivers\\SystemTaxDriver' => 'Lunar\\Core\\Drivers\\SystemTaxDriver',
        'Lunar\\Events\\PaymentAttemptEvent' => 'Lunar\\Core\\Events\\PaymentAttemptEvent',
        'Lunar\\Exceptions\\CartLineIdMismatchException' => 'Lunar\\Core\\Exceptions\\CartLineIdMismatchException',
        'Lunar\\Exceptions\\Carts\\BillingAddressIncompleteException' => 'Lunar\\Core\\Exceptions\\Carts\\BillingAddressIncompleteException',
        'Lunar\\Exceptions\\Carts\\BillingAddressMissingException' => 'Lunar\\Core\\Exceptions\\Carts\\BillingAddressMissingException',
        'Lunar\\Exceptions\\Carts\\CartException' => 'Lunar\\Core\\Exceptions\\Carts\\CartException',
        'Lunar\\Exceptions\\Carts\\OrderExistsException' => 'Lunar\\Core\\Exceptions\\Carts\\OrderExistsException',
        'Lunar\\Exceptions\\Carts\\ShippingAddressIncompleteException' => 'Lunar\\Core\\Exceptions\\Carts\\ShippingAddressIncompleteException',
        'Lunar\\Exceptions\\Carts\\ShippingAddressMissingException' => 'Lunar\\Core\\Exceptions\\Carts\\ShippingAddressMissingException',
        'Lunar\\Exceptions\\Carts\\ShippingOptionMissingException' => 'Lunar\\Core\\Exceptions\\Carts\\ShippingOptionMissingException',
        'Lunar\\Exceptions\\CustomerNotBelongsToUserException' => 'Lunar\\Core\\Exceptions\\CustomerNotBelongsToUserException',
        'Lunar\\Exceptions\\DisallowMultipleCartOrdersException' => 'Lunar\\Core\\Exceptions\\DisallowMultipleCartOrdersException',
        'Lunar\\Exceptions\\FieldTypeException' => 'Lunar\\Core\\Exceptions\\FieldTypeException',
        'Lunar\\Exceptions\\FieldTypes\\FieldTypeMissingException' => 'Lunar\\Core\\Exceptions\\FieldTypes\\FieldTypeMissingException',
        'Lunar\\Exceptions\\FieldTypes\\InvalidFieldTypeException' => 'Lunar\\Core\\Exceptions\\FieldTypes\\InvalidFieldTypeException',
        'Lunar\\Exceptions\\FingerprintMismatchException' => 'Lunar\\Core\\Exceptions\\FingerprintMismatchException',
        'Lunar\\Exceptions\\InvalidCartLineQuantityException' => 'Lunar\\Core\\Exceptions\\InvalidCartLineQuantityException',
        'Lunar\\Exceptions\\InvalidPaymentTypeException' => 'Lunar\\Core\\Exceptions\\InvalidPaymentTypeException',
        'Lunar\\Exceptions\\LunarException' => 'Lunar\\Core\\Exceptions\\LunarException',
        'Lunar\\Exceptions\\MaximumCartLineQuantityException' => 'Lunar\\Core\\Exceptions\\MaximumCartLineQuantityException',
        'Lunar\\Exceptions\\MissingCurrencyPriceException' => 'Lunar\\Core\\Exceptions\\MissingCurrencyPriceException',
        'Lunar\\Exceptions\\NonPurchasableItemException' => 'Lunar\\Core\\Exceptions\\NonPurchasableItemException',
        'Lunar\\Exceptions\\SchedulingException' => 'Lunar\\Core\\Exceptions\\SchedulingException',
        'Lunar\\Facades\\AttributeManifest' => 'Lunar\\Core\\Facades\\AttributeManifest',
        'Lunar\\Facades\\CartSession' => 'Lunar\\Core\\Facades\\CartSession',
        'Lunar\\Facades\\Converter' => 'Lunar\\Core\\Facades\\Converter',
        'Lunar\\Facades\\DB' => 'Lunar\\Core\\Facades\\DB',
        'Lunar\\Facades\\Discounts' => 'Lunar\\Core\\Facades\\Discounts',
        'Lunar\\Facades\\FieldTypeManifest' => 'Lunar\\Core\\Facades\\FieldTypeManifest',
        'Lunar\\Facades\\ModelManifest' => 'Lunar\\Core\\Facades\\ModelManifest',
        'Lunar\\Facades\\Payments' => 'Lunar\\Core\\Facades\\Payments',
        'Lunar\\Facades\\Pricing' => 'Lunar\\Core\\Facades\\Pricing',
        'Lunar\\Facades\\ShippingManifest' => 'Lunar\\Core\\Facades\\ShippingManifest',
        'Lunar\\Facades\\StorefrontSession' => 'Lunar\\Core\\Facades\\StorefrontSession',
        'Lunar\\Facades\\Taxes' => 'Lunar\\Core\\Facades\\Taxes',
        'Lunar\\Facades\\Telemetry' => 'Lunar\\Core\\Facades\\Telemetry',
        'Lunar\\FieldTypes\\Dropdown' => 'Lunar\\Core\\FieldTypes\\Dropdown',
        'Lunar\\FieldTypes\\File' => 'Lunar\\Core\\FieldTypes\\File',
        'Lunar\\FieldTypes\\ListField' => 'Lunar\\Core\\FieldTypes\\ListField',
        'Lunar\\FieldTypes\\Number' => 'Lunar\\Core\\FieldTypes\\Number',
        'Lunar\\FieldTypes\\Text' => 'Lunar\\Core\\FieldTypes\\Text',
        'Lunar\\FieldTypes\\Toggle' => 'Lunar\\Core\\FieldTypes\\Toggle',
        'Lunar\\FieldTypes\\TranslatedText' => 'Lunar\\Core\\FieldTypes\\TranslatedText',
        'Lunar\\FieldTypes\\Vimeo' => 'Lunar\\Core\\FieldTypes\\Vimeo',
        'Lunar\\FieldTypes\\YouTube' => 'Lunar\\Core\\FieldTypes\\YouTube',
        'Lunar\\Generators\\UrlGenerator' => 'Lunar\\Core\\Generators\\UrlGenerator',
        'Lunar\\Jobs\\Collections\\RebuildCollectionTree' => 'Lunar\\Core\\Jobs\\Collections\\RebuildCollectionTree',
        'Lunar\\Jobs\\Collections\\UpdateProductPositions' => 'Lunar\\Core\\Jobs\\Collections\\UpdateProductPositions',
        'Lunar\\Jobs\\Currencies\\CreateCurrencyPrices' => 'Lunar\\Core\\Jobs\\Currencies\\CreateCurrencyPrices',
        'Lunar\\Jobs\\Currencies\\SyncPriceCurrencies' => 'Lunar\\Core\\Jobs\\Currencies\\SyncPriceCurrencies',
        'Lunar\\Jobs\\Orders\\MarkAsNewCustomer' => 'Lunar\\Core\\Jobs\\Orders\\MarkAsNewCustomer',
        'Lunar\\Jobs\\Products\\Associations\\Associate' => 'Lunar\\Core\\Jobs\\Products\\Associations\\Associate',
        'Lunar\\Jobs\\Products\\Associations\\Dissociate' => 'Lunar\\Core\\Jobs\\Products\\Associations\\Dissociate',
        'Lunar\\Jobs\\SyncTags' => 'Lunar\\Core\\Jobs\\SyncTags',
        'Lunar\\Listeners\\CartSessionAuthListener' => 'Lunar\\Core\\Listeners\\CartSessionAuthListener',
        'Lunar\\LunarServiceProvider' => 'Lunar\\Core\\LunarServiceProvider',
        'Lunar\\Managers\\CartSessionManager' => 'Lunar\\Core\\Managers\\CartSessionManager',
        'Lunar\\Managers\\DiscountManager' => 'Lunar\\Core\\Managers\\DiscountManager',
        'Lunar\\Managers\\PaymentManager' => 'Lunar\\Core\\Managers\\PaymentManager',
        'Lunar\\Managers\\PricingManager' => 'Lunar\\Core\\Managers\\PricingManager',
        'Lunar\\Managers\\StorefrontSessionManager' => 'Lunar\\Core\\Managers\\StorefrontSessionManager',
        'Lunar\\Managers\\TaxManager' => 'Lunar\\Core\\Managers\\TaxManager',
        'Lunar\\Models\\Address' => 'Lunar\\Core\\Models\\Address',
        'Lunar\\Models\\Asset' => 'Lunar\\Core\\Models\\Asset',
        'Lunar\\Models\\Attribute' => 'Lunar\\Core\\Models\\Attribute',
        'Lunar\\Models\\AttributeGroup' => 'Lunar\\Core\\Models\\AttributeGroup',
        'Lunar\\Models\\Brand' => 'Lunar\\Core\\Models\\Brand',
        'Lunar\\Models\\Cart' => 'Lunar\\Core\\Models\\Cart',
        'Lunar\\Models\\CartAddress' => 'Lunar\\Core\\Models\\CartAddress',
        'Lunar\\Models\\CartLine' => 'Lunar\\Core\\Models\\CartLine',
        'Lunar\\Models\\Channel' => 'Lunar\\Core\\Models\\Channel',
        'Lunar\\Models\\Collection' => 'Lunar\\Core\\Models\\Collection',
        'Lunar\\Models\\CollectionGroup' => 'Lunar\\Core\\Models\\CollectionGroup',
        'Lunar\\Models\\Contracts\\Address' => 'Lunar\\Core\\Models\\Contracts\\Address',
        'Lunar\\Models\\Contracts\\Asset' => 'Lunar\\Core\\Models\\Contracts\\Asset',
        'Lunar\\Models\\Contracts\\Attribute' => 'Lunar\\Core\\Models\\Contracts\\Attribute',
        'Lunar\\Models\\Contracts\\AttributeGroup' => 'Lunar\\Core\\Models\\Contracts\\AttributeGroup',
        'Lunar\\Models\\Contracts\\Brand' => 'Lunar\\Core\\Models\\Contracts\\Brand',
        'Lunar\\Models\\Contracts\\Cart' => 'Lunar\\Core\\Models\\Contracts\\Cart',
        'Lunar\\Models\\Contracts\\CartAddress' => 'Lunar\\Core\\Models\\Contracts\\CartAddress',
        'Lunar\\Models\\Contracts\\CartLine' => 'Lunar\\Core\\Models\\Contracts\\CartLine',
        'Lunar\\Models\\Contracts\\Channel' => 'Lunar\\Core\\Models\\Contracts\\Channel',
        'Lunar\\Models\\Contracts\\Collection' => 'Lunar\\Core\\Models\\Contracts\\Collection',
        'Lunar\\Models\\Contracts\\CollectionGroup' => 'Lunar\\Core\\Models\\Contracts\\CollectionGroup',
        'Lunar\\Models\\Contracts\\Country' => 'Lunar\\Core\\Models\\Contracts\\Country',
        'Lunar\\Models\\Contracts\\Currency' => 'Lunar\\Core\\Models\\Contracts\\Currency',
        'Lunar\\Models\\Contracts\\Customer' => 'Lunar\\Core\\Models\\Contracts\\Customer',
        'Lunar\\Models\\Contracts\\CustomerGroup' => 'Lunar\\Core\\Models\\Contracts\\CustomerGroup',
        'Lunar\\Models\\Contracts\\Discount' => 'Lunar\\Core\\Models\\Contracts\\Discount',
        'Lunar\\Models\\Contracts\\DiscountCollection' => 'Lunar\\Core\\Models\\Contracts\\DiscountCollection',
        'Lunar\\Models\\Contracts\\Discountable' => 'Lunar\\Core\\Models\\Contracts\\Discountable',
        'Lunar\\Models\\Contracts\\Language' => 'Lunar\\Core\\Models\\Contracts\\Language',
        'Lunar\\Models\\Contracts\\Order' => 'Lunar\\Core\\Models\\Contracts\\Order',
        'Lunar\\Models\\Contracts\\OrderAddress' => 'Lunar\\Core\\Models\\Contracts\\OrderAddress',
        'Lunar\\Models\\Contracts\\OrderLine' => 'Lunar\\Core\\Models\\Contracts\\OrderLine',
        'Lunar\\Models\\Contracts\\Price' => 'Lunar\\Core\\Models\\Contracts\\Price',
        'Lunar\\Models\\Contracts\\Product' => 'Lunar\\Core\\Models\\Contracts\\Product',
        'Lunar\\Models\\Contracts\\ProductAssociation' => 'Lunar\\Core\\Models\\Contracts\\ProductAssociation',
        'Lunar\\Models\\Contracts\\ProductOption' => 'Lunar\\Core\\Models\\Contracts\\ProductOption',
        'Lunar\\Models\\Contracts\\ProductOptionValue' => 'Lunar\\Core\\Models\\Contracts\\ProductOptionValue',
        'Lunar\\Models\\Contracts\\ProductType' => 'Lunar\\Core\\Models\\Contracts\\ProductType',
        'Lunar\\Models\\Contracts\\ProductVariant' => 'Lunar\\Core\\Models\\Contracts\\ProductVariant',
        'Lunar\\Models\\Contracts\\State' => 'Lunar\\Core\\Models\\Contracts\\State',
        'Lunar\\Models\\Contracts\\Tag' => 'Lunar\\Core\\Models\\Contracts\\Tag',
        'Lunar\\Models\\Contracts\\TaxClass' => 'Lunar\\Core\\Models\\Contracts\\TaxClass',
        'Lunar\\Models\\Contracts\\TaxRate' => 'Lunar\\Core\\Models\\Contracts\\TaxRate',
        'Lunar\\Models\\Contracts\\TaxRateAmount' => 'Lunar\\Core\\Models\\Contracts\\TaxRateAmount',
        'Lunar\\Models\\Contracts\\TaxZone' => 'Lunar\\Core\\Models\\Contracts\\TaxZone',
        'Lunar\\Models\\Contracts\\TaxZoneCountry' => 'Lunar\\Core\\Models\\Contracts\\TaxZoneCountry',
        'Lunar\\Models\\Contracts\\TaxZoneCustomerGroup' => 'Lunar\\Core\\Models\\Contracts\\TaxZoneCustomerGroup',
        'Lunar\\Models\\Contracts\\TaxZonePostcode' => 'Lunar\\Core\\Models\\Contracts\\TaxZonePostcode',
        'Lunar\\Models\\Contracts\\TaxZoneState' => 'Lunar\\Core\\Models\\Contracts\\TaxZoneState',
        'Lunar\\Models\\Contracts\\Transaction' => 'Lunar\\Core\\Models\\Contracts\\Transaction',
        'Lunar\\Models\\Contracts\\Url' => 'Lunar\\Core\\Models\\Contracts\\Url',
        'Lunar\\Models\\Contracts\\UserPermission' => 'Lunar\\Core\\Models\\Contracts\\UserPermission',
        'Lunar\\Models\\Country' => 'Lunar\\Core\\Models\\Country',
        'Lunar\\Models\\Currency' => 'Lunar\\Core\\Models\\Currency',
        'Lunar\\Models\\Customer' => 'Lunar\\Core\\Models\\Customer',
        'Lunar\\Models\\CustomerGroup' => 'Lunar\\Core\\Models\\CustomerGroup',
        'Lunar\\Models\\Discount' => 'Lunar\\Core\\Models\\Discount',
        'Lunar\\Models\\DiscountCollection' => 'Lunar\\Core\\Models\\DiscountCollection',
        'Lunar\\Models\\Discountable' => 'Lunar\\Core\\Models\\Discountable',
        'Lunar\\Models\\Language' => 'Lunar\\Core\\Models\\Language',
        'Lunar\\Models\\Order' => 'Lunar\\Core\\Models\\Order',
        'Lunar\\Models\\OrderAddress' => 'Lunar\\Core\\Models\\OrderAddress',
        'Lunar\\Models\\OrderLine' => 'Lunar\\Core\\Models\\OrderLine',
        'Lunar\\Models\\Price' => 'Lunar\\Core\\Models\\Price',
        'Lunar\\Models\\Product' => 'Lunar\\Core\\Models\\Product',
        'Lunar\\Models\\ProductAssociation' => 'Lunar\\Core\\Models\\ProductAssociation',
        'Lunar\\Models\\ProductOption' => 'Lunar\\Core\\Models\\ProductOption',
        'Lunar\\Models\\ProductOptionValue' => 'Lunar\\Core\\Models\\ProductOptionValue',
        'Lunar\\Models\\ProductType' => 'Lunar\\Core\\Models\\ProductType',
        'Lunar\\Models\\ProductVariant' => 'Lunar\\Core\\Models\\ProductVariant',
        'Lunar\\Models\\State' => 'Lunar\\Core\\Models\\State',
        'Lunar\\Models\\Tag' => 'Lunar\\Core\\Models\\Tag',
        'Lunar\\Models\\TaxClass' => 'Lunar\\Core\\Models\\TaxClass',
        'Lunar\\Models\\TaxRate' => 'Lunar\\Core\\Models\\TaxRate',
        'Lunar\\Models\\TaxRateAmount' => 'Lunar\\Core\\Models\\TaxRateAmount',
        'Lunar\\Models\\TaxZone' => 'Lunar\\Core\\Models\\TaxZone',
        'Lunar\\Models\\TaxZoneCountry' => 'Lunar\\Core\\Models\\TaxZoneCountry',
        'Lunar\\Models\\TaxZoneCustomerGroup' => 'Lunar\\Core\\Models\\TaxZoneCustomerGroup',
        'Lunar\\Models\\TaxZonePostcode' => 'Lunar\\Core\\Models\\TaxZonePostcode',
        'Lunar\\Models\\TaxZoneState' => 'Lunar\\Core\\Models\\TaxZoneState',
        'Lunar\\Models\\Transaction' => 'Lunar\\Core\\Models\\Transaction',
        'Lunar\\Models\\Url' => 'Lunar\\Core\\Models\\Url',
        'Lunar\\Models\\UserPermission' => 'Lunar\\Core\\Models\\UserPermission',
        'Lunar\\Observers\\AddressObserver' => 'Lunar\\Core\\Observers\\AddressObserver',
        'Lunar\\Observers\\CartLineObserver' => 'Lunar\\Core\\Observers\\CartLineObserver',
        'Lunar\\Observers\\ChannelObserver' => 'Lunar\\Core\\Observers\\ChannelObserver',
        'Lunar\\Observers\\CollectionObserver' => 'Lunar\\Core\\Observers\\CollectionObserver',
        'Lunar\\Observers\\CurrencyObserver' => 'Lunar\\Core\\Observers\\CurrencyObserver',
        'Lunar\\Observers\\CustomerGroupObserver' => 'Lunar\\Core\\Observers\\CustomerGroupObserver',
        'Lunar\\Observers\\CustomerObserver' => 'Lunar\\Core\\Observers\\CustomerObserver',
        'Lunar\\Observers\\DiscountObserver' => 'Lunar\\Core\\Observers\\DiscountObserver',
        'Lunar\\Observers\\LanguageObserver' => 'Lunar\\Core\\Observers\\LanguageObserver',
        'Lunar\\Observers\\MediaObserver' => 'Lunar\\Core\\Observers\\MediaObserver',
        'Lunar\\Observers\\OrderLineObserver' => 'Lunar\\Core\\Observers\\OrderLineObserver',
        'Lunar\\Observers\\OrderObserver' => 'Lunar\\Core\\Observers\\OrderObserver',
        'Lunar\\Observers\\PriceObserver' => 'Lunar\\Core\\Observers\\PriceObserver',
        'Lunar\\Observers\\ProductObserver' => 'Lunar\\Core\\Observers\\ProductObserver',
        'Lunar\\Observers\\ProductOptionObserver' => 'Lunar\\Core\\Observers\\ProductOptionObserver',
        'Lunar\\Observers\\ProductOptionValueObserver' => 'Lunar\\Core\\Observers\\ProductOptionValueObserver',
        'Lunar\\Observers\\ProductVariantObserver' => 'Lunar\\Core\\Observers\\ProductVariantObserver',
        'Lunar\\Observers\\TransactionObserver' => 'Lunar\\Core\\Observers\\TransactionObserver',
        'Lunar\\Observers\\UrlObserver' => 'Lunar\\Core\\Observers\\UrlObserver',
        'Lunar\\PaymentTypes\\AbstractPayment' => 'Lunar\\Core\\PaymentTypes\\AbstractPayment',
        'Lunar\\PaymentTypes\\OfflinePayment' => 'Lunar\\Core\\PaymentTypes\\OfflinePayment',
        'Lunar\\Paypal\\PaypalInterface' => 'Lunar\\Paypal\\Contracts\\PaypalInterface',
        'Lunar\\Pipelines\\CartLine\\GetUnitPrice' => 'Lunar\\Core\\Pipelines\\CartLine\\GetUnitPrice',
        'Lunar\\Pipelines\\CartPrune\\PruneAfter' => 'Lunar\\Core\\Pipelines\\CartPrune\\PruneAfter',
        'Lunar\\Pipelines\\CartPrune\\WhereNotMerged' => 'Lunar\\Core\\Pipelines\\CartPrune\\WhereNotMerged',
        'Lunar\\Pipelines\\CartPrune\\WithoutOrders' => 'Lunar\\Core\\Pipelines\\CartPrune\\WithoutOrders',
        'Lunar\\Pipelines\\Cart\\ApplyDiscounts' => 'Lunar\\Core\\Pipelines\\Cart\\ApplyDiscounts',
        'Lunar\\Pipelines\\Cart\\ApplyShipping' => 'Lunar\\Core\\Pipelines\\Cart\\ApplyShipping',
        'Lunar\\Pipelines\\Cart\\Calculate' => 'Lunar\\Core\\Pipelines\\Cart\\Calculate',
        'Lunar\\Pipelines\\Cart\\CalculateLines' => 'Lunar\\Core\\Pipelines\\Cart\\CalculateLines',
        'Lunar\\Pipelines\\Cart\\CalculateShippingSubTotal' => 'Lunar\\Core\\Pipelines\\Cart\\CalculateShippingSubTotal',
        'Lunar\\Pipelines\\Cart\\CalculateTax' => 'Lunar\\Core\\Pipelines\\Cart\\CalculateTax',
        'Lunar\\Pipelines\\Order\\Creation\\CleanUpOrderLines' => 'Lunar\\Core\\Pipelines\\Order\\Creation\\CleanUpOrderLines',
        'Lunar\\Pipelines\\Order\\Creation\\CreateOrderAddresses' => 'Lunar\\Core\\Pipelines\\Order\\Creation\\CreateOrderAddresses',
        'Lunar\\Pipelines\\Order\\Creation\\CreateOrderLines' => 'Lunar\\Core\\Pipelines\\Order\\Creation\\CreateOrderLines',
        'Lunar\\Pipelines\\Order\\Creation\\CreateShippingLine' => 'Lunar\\Core\\Pipelines\\Order\\Creation\\CreateShippingLine',
        'Lunar\\Pipelines\\Order\\Creation\\FillOrderFromCart' => 'Lunar\\Core\\Pipelines\\Order\\Creation\\FillOrderFromCart',
        'Lunar\\Pipelines\\Order\\Creation\\MapDiscountBreakdown' => 'Lunar\\Core\\Pipelines\\Order\\Creation\\MapDiscountBreakdown',
        'Lunar\\Pricing\\DefaultPriceFormatter' => 'Lunar\\Core\\Pricing\\DefaultPriceFormatter',
        'Lunar\\Pricing\\PriceFormatterInterface' => 'Lunar\\Core\\Pricing\\PriceFormatterInterface',
        'Lunar\\Rules\\MaxDecimalPlaces' => 'Lunar\\Core\\Rules\\MaxDecimalPlaces',
        'Lunar\\Rules\\ValidCoupon' => 'Lunar\\Core\\Rules\\ValidCoupon',
        'Lunar\\Search\\BrandIndexer' => 'Lunar\\Core\\Search\\BrandIndexer',
        'Lunar\\Search\\CollectionIndexer' => 'Lunar\\Core\\Search\\CollectionIndexer',
        'Lunar\\Search\\CustomerIndexer' => 'Lunar\\Core\\Search\\CustomerIndexer',
        'Lunar\\Search\\Interfaces\\ScoutIndexerInterface' => 'Lunar\\Core\\Search\\Interfaces\\ScoutIndexerInterface',
        'Lunar\\Search\\OrderIndexer' => 'Lunar\\Core\\Search\\OrderIndexer',
        'Lunar\\Search\\ProductIndexer' => 'Lunar\\Core\\Search\\ProductIndexer',
        'Lunar\\Search\\ProductOptionIndexer' => 'Lunar\\Core\\Search\\ProductOptionIndexer',
        'Lunar\\Search\\ScoutIndexer' => 'Lunar\\Core\\Search\\ScoutIndexer',
        'Lunar\\Utils\\Arr' => 'Lunar\\Core\\Utils\\Arr',
        'Lunar\\Utils\\MeasurementConverter' => 'Lunar\\Core\\Utils\\MeasurementConverter',
        'Lunar\\Validation\\BaseValidator' => 'Lunar\\Core\\Validation\\BaseValidator',
        'Lunar\\Validation\\CartLine\\CartLineAvailability' => 'Lunar\\Core\\Validation\\CartLine\\CartLineAvailability',
        'Lunar\\Validation\\CartLine\\CartLineQuantity' => 'Lunar\\Core\\Validation\\CartLine\\CartLineQuantity',
        'Lunar\\Validation\\CartLine\\CartLineStock' => 'Lunar\\Core\\Validation\\CartLine\\CartLineStock',
        'Lunar\\Validation\\Cart\\ShippingOptionValidator' => 'Lunar\\Core\\Validation\\Cart\\ShippingOptionValidator',
        'Lunar\\Validation\\Cart\\ValidateCartForOrderCreation' => 'Lunar\\Core\\Validation\\Cart\\ValidateCartForOrderCreation',
        // --- Spec 0006: lunarphp/filament bridge extraction ---
        'Lunar\\Admin\\Support\\Forms\\Components\\Attributes' => 'Lunar\\Filament\\Forms\\Components\\Attributes',
        'Lunar\\Admin\\Support\\Forms\\Components\\AttributeSelector' => 'Lunar\\Filament\\Forms\\Components\\AttributeSelector',
        'Lunar\\Admin\\Support\\Forms\\Components\\MediaSelect' => 'Lunar\\Filament\\Forms\\Components\\MediaSelect',
        'Lunar\\Admin\\Support\\Forms\\Components\\PermissionSelector' => 'Lunar\\Filament\\Forms\\Components\\PermissionSelector',
        'Lunar\\Admin\\Support\\Forms\\Components\\Tags' => 'Lunar\\Filament\\Forms\\Components\\Tags',
        'Lunar\\Admin\\Support\\Forms\\Components\\TextInputSelectAffix' => 'Lunar\\Filament\\Forms\\Components\\TextInputSelectAffix',
        'Lunar\\Admin\\Support\\Forms\\Components\\TranslatedRichEditor' => 'Lunar\\Filament\\Forms\\Components\\TranslatedRichEditor',
        'Lunar\\Admin\\Support\\Forms\\Components\\TranslatedText' => 'Lunar\\Filament\\Forms\\Components\\TranslatedText',
        'Lunar\\Admin\\Support\\Forms\\Components\\TranslatedTextInput' => 'Lunar\\Filament\\Forms\\Components\\TranslatedTextInput',
        'Lunar\\Admin\\Support\\Forms\\Components\\Vimeo' => 'Lunar\\Filament\\Forms\\Components\\Vimeo',
        'Lunar\\Admin\\Support\\Forms\\Components\\YouTube' => 'Lunar\\Filament\\Forms\\Components\\YouTube',
        'Lunar\\Admin\\Support\\Tables\\Columns\\ThumbnailImageColumn' => 'Lunar\\Filament\\Tables\\Columns\\ThumbnailImageColumn',
        'Lunar\\Admin\\Support\\Tables\\Columns\\TranslatedTextColumn' => 'Lunar\\Filament\\Tables\\Columns\\TranslatedTextColumn',
        'Lunar\\Admin\\Support\\Tables\\Components\\KeyValue' => 'Lunar\\Filament\\Tables\\Components\\KeyValue',
        'Lunar\\Admin\\Support\\Tables\\Actions\\Collections\\CreateChildCollection' => 'Lunar\\Filament\\Tables\\Actions\\Collections\\CreateChildCollection',
        'Lunar\\Admin\\Support\\Infolists\\Components\\Livewire' => 'Filament\\Schemas\\Components\\Livewire',
        'Lunar\\Admin\\Support\\Infolists\\Components\\Tags' => 'Lunar\\Filament\\Infolists\\Components\\Tags',
        'Lunar\\Admin\\Support\\Infolists\\Components\\Timeline' => 'Lunar\\Filament\\Infolists\\Components\\Timeline',
        'Lunar\\Admin\\Support\\Infolists\\Components\\Transaction' => 'Lunar\\Filament\\Infolists\\Components\\Transaction',
        'Lunar\\Admin\\Support\\FieldTypes\\BaseFieldType' => 'Lunar\\Filament\\FieldTypes\\BaseFieldType',
        'Lunar\\Admin\\Support\\FieldTypes\\Dropdown' => 'Lunar\\Filament\\FieldTypes\\Dropdown',
        'Lunar\\Admin\\Support\\FieldTypes\\File' => 'Lunar\\Filament\\FieldTypes\\File',
        'Lunar\\Admin\\Support\\FieldTypes\\ListField' => 'Lunar\\Filament\\FieldTypes\\ListField',
        'Lunar\\Admin\\Support\\FieldTypes\\Number' => 'Lunar\\Filament\\FieldTypes\\Number',
        'Lunar\\Admin\\Support\\FieldTypes\\TextField' => 'Lunar\\Filament\\FieldTypes\\TextField',
        'Lunar\\Admin\\Support\\FieldTypes\\Toggle' => 'Lunar\\Filament\\FieldTypes\\Toggle',
        'Lunar\\Admin\\Support\\FieldTypes\\TranslatedText' => 'Lunar\\Filament\\FieldTypes\\TranslatedText',
        'Lunar\\Admin\\Support\\FieldTypes\\Vimeo' => 'Lunar\\Filament\\FieldTypes\\Vimeo',
        'Lunar\\Admin\\Support\\FieldTypes\\YouTube' => 'Lunar\\Filament\\FieldTypes\\YouTube',
        'Lunar\\Admin\\Support\\Synthesizers\\AbstractFieldSynth' => 'Lunar\\Filament\\Synthesizers\\AbstractFieldSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\DropdownSynth' => 'Lunar\\Filament\\Synthesizers\\DropdownSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\FileSynth' => 'Lunar\\Filament\\Synthesizers\\FileSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\ListSynth' => 'Lunar\\Filament\\Synthesizers\\ListSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\NumberSynth' => 'Lunar\\Filament\\Synthesizers\\NumberSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\PriceSynth' => 'Lunar\\Filament\\Synthesizers\\PriceSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\TextSynth' => 'Lunar\\Filament\\Synthesizers\\TextSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\ToggleSynth' => 'Lunar\\Filament\\Synthesizers\\ToggleSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\TranslatedTextSynth' => 'Lunar\\Filament\\Synthesizers\\TranslatedTextSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\VimeoSynth' => 'Lunar\\Filament\\Synthesizers\\VimeoSynth',
        'Lunar\\Admin\\Support\\Synthesizers\\YouTubeSynth' => 'Lunar\\Filament\\Synthesizers\\YouTubeSynth',
        'Lunar\\Admin\\Filament\\Widgets\\Dashboard\\Orders\\AverageOrderValueChart' => 'Lunar\\Filament\\Widgets\\Dashboard\\Orders\\AverageOrderValueChart',
        'Lunar\\Admin\\Filament\\Widgets\\Dashboard\\Orders\\LatestOrdersTable' => 'Lunar\\Filament\\Widgets\\Dashboard\\Orders\\LatestOrdersTable',
        'Lunar\\Admin\\Filament\\Widgets\\Dashboard\\Orders\\NewVsReturningCustomersChart' => 'Lunar\\Filament\\Widgets\\Dashboard\\Orders\\NewVsReturningCustomersChart',
        'Lunar\\Admin\\Filament\\Widgets\\Dashboard\\Orders\\OrdersSalesChart' => 'Lunar\\Filament\\Widgets\\Dashboard\\Orders\\OrdersSalesChart',
        'Lunar\\Admin\\Filament\\Widgets\\Dashboard\\Orders\\OrderStatsOverview' => 'Lunar\\Filament\\Widgets\\Dashboard\\Orders\\OrderStatsOverview',
        'Lunar\\Admin\\Filament\\Widgets\\Dashboard\\Orders\\OrderTotalsChart' => 'Lunar\\Filament\\Widgets\\Dashboard\\Orders\\OrderTotalsChart',
        'Lunar\\Admin\\Filament\\Widgets\\Dashboard\\Orders\\PopularProductsTable' => 'Lunar\\Filament\\Widgets\\Dashboard\\Orders\\PopularProductsTable',
        'Lunar\\Admin\\Filament\\Widgets\\Products\\VariantSwitcherTable' => 'Lunar\\Filament\\Widgets\\Products\\VariantSwitcherTable',
        'Lunar\\Admin\\Support\\Forms\\AttributeData' => 'Lunar\\Filament\\Support\\Forms\\AttributeData',
        'Lunar\\Admin\\Support\\Facades\\AttributeData' => 'Lunar\\Filament\\Support\\Facades\\AttributeData',
        'Lunar\\Admin\\Filament\\Resources\\ActivityResource\\Schemas\\ActivityForm' => 'Lunar\\Filament\\Schemas\\Activity\\ActivityForm',
        'Lunar\\Admin\\Filament\\Resources\\AttributeGroupResource\\Schemas\\AttributeGroupForm' => 'Lunar\\Filament\\Schemas\\AttributeGroup\\AttributeGroupForm',
        'Lunar\\Admin\\Filament\\Resources\\BrandResource\\Schemas\\BrandForm' => 'Lunar\\Filament\\Schemas\\Brand\\BrandForm',
        'Lunar\\Admin\\Filament\\Resources\\ChannelResource\\Schemas\\ChannelForm' => 'Lunar\\Filament\\Schemas\\Channel\\ChannelForm',
        'Lunar\\Admin\\Filament\\Resources\\CollectionResource\\Schemas\\CollectionForm' => 'Lunar\\Filament\\Schemas\\Collection\\CollectionForm',
        'Lunar\\Admin\\Filament\\Resources\\CollectionGroupResource\\Schemas\\CollectionGroupForm' => 'Lunar\\Filament\\Schemas\\CollectionGroup\\CollectionGroupForm',
        'Lunar\\Admin\\Filament\\Resources\\CurrencyResource\\Schemas\\CurrencyForm' => 'Lunar\\Filament\\Schemas\\Currency\\CurrencyForm',
        'Lunar\\Admin\\Filament\\Resources\\CustomerResource\\Schemas\\CustomerForm' => 'Lunar\\Filament\\Schemas\\Customer\\CustomerForm',
        'Lunar\\Admin\\Filament\\Resources\\CustomerGroupResource\\Schemas\\CustomerGroupForm' => 'Lunar\\Filament\\Schemas\\CustomerGroup\\CustomerGroupForm',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\Schemas\\DiscountForm' => 'Lunar\\Filament\\Schemas\\Discount\\DiscountForm',
        'Lunar\\Admin\\Filament\\Resources\\LanguageResource\\Schemas\\LanguageForm' => 'Lunar\\Filament\\Schemas\\Language\\LanguageForm',
        'Lunar\\Admin\\Filament\\Resources\\ProductResource\\Schemas\\ProductForm' => 'Lunar\\Filament\\Schemas\\Product\\ProductForm',
        'Lunar\\Admin\\Filament\\Resources\\ProductOptionResource\\Schemas\\ProductOptionForm' => 'Lunar\\Filament\\Schemas\\ProductOption\\ProductOptionForm',
        'Lunar\\Admin\\Filament\\Resources\\ProductTypeResource\\Schemas\\ProductTypeForm' => 'Lunar\\Filament\\Schemas\\ProductType\\ProductTypeForm',
        'Lunar\\Admin\\Filament\\Resources\\ProductVariantResource\\Schemas\\ProductVariantForm' => 'Lunar\\Filament\\Schemas\\ProductVariant\\ProductVariantForm',
        'Lunar\\Admin\\Filament\\Resources\\StaffResource\\Schemas\\StaffForm' => 'Lunar\\Filament\\Schemas\\Staff\\StaffForm',
        'Lunar\\Admin\\Filament\\Resources\\TagResource\\Schemas\\TagForm' => 'Lunar\\Filament\\Schemas\\Tag\\TagForm',
        'Lunar\\Admin\\Filament\\Resources\\TaxClassResource\\Schemas\\TaxClassForm' => 'Lunar\\Filament\\Schemas\\TaxClass\\TaxClassForm',
        'Lunar\\Admin\\Filament\\Resources\\TaxRateResource\\Schemas\\TaxRateForm' => 'Lunar\\Filament\\Schemas\\TaxRate\\TaxRateForm',
        'Lunar\\Admin\\Filament\\Resources\\TaxZoneResource\\Schemas\\TaxZoneForm' => 'Lunar\\Filament\\Schemas\\TaxZone\\TaxZoneForm',
        'Lunar\\Admin\\Filament\\Resources\\ActivityResource\\Tables\\ActivityTable' => 'Lunar\\Filament\\Tables\\Activity\\ActivityTable',
        'Lunar\\Admin\\Filament\\Resources\\AttributeGroupResource\\Tables\\AttributeGroupTable' => 'Lunar\\Filament\\Tables\\AttributeGroup\\AttributeGroupTable',
        'Lunar\\Admin\\Filament\\Resources\\BrandResource\\Tables\\BrandTable' => 'Lunar\\Filament\\Tables\\Brand\\BrandTable',
        'Lunar\\Admin\\Filament\\Resources\\ChannelResource\\Tables\\ChannelTable' => 'Lunar\\Filament\\Tables\\Channel\\ChannelTable',
        'Lunar\\Admin\\Filament\\Resources\\CollectionGroupResource\\Tables\\CollectionGroupTable' => 'Lunar\\Filament\\Tables\\CollectionGroup\\CollectionGroupTable',
        'Lunar\\Admin\\Filament\\Resources\\CurrencyResource\\Tables\\CurrencyTable' => 'Lunar\\Filament\\Tables\\Currency\\CurrencyTable',
        'Lunar\\Admin\\Filament\\Resources\\CustomerResource\\Tables\\CustomerTable' => 'Lunar\\Filament\\Tables\\Customer\\CustomerTable',
        'Lunar\\Admin\\Filament\\Resources\\CustomerGroupResource\\Tables\\CustomerGroupTable' => 'Lunar\\Filament\\Tables\\CustomerGroup\\CustomerGroupTable',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\Tables\\DiscountTable' => 'Lunar\\Filament\\Tables\\Discount\\DiscountTable',
        'Lunar\\Admin\\Filament\\Resources\\LanguageResource\\Tables\\LanguageTable' => 'Lunar\\Filament\\Tables\\Language\\LanguageTable',
        'Lunar\\Admin\\Filament\\Resources\\OrderResource\\Tables\\OrderTable' => 'Lunar\\Filament\\Tables\\Order\\OrderTable',
        'Lunar\\Admin\\Filament\\Resources\\ProductResource\\Tables\\ProductTable' => 'Lunar\\Filament\\Tables\\Product\\ProductTable',
        'Lunar\\Admin\\Filament\\Resources\\ProductOptionResource\\Tables\\ProductOptionTable' => 'Lunar\\Filament\\Tables\\ProductOption\\ProductOptionTable',
        'Lunar\\Admin\\Filament\\Resources\\ProductTypeResource\\Tables\\ProductTypeTable' => 'Lunar\\Filament\\Tables\\ProductType\\ProductTypeTable',
        'Lunar\\Admin\\Filament\\Resources\\ProductVariantResource\\Tables\\ProductVariantTable' => 'Lunar\\Filament\\Tables\\ProductVariant\\ProductVariantTable',
        'Lunar\\Admin\\Filament\\Resources\\StaffResource\\Tables\\StaffTable' => 'Lunar\\Filament\\Tables\\Staff\\StaffTable',
        'Lunar\\Admin\\Filament\\Resources\\TagResource\\Tables\\TagTable' => 'Lunar\\Filament\\Tables\\Tag\\TagTable',
        'Lunar\\Admin\\Filament\\Resources\\TaxClassResource\\Tables\\TaxClassTable' => 'Lunar\\Filament\\Tables\\TaxClass\\TaxClassTable',
        'Lunar\\Admin\\Filament\\Resources\\TaxRateResource\\Tables\\TaxRateTable' => 'Lunar\\Filament\\Tables\\TaxRate\\TaxRateTable',
        'Lunar\\Admin\\Filament\\Resources\\TaxZoneResource\\Tables\\TaxZoneTable' => 'Lunar\\Filament\\Tables\\TaxZone\\TaxZoneTable',
        'Lunar\\Admin\\Filament\\Resources\\AttributeGroupResource\\RelationManagers\\AttributesRelationManager' => 'Lunar\\Filament\\RelationManagers\\AttributeGroup\\AttributesRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\CustomerResource\\RelationManagers\\AddressRelationManager' => 'Lunar\\Filament\\RelationManagers\\Customer\\AddressRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\CustomerResource\\RelationManagers\\OrdersRelationManager' => 'Lunar\\Filament\\RelationManagers\\Customer\\OrdersRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\CustomerResource\\RelationManagers\\UserRelationManager' => 'Lunar\\Filament\\RelationManagers\\Customer\\UserRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\BrandLimitationRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\BrandLimitationRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\CollectionConditionRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\CollectionConditionRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\CollectionLimitationRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\CollectionLimitationRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\CustomerLimitationRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\CustomerLimitationRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\ProductConditionRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\ProductConditionRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\ProductLimitationRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\ProductLimitationRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\ProductRewardRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\ProductRewardRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\DiscountResource\\RelationManagers\\ProductVariantLimitationRelationManager' => 'Lunar\\Filament\\RelationManagers\\Discount\\ProductVariantLimitationRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\ProductResource\\RelationManagers\\CustomerGroupPricingRelationManager' => 'Lunar\\Filament\\RelationManagers\\Product\\CustomerGroupPricingRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\ProductResource\\RelationManagers\\CustomerGroupRelationManager' => 'Lunar\\Filament\\RelationManagers\\Product\\CustomerGroupRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\ProductOptionResource\\RelationManagers\\ValuesRelationManager' => 'Lunar\\Filament\\RelationManagers\\ProductOption\\ValuesRelationManager',
        'Lunar\\Admin\\Filament\\Resources\\TaxRateResource\\RelationManagers\\TaxRateAmountRelationManager' => 'Lunar\\Filament\\RelationManagers\\TaxRate\\TaxRateAmountRelationManager',
        // Cross-package primitives broken out of the admin shell.
        'Lunar\\Admin\\Support\\Concerns\\CallsHooks' => 'Lunar\\Filament\\Support\\Concerns\\CallsHooks',
        'Lunar\\Admin\\Support\\Concerns\\RelationManagers\\ExtendsForms' => 'Lunar\\Filament\\Support\\Concerns\\RelationManagers\\ExtendsForms',
        'Lunar\\Admin\\Support\\Concerns\\RelationManagers\\ExtendsTables' => 'Lunar\\Filament\\Support\\Concerns\\RelationManagers\\ExtendsTables',
        'Lunar\\Admin\\Support\\RelationManagers\\BaseRelationManager' => 'Lunar\\Filament\\RelationManagers\\BaseRelationManager',
        'Lunar\\Admin\\Support\\CustomerStatus' => 'Lunar\\Filament\\Support\\CustomerStatus',
        'Lunar\\Admin\\Support\\Actions\\Traits\\CreatesChildCollections' => 'Lunar\\Filament\\Support\\Concerns\\CreatesChildCollections',
        'Lunar\\Admin\\Base\\LunarPanelDiscountInterface' => 'Lunar\\Filament\\Contracts\\DiscountFormType',
        // Catalog *Updated events removed in v2 (spec 0043) — catalog changes
        // now emit core cache-invalidation events. The CustomerUserEdited bridge
        // survives (it drives customer search reindexing).
        'Lunar\\Admin\\Events\\CustomerUserEdited' => 'Lunar\\Filament\\Events\\CustomerUserEdited',
        // Resource-tied widgets moved to bridge.
        'Lunar\\Admin\\Filament\\Resources\\CustomerResource\\Widgets\\CustomerStatsOverviewWidget' => 'Lunar\\Filament\\Widgets\\Customer\\CustomerStatsOverviewWidget',
        'Lunar\\Admin\\Filament\\Resources\\ProductResource\\Widgets\\ProductOptionsWidget' => 'Lunar\\Filament\\Widgets\\Products\\ProductOptionsWidget',
        'Lunar\\Admin\\Filament\\Resources\\CollectionGroupResource\\Widgets\\CollectionTreeView' => 'Lunar\\Filament\\Widgets\\Collections\\CollectionTreeView',
        'Lunar\\Admin\\Actions\\Products\\MapVariantsToProductOptions' => 'Lunar\\Filament\\Actions\\Products\\MapVariantsToProductOptions',
        'Lunar\\Admin\\Support\\Actions\\Collections\\CreateChildCollection' => 'Lunar\\Filament\\Actions\\Collections\\CreateChildCollection',
        'Lunar\\Admin\\Support\\Actions\\Collections\\CreateRootCollection' => 'Lunar\\Filament\\Actions\\Collections\\CreateRootCollection',
        'Lunar\\Admin\\Support\\Actions\\Collections\\DeleteCollection' => 'Lunar\\Filament\\Actions\\Collections\\DeleteCollection',
        'Lunar\\Admin\\Support\\Actions\\Collections\\MoveCollection' => 'Lunar\\Filament\\Actions\\Collections\\MoveCollection',
        // --- Spec 0010: Staff + auth move to core ---
        'Lunar\\Admin\\Models\\Staff' => 'Lunar\\Core\\Models\\Staff',
        'Lunar\\Admin\\Auth\\Manifest' => 'Lunar\\Core\\Auth\\Manifest',
        'Lunar\\Admin\\Database\\Factories\\StaffFactory' => 'Lunar\\Core\\Database\\Factories\\StaffFactory',
        'Lunar\\Admin\\Database\\State\\EnsureBaseRolesAndPermissions' => 'Lunar\\Core\\Database\\State\\EnsureBaseRolesAndPermissions',
        'Lunar\\Admin\\Support\\DataTransferObjects\\Permission' => 'Lunar\\Core\\Support\\DataTransferObjects\\Permission',
        'Lunar\\Admin\\Support\\DataTransferObjects\\Role' => 'Lunar\\Core\\Support\\DataTransferObjects\\Role',
        'Lunar\\Admin\\Support\\Facades\\LunarAccessControl' => 'Lunar\\Core\\Support\\Facades\\LunarAccessControl',

        // --- Spec 0075: first staff account creation moves to core ---
        'Lunar\\Admin\\Console\\Commands\\MakeLunarAdminCommand' => 'Lunar\\Core\\Console\\Commands\\CreateAdmin',

    ];

    /**
     * Plain (no-config) Rector rules contributed by v2 breaking specs.
     *
     * @var array<int, class-string>
     */
    public const V1_TO_V2 = [
        RewriteModelClassCallRector::class,
        RewriteOrderRefundCallRector::class,
        RetypeFormatterStyleParamRector::class,
    ];

    /**
     * Catalogue fields promoted from `attribute_data` to dedicated translatable
     * columns in spec 0018. Drives `RewriteTranslatedFieldCallRector`, which
     * rewrites `translateAttribute('name')` / `attr('name')` reads to
     * `translate('name')`. Brand's `name` stays a plain (non-translatable)
     * string column, so it needs no read rewrite — only `description` /
     * `short_description` moved for Brand, and those were never attribute-backed
     * reads in v1.
     *
     * @var list<string>
     */
    public const V1_TO_V2_TRANSLATED_FIELDS = [
        'name',
        'description',
    ];

    /**
     * Money attribute columns per model, contributed by spec 0012.
     *
     * Drives the four price-rewrite rules under
     * `Lunar\Upgrade\Rector\Price\*`. Both v1 (`Lunar\Models\*`) and v2
     * (`Lunar\Core\Models\*`) class strings are listed so the rules apply
     * whether or not `RenameClassRector` has already rewritten the
     * surrounding type information by the time they fire.
     *
     * @var array<class-string, list<string>>
     */
    public const V1_TO_V2_MONEY_ATTRIBUTES = [
        'Lunar\\Core\\Models\\Order' => ['sub_total', 'discount_total', 'tax_total', 'total', 'shipping_total'],
        'Lunar\\Models\\Order' => ['sub_total', 'discount_total', 'tax_total', 'total', 'shipping_total'],
        'Lunar\\Core\\Models\\OrderLine' => ['unit_price', 'sub_total', 'tax_total', 'discount_total', 'total'],
        'Lunar\\Models\\OrderLine' => ['unit_price', 'sub_total', 'tax_total', 'discount_total', 'total'],
        'Lunar\\Core\\Models\\Transaction' => ['amount'],
        'Lunar\\Models\\Transaction' => ['amount'],
        'Lunar\\Core\\Models\\Price' => ['price', 'list_price'],
        'Lunar\\Models\\Price' => ['price', 'list_price'],
    ];

    /**
     * Money attribute columns that carry a unit-quantity semantic,
     * contributed by spec 0012.
     *
     * Only the catalogue `Price` model (`lunar_prices.price` /
     * `list_price`) stores a raw per-pack price that requires
     * division by `priceable->unit_quantity` to display per-single-unit.
     * `OrderLine.unit_price` is persisted *already divided* to per-
     * single-unit by `CalculateLineSubtotal`, so `unitDecimal` /
     * `unitFormatted` calls on order lines were a latent v1 bug (double
     * division when `unit_quantity > 1`). The unit-rewrite rule only
     * touches the catalogue Price model; `unit*` calls on other models
     * are left alone for manual review (the upgrade docs flag this).
     *
     * @var array<class-string, list<string>>
     */
    public const V1_TO_V2_UNIT_AWARE_ATTRIBUTES = [
        'Lunar\\Core\\Models\\Price' => ['price', 'list_price'],
        'Lunar\\Models\\Price' => ['price', 'list_price'],
    ];

    /**
     * Classes/traits removed in v2 with no replacement target.
     *
     * Spec 0007 inlined the ten page-extension traits under
     * `Lunar\Admin\Support\Pages\Concerns\` into their five base page
     * classes. Downstream code that imported a trait directly hits a
     * "Trait not found" error pointing at the FQCN listed here — drop
     * the `use` line, the base page already provides the behaviour.
     *
     * @var array<int, class-string>
     */
    public const V1_TO_V2_REMOVED_CLASSES = [
        // Spec 0016 removed the action base class; actions now implement a
        // contract and are resolved from the container. A consumer that
        // extended AbstractAction should implement the relevant
        // `Lunar\Core\Contracts\Actions\…` interface instead.
        'Lunar\\Actions\\AbstractAction',
        'Lunar\\Core\\Actions\\AbstractAction',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsFooterWidgets',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsFormActions',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsForms',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsHeaderActions',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsHeaderWidgets',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsHeadings',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsInfolist',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsTablePagination',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsTables',
        'Lunar\\Admin\\Support\\Pages\\Concerns\\ExtendsTabs',
    ];

    /**
     * Method renames on Lunar base classes, contributed by v2 breaking
     * specs. Consumed by `RenameMethodRector` via `MethodCallRename`.
     *
     * Spec 0005 dropped the resource-level wrapper traits, renaming the
     * protected slot methods `getDefaultForm` / `getDefaultTable` back to
     * Filament's native `form` / `table` entry points.
     *
     * Spec 0056 renamed the `ProductType` attribute pivot relation
     * `mappedAttributes` to `attributeMapping`, freeing `mappedAttributes`
     * for the standard attributable meaning (the type's own fields) now that
     * product types carry `attribute_data`. Both the v1 and v2 class strings
     * are listed so the rename applies regardless of whether
     * `RenameClassRector` has already rewritten the surrounding type.
     *
     * @var array<int, array{0: class-string, 1: string, 2: string}>
     */
    public const V1_TO_V2_METHOD_RENAMES = [
        ['Lunar\\Admin\\Support\\Resources\\BaseResource', 'getDefaultForm', 'form'],
        ['Lunar\\Admin\\Support\\Resources\\BaseResource', 'getDefaultTable', 'table'],
        ['Lunar\\Core\\Models\\ProductType', 'mappedAttributes', 'attributeMapping'],
        ['Lunar\\Models\\ProductType', 'mappedAttributes', 'attributeMapping'],
    ];

    /**
     * Property/attribute renames on Lunar models, contributed by v2
     * breaking specs. Consumed by `RenamePropertyRector` via
     * `RenameProperty`.
     *
     * Spec 0017 renamed the catalogue `Price` model's `compare_price`
     * column to `list_price`. The rename fires before the money-attribute
     * rewrites (which now key on `list_price`), so a chained v1 call such
     * as `$price->compare_price->decimal()` first becomes
     * `$price->list_price->decimal()` and then `$price->decimal('list_price')`.
     * Both the v1 (`Lunar\Models\Price`) and v2 (`Lunar\Core\Models\Price`)
     * class strings are listed so the rename applies regardless of whether
     * `RenameClassRector` has already rewritten the surrounding type.
     *
     * Spec 0048 renamed the `ProductVariant` selling-policy attribute
     * `purchasable` to `selling_policy`. `RenamePropertyRector` is class-scoped,
     * so it rewrites `->purchasable` only on expressions typed as
     * `ProductVariant` — the `CartLine`/`OrderLine` morph relation
     * (`$line->purchasable`) and the `customer_group_product.purchasable` pivot
     * boolean, which mean unrelated things, are left untouched. A fully untyped
     * `$model->purchasable` cannot be inferred and is flagged for manual review
     * in the upgrade notes (those are almost always the morph/pivot accesses,
     * which must not be renamed anyway).
     *
     * Spec 0056 renamed the `ProductType` attribute pivot relation (see
     * `V1_TO_V2_METHOD_RENAMES`); the property fetch form
     * (`$type->mappedAttributes`) is covered here.
     *
     * @var array<int, array{0: class-string, 1: string, 2: string}>
     */
    public const V1_TO_V2_PROPERTY_RENAMES = [
        ['Lunar\\Core\\Models\\Price', 'compare_price', 'list_price'],
        ['Lunar\\Models\\Price', 'compare_price', 'list_price'],
        ['Lunar\\Core\\Models\\ProductVariant', 'purchasable', 'selling_policy'],
        ['Lunar\\Models\\ProductVariant', 'purchasable', 'selling_policy'],
        ['Lunar\\Core\\Models\\ProductType', 'mappedAttributes', 'attributeMapping'],
        ['Lunar\\Models\\ProductType', 'mappedAttributes', 'attributeMapping'],
    ];

    /**
     * Maps each concrete action (post-rename, v2 namespace) to the contract a
     * caller should resolve from the container. Drives
     * `RewriteActionRunCallRector`, which rewrites `Action::run(...)` to
     * `app(Contract::class)->execute(...)` after spec 0016 removed the
     * `AbstractAction::run()` / `::make()` shortcuts.
     *
     * @var array<class-string, class-string>
     */
    public const V1_TO_V2_ACTION_CONTRACTS = [
        'Lunar\\Core\\Actions\\Carts\\AddAddress' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\AddsAddress',
        'Lunar\\Core\\Actions\\Carts\\AddOrUpdatePurchasable' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\AddsOrUpdatesPurchasable',
        'Lunar\\Core\\Actions\\Carts\\AssociateUser' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\AssociatesUser',
        'Lunar\\Core\\Actions\\Carts\\CalculateLine' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\CalculatesLine',
        'Lunar\\Core\\Actions\\Carts\\CalculateLineSubtotal' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\CalculatesLineSubtotal',
        'Lunar\\Core\\Actions\\Carts\\CreateOrder' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\CreatesOrder',
        'Lunar\\Core\\Actions\\Carts\\GenerateFingerprint' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\GeneratesFingerprint',
        'Lunar\\Core\\Actions\\Carts\\GetExistingCartLine' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\GetsExistingCartLine',
        'Lunar\\Core\\Actions\\Carts\\MergeCart' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\MergesCart',
        'Lunar\\Core\\Actions\\Carts\\RemovePurchasable' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\RemovesPurchasable',
        'Lunar\\Core\\Actions\\Carts\\SetShippingOption' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\SetsShippingOption',
        'Lunar\\Core\\Actions\\Carts\\UpdateCartLine' => 'Lunar\\Core\\Contracts\\Actions\\Carts\\UpdatesCartLine',
        'Lunar\\Core\\Actions\\Collections\\CreateChildCollection' => 'Lunar\\Core\\Contracts\\Actions\\Collections\\CreatesChildCollection',
        'Lunar\\Core\\Actions\\Collections\\CreateRootCollection' => 'Lunar\\Core\\Contracts\\Actions\\Collections\\CreatesRootCollection',
        'Lunar\\Core\\Actions\\Collections\\DeleteCollection' => 'Lunar\\Core\\Contracts\\Actions\\Collections\\DeletesCollection',
        'Lunar\\Core\\Actions\\Collections\\MoveCollection' => 'Lunar\\Core\\Contracts\\Actions\\Collections\\MovesCollection',
        'Lunar\\Core\\Actions\\Collections\\SortProducts' => 'Lunar\\Core\\Contracts\\Actions\\Collections\\SortsProducts',
        'Lunar\\Core\\Actions\\Orders\\CaptureOrder' => 'Lunar\\Core\\Contracts\\Actions\\Orders\\CapturesOrder',
        'Lunar\\Core\\Actions\\Orders\\GenerateOrderReference' => 'Lunar\\Core\\Contracts\\Actions\\Orders\\GeneratesOrderReference',
        'Lunar\\Core\\Actions\\Orders\\RefundOrder' => 'Lunar\\Core\\Contracts\\Actions\\Orders\\RefundsOrder',
        'Lunar\\Core\\Actions\\Products\\AdjustStock' => 'Lunar\\Core\\Contracts\\Actions\\Products\\AdjustsStock',
        'Lunar\\Core\\Actions\\Products\\DuplicateProduct' => 'Lunar\\Core\\Contracts\\Actions\\Products\\DuplicatesProduct',
        'Lunar\\Core\\Actions\\Products\\MapVariantsToProductOptions' => 'Lunar\\Core\\Contracts\\Actions\\Products\\MapsVariantsToProductOptions',
        'Lunar\\Core\\Actions\\Products\\UpdateProductStatus' => 'Lunar\\Core\\Contracts\\Actions\\Products\\UpdatesProductStatus',
        'Lunar\\Core\\Actions\\Taxes\\GetTaxZone' => 'Lunar\\Core\\Contracts\\Actions\\Taxes\\GetsTaxZone',
    ];
}
