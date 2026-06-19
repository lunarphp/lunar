<?php

return [
    'shared' => [
        'confirm' => [
            'label' => 'Confirm',
            'error' => 'You must confirm before this action can proceed.',
        ],
    ],
    'collections' => [
        'create_root' => [
            'label' => 'Create Root Collection',
            'notification' => [
                'success' => 'Collection created.',
            ],
        ],
        'create_child' => [
            'label' => 'Create Child Collection',
            'notification' => [
                'success' => 'Collection created.',
            ],
        ],
        'move' => [
            'label' => 'Move Collection',
            'notification' => [
                'success' => 'Collection moved.',
            ],
        ],
        'delete' => [
            'label' => 'Delete',
            'notification' => [
                'success' => 'Collection deleted.',
            ],
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Cannot Delete',
                    'body' => 'This collection has child collections and cannot be deleted.',
                ],
            ],
        ],
    ],
    'orders' => [
        'close' => [
            'label' => 'Захиалга хаах',
            'confirm' => 'Хаах нь захиалга бүрэн шийдвэрлэгдсэний дараа архивлана. Та дараа нь дахин нээх боломжтой.',
            'notification' => [
                'success' => 'Захиалга хаагдлаа.',
            ],
        ],
        'reopen' => [
            'label' => 'Захиалга дахин нээх',
            'confirm' => 'Дахин нээх нь захиалгыг таны нээлттэй ажлын жагсаалт руу буцаана.',
            'notification' => [
                'success' => 'Захиалга дахин нээгдлээ.',
            ],
        ],
        'shared' => [
            'transaction' => [
                'label' => 'Transaction',
            ],
            'amount' => [
                'label' => 'Amount',
                'hint' => [
                    'less_than_total' => 'The amount is less than the transaction total.',
                ],
            ],
            'notes' => [
                'label' => 'Notes',
            ],
        ],
        'refund' => [
            'label' => 'Refund',
            'modal_heading' => 'Refund payment',
            'confirm' => [
                'helper_text' => 'I confirm I want to refund this amount.',
            ],
            'notification' => [
                'success' => 'Refund issued.',
                'error' => 'Refund failed.',
            ],
        ],
        'capture' => [
            'label' => 'Capture',
            'modal_heading' => 'Capture payment',
            'confirm' => [
                'helper_text' => 'I confirm I want to capture this amount.',
            ],
            'notification' => [
                'success' => 'Capture successful.',
                'error' => 'Capture failed.',
            ],
        ],
        'place_on_hold' => [
            'label' => 'Place on Hold',
            'confirm' => 'Placing the order on hold pauses it in your workflow until you resume it.',
            'notification' => [
                'success' => 'Order placed on hold.',
            ],
        ],
        'cancel_order' => [
            'label' => 'Cancel order',
            'modal_heading' => 'Захиалга цуцлах',
            'reason' => 'Цуцлах шалтгаан',
            'note' => 'Ажилтны тэмдэглэл',
            'note_help' => 'Энэ тэмдэглэлийг зөвхөн та болон бусад ажилтнууд харах боломжтой.',
            'notify' => 'Худалдан авагчид мэдэгдэл илгээх',
            'notification' => [
                'success' => 'Order cancelled.',
                'error' => 'Захиалгыг цуцалж чадсангүй.',
            ],
        ],
        'resume_order' => [
            'label' => 'Resume Order',
            'confirm' => 'Resuming the order moves it back into the active workflow at awaiting payment.',
            'notification' => [
                'success' => 'Order resumed.',
            ],
        ],
        'mark_as_complete' => [
            'label' => 'Дууссан гэж тэмдэглэх',
            'notification' => [
                'success' => 'Захиалгыг дууссан гэж тэмдэглэв.',
            ],
        ],
        'mark_as_shipped' => [
            'label' => 'Mark as Shipped',
            'bulk_label' => 'Mark selected as Shipped',
            'notification' => [
                'success' => 'Order marked as shipped.',
                'bulk_success' => 'Selected orders marked as shipped.',
            ],
        ],
        'add_note' => [
            'label' => 'Add Note',
            'modal_heading' => 'Add a note',
            'fields' => [
                'note' => [
                    'label' => 'Note',
                ],
            ],
            'notification' => [
                'success' => 'Note added.',
            ],
        ],
        'download_pdf' => [
            'label' => 'Download PDF',
        ],
    ],
    'products' => [
        'duplicate' => [
            'label' => 'Duplicate',
            'notification' => [
                'success' => 'Product duplicated.',
            ],
        ],
        'publish' => [
            'label' => 'Publish',
            'bulk_label' => 'Publish selected',
            'notification' => [
                'success' => 'Products published.',
            ],
        ],
        'unpublish' => [
            'label' => 'Unpublish',
            'bulk_label' => 'Unpublish selected',
            'confirm' => [
                'helper_text' => 'Unpublished products will no longer be visible to customers.',
            ],
            'notification' => [
                'success' => 'Products unpublished.',
            ],
        ],
        'archive' => [
            'label' => 'Archive',
            'bulk_label' => 'Archive selected',
            'confirm' => [
                'helper_text' => 'Archived products are hidden from the storefront and admin lists.',
            ],
            'notification' => [
                'success' => 'Products archived.',
            ],
        ],
        'adjust_stock' => [
            'label' => 'Adjust Stock',
            'modal_heading' => 'Adjust stock level',
            'fields' => [
                'delta' => [
                    'label' => 'Adjustment',
                    'helper_text' => 'Use a positive number to add stock, negative to remove.',
                ],
                'reason' => [
                    'label' => 'Reason',
                ],
            ],
            'notification' => [
                'success' => 'Stock adjusted.',
            ],
        ],
        'map_variants_to_options' => [
            'label' => 'Map Variants to Product Options',
            'notification' => [
                'success' => 'Variants mapped to product options.',
            ],
        ],
    ],
];
