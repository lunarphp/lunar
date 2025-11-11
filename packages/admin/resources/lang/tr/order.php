<?php

return [

    'label' => 'Sipariş',

    'plural_label' => 'Siparişler',

    'breadcrumb' => [
        'manage' => 'Yönet',
    ],

    'tabs' => [
        'all' => 'Tümü',
    ],

    'transactions' => [
        'capture' => 'Tahsil Edildi',
        'intent' => 'Ödeme Niyeti',
        'refund' => 'İade Edildi',
        'failed' => 'Başarısız',
    ],

    'table' => [
        'status' => [
            'label' => 'Durum',
        ],
        'reference' => [
            'label' => 'Referans',
        ],
        'customer_reference' => [
            'label' => 'Müşteri Referansı',
        ],
        'customer' => [
            'label' => 'Müşteri',
        ],
        'tags' => [
            'label' => 'Etiketler',
        ],
        'postcode' => [
            'label' => 'Posta Kodu',
        ],
        'email' => [
            'label' => 'E-posta',
            'copy_message' => 'E-posta adresi kopyalandı',
        ],
        'phone' => [
            'label' => 'Telefon',
        ],
        'total' => [
            'label' => 'Toplam',
        ],
        'date' => [
            'label' => 'Tarih',
        ],
        'new_customer' => [
            'label' => 'Müşteri Türü',
        ],
        'placed_after' => [
            'label' => 'Bu Tarihten Sonra',
        ],
        'placed_before' => [
            'label' => 'Bu Tarihten Önce',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Ad',
            ],
            'last_name' => [
                'label' => 'Soyad',
            ],
            'line_one' => [
                'label' => 'Adres Satırı 1',
            ],
            'line_two' => [
                'label' => 'Adres Satırı 2',
            ],
            'line_three' => [
                'label' => 'Adres Satırı 3',
            ],
            'company_name' => [
                'label' => 'Şirket Adı',
            ],
            'tax_identifier' => [
                'label' => 'Vergi Kimlik Numarası',
            ],
            'contact_phone' => [
                'label' => 'Telefon',
            ],
            'contact_email' => [
                'label' => 'E-posta Adresi',
            ],
            'city' => [
                'label' => 'Şehir',
            ],
            'state' => [
                'label' => 'Eyalet / İl',
            ],
            'postcode' => [
                'label' => 'Posta Kodu',
            ],
            'country_id' => [
                'label' => 'Ülke',
            ],
        ],

        'reference' => [
            'label' => 'Referans',
        ],
        'status' => [
            'label' => 'Durum',
        ],
        'transaction' => [
            'label' => 'İşlem',
        ],
        'amount' => [
            'label' => 'Miktar',

            'hint' => [
                'less_than_total' => 'Toplam işlem tutarından daha az bir miktar tahsil etmek üzeresiniz',
            ],
        ],

        'notes' => [
            'label' => 'Notlar',
        ],
        'confirm' => [
            'label' => 'Onayla',

            'alert' => 'Onay gerekli',

            'hint' => [
                'capture' => 'Lütfen bu ödemeyi tahsil etmek istediğinizi onaylayın',
                'refund' => 'Lütfen bu tutarı iade etmek istediğinizi onaylayın.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Notlar',
            'placeholder' => 'Bu siparişte not yok',
        ],
        'delivery_instructions' => [
            'label' => 'Teslimat Talimatları',
        ],
        'shipping_total' => [
            'label' => 'Kargo Toplamı',
        ],
        'paid' => [
            'label' => 'Ödendi',
        ],
        'refund' => [
            'label' => 'İade',
        ],
        'unit_price' => [
            'label' => 'Birim Fiyat',
        ],
        'quantity' => [
            'label' => 'Miktar',
        ],
        'sub_total' => [
            'label' => 'Ara Toplam',
        ],
        'discount_total' => [
            'label' => 'İndirim Toplamı',
        ],
        'total' => [
            'label' => 'Toplam',
        ],
        'current_stock_level' => [
            'message' => 'Mevcut Stok Seviyesi: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'sipariş anında: :count',
        ],
        'status' => [
            'label' => 'Durum',
        ],
        'reference' => [
            'label' => 'Referans',
        ],
        'customer_reference' => [
            'label' => 'Müşteri Referansı',
        ],
        'channel' => [
            'label' => 'Kanal',
        ],
        'date_created' => [
            'label' => 'Oluşturulma Tarihi',
        ],
        'date_placed' => [
            'label' => 'Sipariş Tarihi',
        ],
        'new_returning' => [
            'label' => 'Yeni / Geri Dönen',
        ],
        'new_customer' => [
            'label' => 'Yeni Müşteri',
        ],
        'returning_customer' => [
            'label' => 'Geri Dönen Müşteri',
        ],
        'shipping_address' => [
            'label' => 'Kargo Adresi',
        ],
        'billing_address' => [
            'label' => 'Fatura Adresi',
        ],
        'address_not_set' => [
            'label' => 'Adres ayarlanmamış',
        ],
        'billing_matches_shipping' => [
            'label' => 'Kargo adresi ile aynı',
        ],
        'additional_info' => [
            'label' => 'Ek Bilgiler',
        ],
        'no_additional_info' => [
            'label' => 'Ek Bilgi Yok',
        ],
        'tags' => [
            'label' => 'Etiketler',
        ],
        'timeline' => [
            'label' => 'Zaman Çizelgesi',
        ],
        'transactions' => [
            'label' => 'İşlemler',
            'placeholder' => 'İşlem yok',
        ],
        'alert' => [
            'requires_capture' => 'Bu sipariş hala ödemenin tahsil edilmesini gerektiriyor.',
            'partially_refunded' => 'Bu sipariş kısmen iade edildi.',
            'refunded' => 'Bu sipariş iade edildi.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Durumu Güncelle',
            'notification' => 'Siparişlerin durumu güncellendi',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'Yeni durum',
            ],
            'additional_content' => [
                'label' => 'Ek içerik',
            ],
            'additional_email_recipient' => [
                'label' => 'Ek e-posta alıcısı',
                'placeholder' => 'opsiyonel',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'PDF İndir',
            'notification' => 'Sipariş PDF\'i indiriliyor',
        ],
        'edit_address' => [
            'label' => 'Düzenle',

            'notification' => [
                'error' => 'Hata',

                'billing_address' => [
                    'saved' => 'Fatura adresi kaydedildi',
                ],

                'shipping_address' => [
                    'saved' => 'Kargo adresi kaydedildi',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Düzenle',
            'form' => [
                'tags' => [
                    'label' => 'Etiketler',
                    'helper_text' => 'Etiketleri Enter, Tab veya virgül (,) tuşuna basarak ayırın',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Ödemeyi Tahsil Et',

            'notification' => [
                'error' => 'Yakalama ile ilgili bir sorun oluştu',
                'success' => 'Yakalama başarılı',
            ],
        ],
        'refund_payment' => [
            'label' => 'İade',

            'notification' => [
                'error' => 'İade ile ilgili bir sorun oluştu',
                'success' => 'İade başarılı',
            ],
        ],
    ],

];
