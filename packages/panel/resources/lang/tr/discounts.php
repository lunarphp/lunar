<?php

return [
    'title' => 'İndirimler',
    'description' => 'Müşterinin ödediği tutarı azaltan kampanyaları — yüzde, sabit tutar veya bir alana bir bedava teklifi — kurun ve her birinin ne zaman, nerede ve kimler için geçerli olacağını belirleyin.',
    'new_discount' => 'Yeni indirim',
    'create_title' => 'Yeni indirim',
    'create_description' => 'İndirime bir ad verin ve fiyatı nasıl düşüreceğini seçin; geri kalan her şey indirim sayfasında yapılandırılır.',
    'create_discount' => 'İndirim oluştur',
    'back_to_discounts' => 'İndirimlere dön',
    'delete_discount' => 'İndirimi sil',
    'confirm_delete_discount' => 'Bu indirim silinsin mi? Şu anda kullanan sepetler indirim olmadan yeniden hesaplanır.',

    'column_status' => 'Durum',
    'column_name' => 'Ad',
    'column_type' => 'Tür',
    'column_coupon' => 'Kupon',
    'column_window' => 'Süre',
    'column_usage' => 'Kullanım',
    'column_priority' => 'Öncelik',

    'search_placeholder' => 'İndirimlerde ara',
    'filter_status' => 'Durum',
    'filter_all_statuses' => 'Tüm durumlar',
    'filter_type' => 'Tür',
    'filter_all_types' => 'Tüm türler',
    'filter_channel' => 'Kanal',
    'filter_all_channels' => 'Tüm kanallar',
    'filter_customer_group' => 'Müşteri grubu',
    'filter_all_customer_groups' => 'Tüm müşteri grupları',
    'filter_redemption' => 'Uygulanma',
    'filter_all_redemptions' => 'Kuponlu ve otomatik',
    'redemption_coupon' => 'Kupon gerektirir',
    'redemption_automatic' => 'Otomatik uygulanır',
    'sort_priority' => 'Önceliğe göre',
    'sort_name' => 'Ad A-Z',
    'sort_starts' => 'En erken başlayan',
    'sort_ends' => 'En erken biten',
    'sort_uses' => 'En çok kullanılan',
    'count_of' => '{total} içinden {shown}',
    'clear_filters' => 'Filtreleri temizle',
    'empty_title' => 'Eşleşen indirim yok',
    'empty_description' => 'Aramayı veya filtreleri temizleyin ya da yeni bir indirim oluşturun.',
    'empty_none_title' => 'Henüz indirim yok',
    'empty_none_description' => 'Kampanyalara başlamak için ilk indiriminizi oluşturun.',

    'status_active' => 'Etkin',
    'status_scheduled' => 'Planlanmış',
    'status_expired' => 'Süresi dolmuş',
    'status_pending' => 'Beklemede',

    'kpi_active_label' => 'Şu anda etkin',
    'kpi_active_hint' => 'Bugün geçerli',
    'kpi_scheduled_label' => 'Planlanmış',
    'kpi_scheduled_hint' => 'Daha sonra başlıyor',
    'kpi_ending_label' => 'Yakında bitiyor',
    'kpi_ending_hint' => '7 gün içinde',
    'kpi_redemptions_label' => 'Kullanımlar',
    'kpi_redemptions_hint' => 'Tüm indirimler, tüm zamanlar',
    'show_kpis' => 'İstatistikleri göster',

    'summary_percentage_off' => '%:percentage indirim',

    'summary_fixed_amount_off' => ':amount indirim',

    'summary_buy_x_get_y' => ':buy al, :get kazan',

    'field_percentage' => 'İndirim yüzdesi',

    'field_percentage_hint' => 'Uygun her satırdan düşülür.',

    'field_amount' => 'İndirim tutarı',

    'field_amounts_hint' => 'Her para birimi için bir tutar belirleyin. Boş bırakılan para birimi indirim almaz.',

    'field_min_qty' => 'Alınacak miktar',

    'field_reward_qty' => 'Ödül miktarı',

    'field_max_reward_qty' => 'En fazla ödül',

    'field_max_reward_qty_hint' => 'Uygun her seti ödüllendirmek için boş bırakın.',

    'field_automatically_add_rewards' => 'Ödülleri sepete otomatik ekle',

    'field_automatically_add_rewards_hint' => 'Müşterinin eklemesini beklemek yerine ödül ürünlerini onun adına ekler.',

    'section_targets' => 'Kapsamı',

    'section_targets_description' => 'Bu indirimi kataloğun bir bölümüyle sınırlayın. Boş bırakılan blok her şeye uygulanır.',

    'section_customers' => 'Uygun müşteriler',

    'bucket_limitation' => 'Kapsamı',

    'bucket_limitation_description' => 'Yalnızca bunlar indirilir.',

    'bucket_exclusion' => 'Hariç',

    'bucket_exclusion_description' => 'Yukarıdakilere uysa bile asla indirilmez.',

    'bucket_condition' => 'Koşulu sağlayan ürünler',

    'bucket_condition_description' => 'Müşterinin ödülü kazanmak için alması gerekenler.',

    'bucket_reward' => 'Ödül ürünleri',

    'bucket_reward_description' => 'Müşterinin aldığı.',

    'bucket_customers' => 'Uygun müşteriler',

    'bucket_customers_description' => 'İndirimi yalnızca bu müşteriler kullanabilir. Herkese açmak için boş bırakın.',

    'kind_products' => 'Ürünler',

    'kind_variants' => 'Varyantlar',

    'kind_collections' => 'Koleksiyonlar',

    'kind_brands' => 'Markalar',

    'kind_customers' => 'Müşteriler',

    'target_add' => 'Ekle',

    'target_remove' => '{label} kaldır',

    'target_empty' => 'Hiçbir şey seçilmedi, yani her şeye uygulanır.',

    'target_dialog_title' => 'Hedef ekle',

    'target_dialog_description' => 'Bu bloğun kapsayabileceği her şeyde arayın.',

    'target_search_placeholder' => 'Ürün, koleksiyon, marka ara',

    'target_no_results' => 'Eşleşen yok.',

    'target_add_selected' => '{count} ekle',

    'section_conditions' => 'Koşullar',

    'section_conditions_description' => 'Bu indirim uygulanmadan önce sepetin sağlaması gerekenler.',

    'field_min_spend' => 'Asgari harcama',

    'field_min_spend_hint' => 'Her para birimi için bir eşik belirleyin. Boş bırakılan para biriminin asgarisi olmaz.',

    'automatic' => 'Otomatik',
    'no_end_date' => 'Bitiş tarihi yok',
    'usage_unlimited' => 'sınırsız',
    'usage_of' => '{max} içinden {used}',

    'section_details' => 'Ayrıntılar',
    'section_details_description' => 'Bu indirimin nasıl tanımlandığı ve uygulanma sırasında nerede yer aldığı.',
    'section_configuration' => 'Yapılandırma',
    'section_configuration_description' => 'Bu indirimin fiyata ne yaptığı.',
    'section_schedule' => 'Zamanlama',
    'section_usage' => 'Kullanım',
    'section_activity' => 'Etkinlik',
    'activity_see_all' => 'Tümünü gör',
    'activity_empty' => 'Henüz bir kayıt yok.',

    'field_name' => 'Ad',
    'field_name_create_hint' => 'Personele gösterilir. Tanıtıcı bundan üretilir ve sonradan değiştirilebilir.',
    'field_handle' => 'Tanıtıcı',
    'field_handle_hint' => 'Bu indirim için benzersiz ve kalıcı bir referans.',
    'field_type' => 'Tür',
    'field_coupon' => 'Kupon kodu',
    'field_coupon_hint' => 'İndirimin otomatik uygulanması için boş bırakın.',
    'field_starts_at' => 'Başlangıç',
    'field_ends_at' => 'Bitiş',
    'field_ends_at_hint' => 'Siz kapatana kadar sürmesi için boş bırakın.',
    'field_priority' => 'Öncelik',
    'field_priority_hint' => 'Düşük değer önce uygulanır. Aynı önceliğe sahip indirimler belirsiz bir sırayla uygulanır.',
    'field_stop' => 'Bu indirimden sonra dur',
    'field_stop_hint' => 'Bu indirim uygulandığında daha düşük öncelikli tüm indirimleri atla.',
    'field_max_uses' => 'En fazla kullanım',
    'field_max_uses_hint' => 'Sınırsız için boş bırakın.',
    'field_max_uses_per_user' => 'Müşteri başına en fazla',
    'field_max_uses_per_user_hint' => 'Sınırsız için boş bırakın.',

    'usage_redeemed' => 'Kullanıldı',

    'raw_data_description' => 'Bu indirim türü için panelde kayıtlı bir form yok, bu yüzden kayıtlı ayarları burada JSON olarak düzenlenir.',
    'raw_data_invalid' => 'Geçerli bir JSON girin.',
    'type_missing' => 'Bu indirim türünü kaydeden paket artık kurulu değil.',

    'bulk_end_now' => 'Şimdi bitir',
    'bulk_delete' => 'Sil',
    'confirm_bulk_end' => 'Seçili indirimler şimdi bitirilsin mi? Hemen uygulanmayı bırakır ama listede kalır.',
    'confirm_bulk_delete' => 'Seçili indirimler silinsin mi? Şu anda kullanan sepetler bunlar olmadan yeniden hesaplanır.',

    'flash_created' => 'İndirim oluşturuldu.',
    'flash_updated' => 'İndirim güncellendi.',
    'flash_deleted' => 'İndirim silindi.',
    'flash_bulk_ended' => '{count} indirim bitirildi.',
    'flash_bulk_deleted' => '{count} indirim silindi.',
];
