<?php

return [

    'order_update' => [
        'label' => 'Actualización del pedido',
        'subject' => 'Novedades sobre tu pedido :reference',
        'greeting' => 'Hola,',
        'intro' => 'Queremos informarte sobre el estado de tu pedido :reference.',
        'outro' => 'Gracias por tu compra.',
    ],

    'order_confirmation' => [
        'label' => 'Confirmación del pedido',
        'subject' => 'Hemos recibido tu pedido :reference',
        'heading' => 'Gracias por tu pedido',
        'intro' => 'Hemos recibido tu pedido :reference, realizado el :date. Aquí tienes un resumen de lo que has pedido.',
        'outro' => 'Te avisaremos en cuanto tu pedido esté en camino.',
    ],

    'payment_received' => [
        'label' => 'Pago recibido',
        'subject' => 'Pago recibido del pedido :reference',
        'heading' => 'Pago recibido',
        'intro' => 'Hemos recibido tu pago del pedido :reference.',
        'outro' => 'Gracias. Nos pondremos en contacto contigo cuando tu pedido esté en camino.',
    ],

    'order_shipped' => [
        'label' => 'Pedido enviado',
        'subject' => 'Tu pedido :reference está en camino',
        'heading' => 'Tu pedido está en camino',
        'intro' => 'Buenas noticias: los siguientes artículos de tu pedido :reference se han enviado.',
        'tracking' => 'Puedes seguir tu envío con los datos de seguimiento que aparecen a continuación.',
        'outro' => 'Gracias por tu compra.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Listo para recoger',
        'subject' => 'Tu pedido :reference está listo para recoger',
        'heading' => 'Tu pedido está listo para recoger',
        'intro' => 'Los siguientes artículos de tu pedido :reference ya están listos para que los recojas.',
        'outro' => 'Recuerda llevar tu número de pedido cuando vengas a recogerlo.',
    ],

    'order_provisioned' => [
        'label' => 'Pedido aprovisionado',
        'subject' => 'Tu pedido :reference está listo',
        'heading' => 'Tus artículos digitales están listos',
        'intro' => 'Los siguientes artículos de tu pedido :reference ya están disponibles.',
        'outro' => 'Gracias por tu compra.',
    ],

    'return_received' => [
        'label' => 'Devolución recibida',
        'subject' => 'Hemos recibido tu devolución del pedido :reference',
        'heading' => 'Devolución recibida',
        'intro' => 'Hemos recibido de vuelta los siguientes artículos de tu pedido :reference.',
        'outro' => 'Si corresponde un reembolso, te lo confirmaremos en un correo aparte.',
    ],

    'order_cancelled' => [
        'label' => 'Pedido cancelado',
        'subject' => 'Tu pedido :reference se ha cancelado',
        'heading' => 'Tu pedido se ha cancelado',
        'intro' => 'Tu pedido :reference se ha cancelado.',
        'outro' => 'Si tienes alguna duda, responde a este correo.',
    ],

    'refund_issued' => [
        'label' => 'Reembolso emitido',
        'subject' => 'Se ha emitido un reembolso del pedido :reference',
        'heading' => 'Reembolso emitido',
        'intro' => 'Hemos emitido un reembolso de tu pedido :reference.',
        'outro' => 'Según tu proveedor de pago, el reembolso puede tardar unos días en aparecer.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Envío parcial',
        'subject' => 'Novedades sobre el resto de tu pedido :reference',
        'heading' => 'Novedades sobre tu pedido',
        'intro' => 'Parte de tu pedido :reference ya se ha enviado. Los artículos restantes todavía se están preparando.',
        'outstanding' => 'Los siguientes artículos están pendientes de envío:',
        'outro' => 'Sentimos el retraso y te avisaremos en cuanto estén en camino.',
    ],

    'partials' => [
        'greeting' => 'Hola :name,',
        'greeting_fallback' => 'Hola,',
        'signoff' => 'Un saludo,',
        'item' => 'Artículo',
        'quantity' => 'Cantidad',
        'total' => 'Total',
        'sub_total' => 'Subtotal',
        'discount' => 'Descuento',
        'shipping' => 'Envío',
        'tax' => 'Impuestos',
        'order_total' => 'Total del pedido',
        'carrier' => 'Transportista',
        'tracking_number' => 'Número de seguimiento',
        'track' => 'Seguir tu paquete',
        'shipping_address' => 'Dirección de envío',
        'billing_address' => 'Dirección de facturación',
        'payment_status' => 'Estado del pago',
        'collect_from' => 'Recoger en',
        'reason' => 'Motivo',
        'refund_amount' => 'Importe reembolsado',
    ],

];
