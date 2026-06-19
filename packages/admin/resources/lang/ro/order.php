<?php

return [
    'label' => 'Comandă',
    'plural_label' => 'Comenzi',
    'breadcrumb' => [
        'manage' => 'Gestionează',
    ],
    'transactions' => [
        'capture' => 'Capturat',
        'intent' => 'Intenție',
        'refund' => 'Rambursat',
        'failed' => 'Eșuat',
    ],
    'table' => [
        'status' => [
            'label' => 'Stare',
        ],
        'reference' => [
            'label' => 'Referință',
        ],
        'customer_reference' => [
            'label' => 'Referință client',
        ],
        'customer' => [
            'label' => 'Client',
        ],
        'tags' => [
            'label' => 'Etichete',
        ],
        'postcode' => [
            'label' => 'Cod poștal',
        ],
        'email' => [
            'label' => 'E-mail',
            'copy_message' => 'Adresă e-mail copiată',
        ],
        'phone' => [
            'label' => 'Telefon',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'date' => [
            'label' => 'Data',
        ],
        'new_customer' => [
            'label' => 'Tip client',
        ],
        'placed_after' => [
            'label' => 'Plasată după',
        ],
        'placed_before' => [
            'label' => 'Plasată înainte de',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Prenume',
            ],
            'last_name' => [
                'label' => 'Nume',
            ],
            'line_one' => [
                'label' => 'Adresă linia 1',
            ],
            'line_two' => [
                'label' => 'Adresă linia 2',
            ],
            'line_three' => [
                'label' => 'Adresă linia 3',
            ],
            'company_name' => [
                'label' => 'Nume companie',
            ],
            'tax_identifier' => [
                'label' => 'Cod fiscal',
            ],
            'contact_phone' => [
                'label' => 'Telefon',
            ],
            'contact_email' => [
                'label' => 'Adresă e-mail',
            ],
            'city' => [
                'label' => 'Oraș',
            ],
            'state' => [
                'label' => 'Județ / Provincie',
            ],
            'postcode' => [
                'label' => 'Cod poștal',
            ],
            'country_id' => [
                'label' => 'Țară',
            ],
        ],
        'reference' => [
            'label' => 'Referință',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Tranzacție',
        ],
        'amount' => [
            'label' => 'Sumă',
            'hint' => [
                'less_than_total' => 'Urmează să capturezi o sumă mai mică decât valoarea totală a tranzacției',
            ],
        ],
        'notes' => [
            'label' => 'Note',
        ],
        'confirm' => [
            'label' => 'Confirmă',
            'alert' => 'Este necesară confirmarea',
            'hint' => [
                'capture' => 'Confirmă că dorești să capturezi această plată',
                'refund' => 'Confirmă că dorești să rambursezi această sumă.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Note',
            'placeholder' => 'Nu există note pentru această comandă',
        ],
        'delivery_instructions' => [
            'label' => 'Instrucțiuni de livrare',
        ],
        'shipping_total' => [
            'label' => 'Total livrare',
        ],
        'paid' => [
            'label' => 'Plătit',
        ],
        'refund' => [
            'label' => 'Rambursare',
        ],
        'unit_price' => [
            'label' => 'Preț unitar',
        ],
        'quantity' => [
            'label' => 'Cantitate',
        ],
        'sub_total' => [
            'label' => 'Subtotal',
        ],
        'discount_total' => [
            'label' => 'Total reducere',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Stoc curent: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'la momentul comenzii: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'Referință',
        ],
        'customer_reference' => [
            'label' => 'Referință client',
        ],
        'channel' => [
            'label' => 'Canal',
        ],
        'date_created' => [
            'label' => 'Data creării',
        ],
        'date_placed' => [
            'label' => 'Data plasării',
        ],
        'new_returning' => [
            'label' => 'Nou / Revenit',
        ],
        'new_customer' => [
            'label' => 'Client nou',
        ],
        'returning_customer' => [
            'label' => 'Client revenit',
        ],
        'shipping_address' => [
            'label' => 'Adresă de livrare',
        ],
        'billing_address' => [
            'label' => 'Adresă de facturare',
        ],
        'address_not_set' => [
            'label' => 'Nicio adresă setată',
        ],
        'billing_matches_shipping' => [
            'label' => 'La fel ca adresa de livrare',
        ],
        'additional_info' => [
            'label' => 'Informații suplimentare',
        ],
        'no_additional_info' => [
            'label' => 'Nu există informații suplimentare',
        ],
        'tags' => [
            'label' => 'Etichete',
        ],
        'timeline' => [
            'label' => 'Cronologie',
        ],
        'transactions' => [
            'label' => 'Tranzacții',
            'placeholder' => 'Nicio tranzacție',
        ],
        'alert' => [
            'requires_capture' => 'Această comandă necesită în continuare capturarea plății.',
            'partially_refunded' => 'Această comandă a fost rambursată parțial.',
            'refunded' => 'Această comandă a fost rambursată.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'Actualizează starea',
            'notification' => 'Starea comenzilor a fost actualizată',
        ],
        'update_status' => [
            'label' => 'Update Status',
            'notification' => 'Order status updated',
            'new_status' => [
                'label' => 'Stare nouă',
            ],
            'additional_content' => [
                'label' => 'Conținut suplimentar',
            ],
            'additional_email_recipient' => [
                'label' => 'Destinatar e-mail suplimentar',
                'placeholder' => 'opțional',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Descarcă PDF',
            'notification' => 'Descărcarea PDF-ului comenzii',
        ],
        'edit_address' => [
            'label' => 'Editează',
            'notification' => [
                'error' => 'Eroare',
                'billing_address' => [
                    'saved' => 'Adresa de facturare a fost salvată',
                ],
                'shipping_address' => [
                    'saved' => 'Adresa de livrare a fost salvată',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Editează',
            'form' => [
                'tags' => [
                    'label' => 'Etichete',
                    'helper_text' => 'Separați etichetele apăsând Enter, Tab sau virgulă (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Capturează plata',
            'notification' => [
                'error' => 'A apărut o problemă la capturare',
                'success' => 'Capturare reușită',
            ],
        ],
        'refund_payment' => [
            'label' => 'Rambursare',
            'notification' => [
                'error' => 'A apărut o problemă la rambursare',
                'success' => 'Rambursare reușită',
            ],
        ],
    ],

    'fulfilments' => [
        'heading' => 'Fulfilments',
        'unreferenced' => 'Fulfilment #:id',
        'on_hold' => 'On hold',
        'empty' => 'No fulfilments yet.',
        'columns' => [
            'reference' => 'Reference',
            'state' => 'State',
            'items' => 'Items',
            'tracking' => 'Tracking',
            'shipped_at' => 'Shipped at',
            'handed_over' => [
                'shipping' => 'Shipped at',
                'collection' => 'Collected at',
                'digital' => 'Provisioned at',
            ],
            'handed_over_default' => 'Fulfilled at',
        ],
        'actions' => [
            'more' => 'More actions',
            'notify' => 'Notify customer',
            'add_tracking' => [
                'label' => 'Add tracking',
                'modal_heading' => 'Add tracking',
                'notification' => [
                    'success' => 'Tracking added.',
                    'error' => 'Could not add tracking.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Remove tracking',
                'notification' => [
                    'success' => 'Tracking removed.',
                    'error' => 'Could not remove tracking.',
                ],
            ],
            'create' => [
                'label' => 'Create fulfilment',
                'modal_heading' => 'Create fulfilment',
                'empty' => 'Every line is already fulfilled.',
                'notification' => [
                    'success' => 'Fulfilment created.',
                    'error' => 'Could not create fulfilment.',
                ],
            ],
            'ship' => [
                'label' => 'Mark shipped',
                'modal_heading' => 'Mark fulfilment as shipped',
                'notification' => [
                    'success' => 'Fulfilment marked as shipped.',
                    'error' => 'Could not ship fulfilment.',
                ],
            ],
            'fulfil' => [
                'label' => 'Mark fulfilled',
                'modal_heading' => 'Mark fulfilment as fulfilled',
                'labels' => [
                    'collection' => 'Mark collected',
                ],
                'notification' => [
                    'success' => 'Fulfilment marked as fulfilled.',
                    'error' => 'Could not fulfil fulfilment.',
                ],
            ],
            'cancel' => [
                'label' => 'Cancel fulfilment',
                'modal_heading' => 'Cancel fulfilment',
                'description' => 'This returns the fulfilment to pending so it can be progressed again. Any shipment details are cleared.',
                'notification' => [
                    'success' => 'Fulfilment cancelled.',
                    'error' => 'Could not cancel fulfilment.',
                ],
            ],
            'change_location' => [
                'label' => 'Change location',
                'modal_heading' => 'Change fulfilment location',
                'field' => 'Location',
                'notification' => [
                    'success' => 'Fulfilment location updated.',
                    'error' => 'Could not change the fulfilment location.',
                ],
            ],
            'return' => [
                'label' => 'Return',
                'notification' => [
                    'success' => 'Fulfilment returned.',
                    'error' => 'Could not return fulfilment.',
                ],
            ],
            'update_status' => [
                'label' => 'Update status',
            ],
            'transition' => [
                'modal_heading' => 'Mark fulfilment as :status?',
                'notification' => [
                    'success' => 'Fulfilment status updated.',
                    'error' => 'Could not update the fulfilment status.',
                ],
            ],
            'undo_return' => [
                'label' => 'Undo return',
                'notification' => [
                    'success' => 'Return undone.',
                    'error' => 'Could not undo the return.',
                ],
            ],
            'hold' => [
                'label' => 'Hold fulfilment',
                'modal_heading' => 'Hold fulfilment',
                'reason' => 'Reason',
                'note' => 'Note',
                'notification' => [
                    'success' => 'Fulfilment placed on hold.',
                    'error' => 'Could not hold the fulfilment.',
                ],
            ],
            'release' => [
                'label' => 'Release hold',
                'notification' => [
                    'success' => 'Fulfilment released.',
                    'error' => 'Could not release the fulfilment.',
                ],
            ],
            'split' => [
                'label' => 'Split',
                'confirm' => 'Split fulfilment',
                'cancel' => 'Cancel',
                'empty' => 'Select a quantity to split out.',
                'modal_heading' => 'Split fulfilment',
                'notification' => [
                    'success' => 'Fulfilment split.',
                    'error' => 'Could not split fulfilment.',
                ],
            ],
            'merge' => [
                'label' => 'Merge',
                'confirm' => 'Merge fulfilment',
                'cancel' => 'Cancel',
                'modal_heading' => 'Merge fulfilment',
                'description' => 'Select the items you would like to merge.',
                'target' => 'Merge with',
                'empty' => 'Select items and a destination to merge.',
                'notification' => [
                    'success' => 'Fulfilments merged.',
                    'error' => 'Could not merge fulfilments.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantity',
            'tracking' => 'Tracking',
            'tracking_item' => 'Tracking #:number',
            'unit_price' => 'Unit Price',
            'sub_total' => 'Sub Total',
            'discount_total' => 'Discount Total',
            'total' => 'Total',
            'stock_level' => 'Current Stock Level: :count',
            'of' => 'of :count',
            'outstanding' => 'Outstanding: :count',
            'tracking_number' => 'Tracking number',
            'tracking_url' => 'Tracking URL',
            'carrier' => 'Carrier',
            'carrier_custom' => 'Custom / other',
            'tracking_url_help' => 'Only needed for carriers without an automatic tracking link.',
            'shipping_method' => 'Shipping method',
            'move_quantity' => 'Quantity to move out',
        ],
    ],

    'other_items' => [
        'heading' => 'Other items',
    ],
];
