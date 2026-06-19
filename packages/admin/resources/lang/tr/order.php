<?php

return [
    'label' => 'Sipariş',
    'plural_label' => 'Siparişler',
    'breadcrumb' => [
        'manage' => 'Yönet',
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
            'label' => 'Order',
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
            'label' => 'Order',
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
            'label' => 'Update Status',
            'notification' => 'Order status updated',
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

    'fulfilments' => [
        'heading' => 'Karşılamalar',
        'unreferenced' => '#:id numaralı karşılama',
        'on_hold' => 'Beklemede',
        'empty' => 'Henüz karşılama yok.',
        'columns' => [
            'reference' => 'Referans',
            'state' => 'Durum',
            'items' => 'Ürünler',
            'tracking' => 'Takip',
            'shipped_at' => 'Gönderim tarihi',
            'handed_over' => [
                'shipping' => 'Gönderim tarihi',
                'collection' => 'Teslim alma tarihi',
                'digital' => 'Sağlanma tarihi',
            ],
            'handed_over_default' => 'Karşılanma tarihi',
        ],
        'actions' => [
            'more' => 'Diğer işlemler',
            'notify' => 'Müşteriyi bilgilendir',
            'add_tracking' => [
                'label' => 'Takip ekle',
                'modal_heading' => 'Takip ekle',
                'notification' => [
                    'success' => 'Takip eklendi.',
                    'error' => 'Takip eklenemedi.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Takibi kaldır',
                'notification' => [
                    'success' => 'Takip kaldırıldı.',
                    'error' => 'Takip kaldırılamadı.',
                ],
            ],
            'create' => [
                'label' => 'Karşılama oluştur',
                'modal_heading' => 'Karşılama oluştur',
                'empty' => 'Tüm satırlar zaten karşılandı.',
                'notification' => [
                    'success' => 'Karşılama oluşturuldu.',
                    'error' => 'Karşılama oluşturulamadı.',
                ],
            ],
            'ship' => [
                'label' => 'Gönderildi olarak işaretle',
                'modal_heading' => 'Karşılamayı gönderildi olarak işaretle',
                'notification' => [
                    'success' => 'Karşılama gönderildi olarak işaretlendi.',
                    'error' => 'Karşılama gönderilemedi.',
                ],
            ],
            'fulfil' => [
                'label' => 'Karşılandı olarak işaretle',
                'modal_heading' => 'Karşılamayı karşılandı olarak işaretle',
                'labels' => [
                    'collection' => 'Teslim alındı olarak işaretle',
                ],
                'notification' => [
                    'success' => 'Karşılama karşılandı olarak işaretlendi.',
                    'error' => 'Karşılama karşılanamadı.',
                ],
            ],
            'cancel' => [
                'label' => 'Karşılamayı iptal et',
                'modal_heading' => 'Karşılamayı iptal et',
                'description' => 'Bu işlem karşılamayı yeniden ilerletilebilmesi için beklemede durumuna döndürür. Tüm gönderim bilgileri temizlenir.',
                'notification' => [
                    'success' => 'Karşılama iptal edildi.',
                    'error' => 'Karşılama iptal edilemedi.',
                ],
            ],
            'change_location' => [
                'label' => 'Konumu değiştir',
                'modal_heading' => 'Karşılama konumunu değiştir',
                'field' => 'Konum',
                'notification' => [
                    'success' => 'Karşılama konumu güncellendi.',
                    'error' => 'Karşılama konumu değiştirilemedi.',
                ],
            ],
            'return' => [
                'label' => 'İade et',
                'notification' => [
                    'success' => 'Karşılama iade edildi.',
                    'error' => 'Karşılama iade edilemedi.',
                ],
            ],
            'update_status' => [
                'label' => 'Durumu güncelle',
            ],
            'transition' => [
                'modal_heading' => 'Karşılama :status olarak işaretlensin mi?',
                'notification' => [
                    'success' => 'Karşılama durumu güncellendi.',
                    'error' => 'Karşılama durumu güncellenemedi.',
                ],
            ],
            'undo_return' => [
                'label' => 'İadeyi geri al',
                'notification' => [
                    'success' => 'İade geri alındı.',
                    'error' => 'İade geri alınamadı.',
                ],
            ],
            'hold' => [
                'label' => 'Karşılamayı beklemeye al',
                'modal_heading' => 'Karşılamayı beklemeye al',
                'reason' => 'Neden',
                'note' => 'Not',
                'notification' => [
                    'success' => 'Karşılama beklemeye alındı.',
                    'error' => 'Karşılama beklemeye alınamadı.',
                ],
            ],
            'release' => [
                'label' => 'Beklemeyi kaldır',
                'notification' => [
                    'success' => 'Karşılamanın beklemesi kaldırıldı.',
                    'error' => 'Karşılamanın beklemesi kaldırılamadı.',
                ],
            ],
            'split' => [
                'label' => 'Böl',
                'confirm' => 'Karşılamayı böl',
                'cancel' => 'İptal',
                'empty' => 'Ayırmak için bir miktar seçin.',
                'modal_heading' => 'Karşılamayı böl',
                'notification' => [
                    'success' => 'Karşılama bölündü.',
                    'error' => 'Karşılama bölünemedi.',
                ],
            ],
            'merge' => [
                'label' => 'Birleştir',
                'confirm' => 'Karşılamayı birleştir',
                'cancel' => 'İptal',
                'modal_heading' => 'Karşılamayı birleştir',
                'description' => 'Birleştirmek istediğiniz ürünleri seçin.',
                'target' => 'Şununla birleştir',
                'empty' => 'Birleştirmek için ürünleri ve bir hedef seçin.',
                'notification' => [
                    'success' => 'Karşılamalar birleştirildi.',
                    'error' => 'Karşılamalar birleştirilemedi.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Miktar',
            'tracking' => 'Takip',
            'tracking_item' => 'Takip #:number',
            'unit_price' => 'Birim Fiyat',
            'sub_total' => 'Ara Toplam',
            'discount_total' => 'İndirim Toplamı',
            'total' => 'Toplam',
            'stock_level' => 'Mevcut Stok Seviyesi: :count',
            'of' => ':count içinden',
            'outstanding' => 'Bekleyen: :count',
            'tracking_number' => 'Takip numarası',
            'tracking_url' => 'Takip URL\'si',
            'carrier' => 'Taşıyıcı',
            'carrier_custom' => 'Özel / diğer',
            'tracking_url_help' => 'Yalnızca otomatik takip bağlantısı olmayan taşıyıcılar için gereklidir.',
            'shipping_method' => 'Gönderim yöntemi',
            'move_quantity' => 'Taşınacak miktar',
        ],
    ],

    'other_items' => [
        'heading' => 'Diğer ürünler',
    ],
];
