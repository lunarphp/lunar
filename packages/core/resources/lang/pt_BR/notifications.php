<?php

return [

    'order_update' => [
        'label' => 'Atualização do pedido',
        'subject' => 'Novidades sobre o seu pedido :reference',
        'greeting' => 'Olá,',
        'intro' => 'Queremos informar você sobre o andamento do seu pedido :reference.',
        'outro' => 'Obrigado por comprar conosco.',
    ],

    'order_confirmation' => [
        'label' => 'Confirmação do pedido',
        'subject' => 'Recebemos o seu pedido :reference',
        'heading' => 'Obrigado pelo seu pedido',
        'intro' => 'Recebemos o seu pedido :reference, realizado em :date. Veja abaixo o resumo do que você pediu.',
        'outro' => 'Avisaremos assim que o seu pedido estiver a caminho.',
    ],

    'payment_received' => [
        'label' => 'Pagamento recebido',
        'subject' => 'Pagamento recebido para o pedido :reference',
        'heading' => 'Pagamento recebido',
        'intro' => 'Recebemos o seu pagamento do pedido :reference.',
        'outro' => 'Obrigado. Entraremos em contato quando o seu pedido estiver a caminho.',
    ],

    'order_shipped' => [
        'label' => 'Pedido enviado',
        'subject' => 'O seu pedido :reference está a caminho',
        'heading' => 'O seu pedido está a caminho',
        'intro' => 'Boas notícias: os seguintes itens do seu pedido :reference foram enviados.',
        'tracking' => 'Você pode acompanhar a entrega com os dados de rastreamento abaixo.',
        'outro' => 'Obrigado por comprar conosco.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Pronto para retirada',
        'subject' => 'O seu pedido :reference está pronto para retirada',
        'heading' => 'O seu pedido está pronto para retirada',
        'intro' => 'Os seguintes itens do seu pedido :reference estão prontos para retirada.',
        'outro' => 'Leve o número do pedido com você no momento da retirada.',
    ],

    'order_provisioned' => [
        'label' => 'Pedido disponibilizado',
        'subject' => 'O seu pedido :reference está pronto',
        'heading' => 'Os seus itens digitais estão prontos',
        'intro' => 'Os seguintes itens do seu pedido :reference já estão disponíveis.',
        'outro' => 'Obrigado por comprar conosco.',
    ],

    'return_received' => [
        'label' => 'Devolução recebida',
        'subject' => 'Recebemos a sua devolução do pedido :reference',
        'heading' => 'Devolução recebida',
        'intro' => 'Recebemos de volta os seguintes itens do seu pedido :reference.',
        'outro' => 'Se houver reembolso a fazer, confirmaremos em um e-mail separado.',
    ],

    'order_cancelled' => [
        'label' => 'Pedido cancelado',
        'subject' => 'O seu pedido :reference foi cancelado',
        'heading' => 'O seu pedido foi cancelado',
        'intro' => 'O seu pedido :reference foi cancelado.',
        'outro' => 'Se tiver alguma dúvida, basta responder a este e-mail.',
    ],

    'refund_issued' => [
        'label' => 'Reembolso emitido',
        'subject' => 'Um reembolso foi emitido para o pedido :reference',
        'heading' => 'Reembolso emitido',
        'intro' => 'Emitimos um reembolso para o seu pedido :reference.',
        'outro' => 'Dependendo do seu provedor de pagamento, o reembolso pode levar alguns dias para aparecer.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Envio parcial',
        'subject' => 'Novidades sobre o restante do seu pedido :reference',
        'heading' => 'Novidades sobre o seu pedido',
        'intro' => 'Parte do seu pedido :reference já foi enviada. Os itens restantes ainda estão sendo preparados.',
        'outstanding' => 'Os seguintes itens ainda serão enviados:',
        'outro' => 'Pedimos desculpas pelo atraso e avisaremos assim que eles estiverem a caminho.',
    ],

    'partials' => [
        'greeting' => 'Olá, :name,',
        'greeting_fallback' => 'Olá,',
        'signoff' => 'Atenciosamente,',
        'item' => 'Item',
        'quantity' => 'Quantidade',
        'total' => 'Total',
        'sub_total' => 'Subtotal',
        'discount' => 'Desconto',
        'shipping' => 'Frete',
        'tax' => 'Impostos',
        'order_total' => 'Total do pedido',
        'carrier' => 'Transportadora',
        'tracking_number' => 'Código de rastreamento',
        'track' => 'Rastrear o seu pacote',
        'shipping_address' => 'Endereço de entrega',
        'billing_address' => 'Endereço de cobrança',
        'payment_status' => 'Status do pagamento',
        'collect_from' => 'Retirar em',
        'reason' => 'Motivo',
        'refund_amount' => 'Valor reembolsado',
    ],

];
