<?php

return [

    'order_update' => [
        'label' => 'Sipariş Güncellemesi',
        'subject' => ':reference numaralı siparişiniz hakkında güncelleme',
        'greeting' => 'Merhaba,',
        'intro' => ':reference numaralı siparişinizin durumu hakkında sizi bilgilendirmek istiyoruz.',
        'outro' => 'Bizden alışveriş yaptığınız için teşekkür ederiz.',
    ],

    'order_confirmation' => [
        'label' => 'Sipariş Onayı',
        'subject' => ':reference numaralı siparişiniz alındı',
        'heading' => 'Siparişiniz için teşekkürler',
        'intro' => ':date tarihinde verdiğiniz :reference numaralı siparişinizi aldık. Sipariş özetiniz aşağıdadır.',
        'outro' => 'Siparişiniz yola çıkar çıkmaz sizi bilgilendireceğiz.',
    ],

    'payment_received' => [
        'label' => 'Ödeme Alındı',
        'subject' => ':reference numaralı sipariş için ödeme alındı',
        'heading' => 'Ödeme alındı',
        'intro' => ':reference numaralı siparişinizin ödemesini aldık.',
        'outro' => 'Teşekkür ederiz. Siparişiniz yola çıktığında sizinle iletişime geçeceğiz.',
    ],

    'order_shipped' => [
        'label' => 'Sipariş Gönderildi',
        'subject' => ':reference numaralı siparişiniz yolda',
        'heading' => 'Siparişiniz yolda',
        'intro' => 'Müjde: :reference numaralı siparişinizdeki aşağıdaki ürünler gönderildi.',
        'tracking' => 'Teslimatınızı aşağıdaki takip bilgileriyle izleyebilirsiniz.',
        'outro' => 'Bizden alışveriş yaptığınız için teşekkür ederiz.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Teslim Almaya Hazır',
        'subject' => ':reference numaralı siparişiniz teslim almaya hazır',
        'heading' => 'Siparişiniz teslim almaya hazır',
        'intro' => ':reference numaralı siparişinizdeki aşağıdaki ürünler teslim almanız için hazır.',
        'outro' => 'Lütfen teslim alırken sipariş numaranızı yanınızda bulundurun.',
    ],

    'order_provisioned' => [
        'label' => 'Sipariş Sağlandı',
        'subject' => ':reference numaralı siparişiniz hazır',
        'heading' => 'Dijital ürünleriniz hazır',
        'intro' => ':reference numaralı siparişinizdeki aşağıdaki ürünler artık kullanımınıza açık.',
        'outro' => 'Bizden alışveriş yaptığınız için teşekkür ederiz.',
    ],

    'return_received' => [
        'label' => 'İade Teslim Alındı',
        'subject' => ':reference numaralı siparişinizin iadesini aldık',
        'heading' => 'İade teslim alındı',
        'intro' => ':reference numaralı siparişinizdeki aşağıdaki ürünleri iade olarak teslim aldık.',
        'outro' => 'Bir geri ödeme söz konusuysa bunu ayrı bir e-postayla onaylayacağız.',
    ],

    'order_cancelled' => [
        'label' => 'Sipariş İptal Edildi',
        'subject' => ':reference numaralı siparişiniz iptal edildi',
        'heading' => 'Siparişiniz iptal edildi',
        'intro' => ':reference numaralı siparişiniz iptal edildi.',
        'outro' => 'Sorularınız için bu e-postayı yanıtlamanız yeterlidir.',
    ],

    'refund_issued' => [
        'label' => 'Geri Ödeme Yapıldı',
        'subject' => ':reference numaralı sipariş için geri ödeme yapıldı',
        'heading' => 'Geri ödeme yapıldı',
        'intro' => ':reference numaralı siparişiniz için geri ödeme yaptık.',
        'outro' => 'Ödeme sağlayıcınıza bağlı olarak geri ödemenin görünmesi birkaç gün sürebilir.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Kısmi Gönderim Bildirimi',
        'subject' => ':reference numaralı siparişinizin kalanı hakkında güncelleme',
        'heading' => 'Siparişiniz hakkında güncelleme',
        'intro' => ':reference numaralı siparişinizin bir kısmı gönderildi. Kalan ürünler hâlâ hazırlanıyor.',
        'outstanding' => 'Aşağıdaki ürünler daha sonra gönderilecek:',
        'outro' => 'Gecikme için özür dileriz. Ürünler yola çıkar çıkmaz sizi bilgilendireceğiz.',
    ],

    'partials' => [
        'greeting' => 'Merhaba :name,',
        'greeting_fallback' => 'Merhaba,',
        'signoff' => 'Saygılarımızla,',
        'item' => 'Ürün',
        'quantity' => 'Adet',
        'total' => 'Toplam',
        'sub_total' => 'Ara Toplam',
        'discount' => 'İndirim',
        'shipping' => 'Kargo',
        'tax' => 'Vergi',
        'order_total' => 'Sipariş Toplamı',
        'carrier' => 'Kargo Firması',
        'tracking_number' => 'Takip Numarası',
        'track' => 'Kargonuzu takip edin',
        'shipping_address' => 'Teslimat Adresi',
        'billing_address' => 'Fatura Adresi',
        'payment_status' => 'Ödeme Durumu',
        'collect_from' => 'Teslim Alma Noktası',
        'reason' => 'Sebep',
        'refund_amount' => 'Geri Ödeme Tutarı',
    ],

];
