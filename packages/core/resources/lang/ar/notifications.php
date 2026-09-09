<?php

return [

    'order_update' => [
        'label' => 'تحديث الطلب',
        'subject' => 'تحديث بخصوص طلبك :reference',
        'greeting' => 'مرحباً،',
        'intro' => 'نود إطلاعك على مستجدات طلبك :reference.',
        'outro' => 'شكراً لتسوقك معنا.',
    ],

    'order_confirmation' => [
        'label' => 'تأكيد الطلب',
        'subject' => 'تم استلام طلبك :reference',
        'heading' => 'شكراً لطلبك',
        'intro' => 'لقد استلمنا طلبك :reference الذي قدمته بتاريخ :date. إليك ملخص ما طلبته.',
        'outro' => 'سنعلمك فور شحن طلبك.',
    ],

    'payment_received' => [
        'label' => 'تم استلام الدفعة',
        'subject' => 'تم استلام دفعة الطلب :reference',
        'heading' => 'تم استلام الدفعة',
        'intro' => 'لقد استلمنا دفعتك للطلب :reference.',
        'outro' => 'شكراً لك. سنتواصل معك عندما يكون طلبك في طريقه إليك.',
    ],

    'order_shipped' => [
        'label' => 'تم شحن الطلب',
        'subject' => 'طلبك :reference في طريقه إليك',
        'heading' => 'طلبك في طريقه إليك',
        'intro' => 'أخبار سارة: تم شحن العناصر التالية من طلبك :reference.',
        'tracking' => 'يمكنك متابعة شحنتك باستخدام بيانات التتبع أدناه.',
        'outro' => 'شكراً لتسوقك معنا.',
    ],

    'order_ready_for_collection' => [
        'label' => 'جاهز للاستلام',
        'subject' => 'طلبك :reference جاهز للاستلام',
        'heading' => 'طلبك جاهز للاستلام',
        'intro' => 'العناصر التالية من طلبك :reference جاهزة لاستلامها.',
        'outro' => 'يرجى إحضار رقم طلبك عند الاستلام.',
    ],

    'order_provisioned' => [
        'label' => 'تم توفير الطلب',
        'subject' => 'طلبك :reference جاهز',
        'heading' => 'عناصرك الرقمية جاهزة',
        'intro' => 'العناصر التالية من طلبك :reference متاحة الآن.',
        'outro' => 'شكراً لتسوقك معنا.',
    ],

    'return_received' => [
        'label' => 'تم استلام المرتجع',
        'subject' => 'استلمنا مرتجعك للطلب :reference',
        'heading' => 'تم استلام المرتجع',
        'intro' => 'لقد استلمنا العناصر التالية المرتجعة من طلبك :reference.',
        'outro' => 'إذا كان هناك مبلغ مستحق للاسترداد، فسنؤكده في رسالة بريد إلكتروني منفصلة.',
    ],

    'order_cancelled' => [
        'label' => 'تم إلغاء الطلب',
        'subject' => 'تم إلغاء طلبك :reference',
        'heading' => 'تم إلغاء طلبك',
        'intro' => 'تم إلغاء طلبك :reference.',
        'outro' => 'إذا كانت لديك أي أسئلة، يرجى الرد على هذه الرسالة.',
    ],

    'refund_issued' => [
        'label' => 'تم استرداد المبلغ',
        'subject' => 'تم استرداد مبلغ للطلب :reference',
        'heading' => 'تم استرداد المبلغ',
        'intro' => 'لقد قمنا باسترداد مبلغ لطلبك :reference.',
        'outro' => 'حسب مزود خدمة الدفع لديك، قد يستغرق ظهور المبلغ المسترد بضعة أيام.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'تحديث الشحن الجزئي',
        'subject' => 'تحديث بخصوص باقي طلبك :reference',
        'heading' => 'تحديث بخصوص طلبك',
        'intro' => 'تم شحن جزء من طلبك :reference. ولا تزال العناصر المتبقية قيد التجهيز.',
        'outstanding' => 'العناصر التالية لم تُشحن بعد:',
        'outro' => 'نعتذر عن التأخير وسنعلمك فور شحنها.',
    ],

    'partials' => [
        'greeting' => 'مرحباً :name،',
        'greeting_fallback' => 'مرحباً،',
        'signoff' => 'مع أطيب التحيات،',
        'item' => 'العنصر',
        'quantity' => 'الكمية',
        'total' => 'الإجمالي',
        'sub_total' => 'المجموع الفرعي',
        'discount' => 'الخصم',
        'shipping' => 'الشحن',
        'tax' => 'الضريبة',
        'order_total' => 'إجمالي الطلب',
        'carrier' => 'شركة الشحن',
        'tracking_number' => 'رقم التتبع',
        'track' => 'تتبع شحنتك',
        'shipping_address' => 'عنوان الشحن',
        'billing_address' => 'عنوان الفوترة',
        'payment_status' => 'حالة الدفع',
        'collect_from' => 'الاستلام من',
        'reason' => 'السبب',
        'refund_amount' => 'المبلغ المسترد',
    ],

];
