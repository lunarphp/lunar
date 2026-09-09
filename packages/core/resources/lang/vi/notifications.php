<?php

return [

    'order_update' => [
        'label' => 'Cập nhật đơn hàng',
        'subject' => 'Cập nhật về đơn hàng :reference của bạn',
        'greeting' => 'Xin chào,',
        'intro' => 'Chúng tôi muốn thông báo cho bạn về tình trạng đơn hàng :reference.',
        'outro' => 'Cảm ơn bạn đã mua sắm cùng chúng tôi.',
    ],

    'order_confirmation' => [
        'label' => 'Xác nhận đơn hàng',
        'subject' => 'Đơn hàng :reference của bạn đã được tiếp nhận',
        'heading' => 'Cảm ơn bạn đã đặt hàng',
        'intro' => 'Chúng tôi đã nhận được đơn hàng :reference của bạn, đặt ngày :date. Dưới đây là tóm tắt những gì bạn đã đặt.',
        'outro' => 'Chúng tôi sẽ thông báo ngay khi đơn hàng của bạn được giao đi.',
    ],

    'payment_received' => [
        'label' => 'Đã nhận thanh toán',
        'subject' => 'Đã nhận thanh toán cho đơn hàng :reference',
        'heading' => 'Đã nhận thanh toán',
        'intro' => 'Chúng tôi đã nhận được thanh toán của bạn cho đơn hàng :reference.',
        'outro' => 'Cảm ơn bạn. Chúng tôi sẽ liên hệ khi đơn hàng của bạn được giao đi.',
    ],

    'order_shipped' => [
        'label' => 'Đơn hàng đã giao đi',
        'subject' => 'Đơn hàng :reference của bạn đang trên đường giao',
        'heading' => 'Đơn hàng của bạn đang trên đường giao',
        'intro' => 'Tin vui: các sản phẩm sau trong đơn hàng :reference của bạn đã được giao đi.',
        'tracking' => 'Bạn có thể theo dõi đơn hàng bằng thông tin vận chuyển bên dưới.',
        'outro' => 'Cảm ơn bạn đã mua sắm cùng chúng tôi.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Sẵn sàng để nhận',
        'subject' => 'Đơn hàng :reference của bạn đã sẵn sàng để nhận',
        'heading' => 'Đơn hàng của bạn đã sẵn sàng để nhận',
        'intro' => 'Các sản phẩm sau trong đơn hàng :reference của bạn đã sẵn sàng để bạn đến nhận.',
        'outro' => 'Vui lòng mang theo mã đơn hàng khi đến nhận.',
    ],

    'order_provisioned' => [
        'label' => 'Đơn hàng đã cung cấp',
        'subject' => 'Đơn hàng :reference của bạn đã sẵn sàng',
        'heading' => 'Sản phẩm kỹ thuật số của bạn đã sẵn sàng',
        'intro' => 'Các sản phẩm sau trong đơn hàng :reference của bạn hiện đã có sẵn.',
        'outro' => 'Cảm ơn bạn đã mua sắm cùng chúng tôi.',
    ],

    'return_received' => [
        'label' => 'Đã nhận hàng trả lại',
        'subject' => 'Chúng tôi đã nhận được hàng trả lại cho đơn hàng :reference',
        'heading' => 'Đã nhận hàng trả lại',
        'intro' => 'Chúng tôi đã nhận lại các sản phẩm sau từ đơn hàng :reference của bạn.',
        'outro' => 'Nếu có khoản hoàn tiền, chúng tôi sẽ xác nhận trong một email riêng.',
    ],

    'order_cancelled' => [
        'label' => 'Đơn hàng đã hủy',
        'subject' => 'Đơn hàng :reference của bạn đã bị hủy',
        'heading' => 'Đơn hàng của bạn đã bị hủy',
        'intro' => 'Đơn hàng :reference của bạn đã bị hủy.',
        'outro' => 'Nếu bạn có câu hỏi, vui lòng trả lời email này.',
    ],

    'refund_issued' => [
        'label' => 'Đã hoàn tiền',
        'subject' => 'Đã hoàn tiền cho đơn hàng :reference',
        'heading' => 'Đã hoàn tiền',
        'intro' => 'Chúng tôi đã hoàn tiền cho đơn hàng :reference của bạn.',
        'outro' => 'Tùy vào nhà cung cấp dịch vụ thanh toán, khoản hoàn tiền có thể mất vài ngày để hiển thị.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Cập nhật giao hàng một phần',
        'subject' => 'Cập nhật về phần còn lại của đơn hàng :reference',
        'heading' => 'Cập nhật về đơn hàng của bạn',
        'intro' => 'Một phần đơn hàng :reference của bạn đã được giao đi. Các sản phẩm còn lại vẫn đang được chuẩn bị.',
        'outstanding' => 'Các sản phẩm sau sẽ được giao sau:',
        'outro' => 'Chúng tôi xin lỗi vì sự chậm trễ và sẽ thông báo ngay khi hàng được giao đi.',
    ],

    'partials' => [
        'greeting' => 'Chào :name,',
        'greeting_fallback' => 'Xin chào,',
        'signoff' => 'Trân trọng,',
        'item' => 'Sản phẩm',
        'quantity' => 'Số lượng',
        'total' => 'Tổng',
        'sub_total' => 'Tạm tính',
        'discount' => 'Giảm giá',
        'shipping' => 'Vận chuyển',
        'tax' => 'Thuế',
        'order_total' => 'Tổng đơn hàng',
        'carrier' => 'Đơn vị vận chuyển',
        'tracking_number' => 'Mã vận đơn',
        'track' => 'Theo dõi kiện hàng',
        'shipping_address' => 'Địa chỉ giao hàng',
        'billing_address' => 'Địa chỉ thanh toán',
        'payment_status' => 'Trạng thái thanh toán',
        'collect_from' => 'Nhận hàng tại',
        'reason' => 'Lý do',
        'refund_amount' => 'Số tiền hoàn',
    ],

];
