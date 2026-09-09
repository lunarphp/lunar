<?php

return [

    'order_update' => [
        'label' => 'Mise à jour de commande',
        'subject' => 'Des nouvelles de votre commande :reference',
        'greeting' => 'Bonjour,',
        'intro' => 'Nous souhaitons vous informer de l\'avancement de votre commande :reference.',
        'outro' => 'Merci pour votre achat.',
    ],

    'order_confirmation' => [
        'label' => 'Confirmation de commande',
        'subject' => 'Votre commande :reference a bien été reçue',
        'heading' => 'Merci pour votre commande',
        'intro' => 'Nous avons bien reçu votre commande :reference, passée le :date. Voici le récapitulatif de votre commande.',
        'outro' => 'Nous vous préviendrons dès que votre commande sera en route.',
    ],

    'payment_received' => [
        'label' => 'Paiement reçu',
        'subject' => 'Paiement reçu pour la commande :reference',
        'heading' => 'Paiement reçu',
        'intro' => 'Nous avons bien reçu votre paiement pour la commande :reference.',
        'outro' => 'Merci. Nous vous recontacterons dès que votre commande sera en route.',
    ],

    'order_shipped' => [
        'label' => 'Commande expédiée',
        'subject' => 'Votre commande :reference est en route',
        'heading' => 'Votre commande est en route',
        'intro' => 'Bonne nouvelle : les articles suivants de votre commande :reference ont été expédiés.',
        'tracking' => 'Vous pouvez suivre votre livraison grâce aux informations de suivi ci-dessous.',
        'outro' => 'Merci pour votre achat.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Prêt pour le retrait',
        'subject' => 'Votre commande :reference est prête à être retirée',
        'heading' => 'Votre commande est prête à être retirée',
        'intro' => 'Les articles suivants de votre commande :reference sont prêts à être retirés.',
        'outro' => 'Merci de vous munir de votre numéro de commande lors du retrait.',
    ],

    'order_provisioned' => [
        'label' => 'Commande mise à disposition',
        'subject' => 'Votre commande :reference est prête',
        'heading' => 'Vos articles numériques sont prêts',
        'intro' => 'Les articles suivants de votre commande :reference sont maintenant disponibles.',
        'outro' => 'Merci pour votre achat.',
    ],

    'return_received' => [
        'label' => 'Retour reçu',
        'subject' => 'Nous avons reçu votre retour pour la commande :reference',
        'heading' => 'Retour reçu',
        'intro' => 'Nous avons bien reçu en retour les articles suivants de votre commande :reference.',
        'outro' => 'Si un remboursement est dû, nous vous le confirmerons dans un e-mail séparé.',
    ],

    'order_cancelled' => [
        'label' => 'Commande annulée',
        'subject' => 'Votre commande :reference a été annulée',
        'heading' => 'Votre commande a été annulée',
        'intro' => 'Votre commande :reference a été annulée.',
        'outro' => 'Pour toute question, répondez simplement à cet e-mail.',
    ],

    'refund_issued' => [
        'label' => 'Remboursement effectué',
        'subject' => 'Un remboursement a été effectué pour la commande :reference',
        'heading' => 'Remboursement effectué',
        'intro' => 'Nous avons effectué un remboursement pour votre commande :reference.',
        'outro' => 'Selon votre prestataire de paiement, le remboursement peut mettre quelques jours à apparaître.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Expédition partielle',
        'subject' => 'Des nouvelles du reste de votre commande :reference',
        'heading' => 'Des nouvelles de votre commande',
        'intro' => 'Une partie de votre commande :reference a été expédiée. Les articles restants sont encore en préparation.',
        'outstanding' => 'Les articles suivants restent à venir :',
        'outro' => 'Nous vous prions de nous excuser pour ce délai et vous préviendrons dès qu\'ils seront en route.',
    ],

    'partials' => [
        'greeting' => 'Bonjour :name,',
        'greeting_fallback' => 'Bonjour,',
        'signoff' => 'Cordialement,',
        'item' => 'Article',
        'quantity' => 'Quantité',
        'total' => 'Total',
        'sub_total' => 'Sous-total',
        'discount' => 'Remise',
        'shipping' => 'Livraison',
        'tax' => 'Taxes',
        'order_total' => 'Total de la commande',
        'carrier' => 'Transporteur',
        'tracking_number' => 'Numéro de suivi',
        'track' => 'Suivre votre colis',
        'shipping_address' => 'Adresse de livraison',
        'billing_address' => 'Adresse de facturation',
        'payment_status' => 'Statut du paiement',
        'collect_from' => 'À retirer chez',
        'reason' => 'Motif',
        'refund_amount' => 'Montant remboursé',
    ],

];
