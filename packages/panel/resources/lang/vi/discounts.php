<?php

return [
    'title' => 'Giảm giá',
    'description' => 'Thiết lập các chương trình khuyến mãi làm giảm số tiền khách phải trả — theo phần trăm, theo số tiền cố định hoặc ưu đãi mua một tặng một — và kiểm soát thời điểm, nơi áp dụng và đối tượng của từng chương trình.',
    'new_discount' => 'Giảm giá mới',
    'create_title' => 'Giảm giá mới',
    'create_description' => 'Đặt tên cho chương trình giảm giá và chọn cách nó giảm giá bán; mọi thứ còn lại được cấu hình trong trang giảm giá.',
    'create_discount' => 'Tạo giảm giá',
    'back_to_discounts' => 'Quay lại danh sách giảm giá',
    'delete_discount' => 'Xóa giảm giá',
    'confirm_delete_discount' => 'Xóa chương trình giảm giá này? Các giỏ hàng đang dùng nó sẽ được tính lại mà không có nó.',

    'column_status' => 'Trạng thái',
    'column_name' => 'Tên',
    'column_type' => 'Loại',
    'column_coupon' => 'Mã giảm giá',
    'column_window' => 'Thời gian',
    'column_usage' => 'Lượt dùng',
    'column_priority' => 'Độ ưu tiên',

    'search_placeholder' => 'Tìm chương trình giảm giá',
    'filter_status' => 'Trạng thái',
    'filter_all_statuses' => 'Tất cả trạng thái',
    'filter_type' => 'Loại',
    'filter_all_types' => 'Tất cả các loại',
    'filter_channel' => 'Kênh',
    'filter_all_channels' => 'Tất cả các kênh',
    'filter_customer_group' => 'Nhóm khách hàng',
    'filter_all_customer_groups' => 'Tất cả nhóm khách hàng',
    'filter_redemption' => 'Cách áp dụng',
    'filter_all_redemptions' => 'Có mã và tự động',
    'redemption_coupon' => 'Cần mã giảm giá',
    'redemption_automatic' => 'Áp dụng tự động',
    'sort_priority' => 'Theo độ ưu tiên',
    'sort_name' => 'Tên A-Z',
    'sort_starts' => 'Bắt đầu sớm nhất',
    'sort_ends' => 'Kết thúc sớm nhất',
    'sort_uses' => 'Dùng nhiều nhất',
    'count_of' => '{shown} trên {total}',
    'clear_filters' => 'Xóa bộ lọc',
    'empty_title' => 'Không có chương trình giảm giá phù hợp',
    'empty_description' => 'Hãy xóa từ khóa tìm kiếm hoặc bộ lọc, hoặc tạo một chương trình giảm giá mới.',
    'empty_none_title' => 'Chưa có chương trình giảm giá',
    'empty_none_description' => 'Tạo chương trình giảm giá đầu tiên để bắt đầu khuyến mãi.',

    'status_active' => 'Đang chạy',
    'status_scheduled' => 'Đã lên lịch',
    'status_expired' => 'Đã hết hạn',
    'status_pending' => 'Chờ xử lý',

    'kpi_active_label' => 'Đang chạy',
    'kpi_active_hint' => 'Áp dụng hôm nay',
    'kpi_scheduled_label' => 'Đã lên lịch',
    'kpi_scheduled_hint' => 'Bắt đầu sau',
    'kpi_ending_label' => 'Sắp kết thúc',
    'kpi_ending_hint' => 'Trong 7 ngày',
    'kpi_redemptions_label' => 'Lượt sử dụng',
    'kpi_redemptions_hint' => 'Tất cả chương trình, từ trước đến nay',
    'show_kpis' => 'Hiện thống kê',

    'summary_percentage_off' => 'Giảm :percentage%',

    'summary_fixed_amount_off' => 'Giảm :amount',

    'summary_buy_x_get_y' => 'Mua :buy tặng :get',

    'field_percentage' => 'Phần trăm giảm',

    'field_percentage_hint' => 'Trừ trên mỗi dòng đủ điều kiện.',

    'field_amount' => 'Số tiền giảm',

    'field_amounts_hint' => 'Đặt số tiền cho từng loại tiền tệ. Loại tiền để trống sẽ không được giảm.',

    'field_min_qty' => 'Số lượng cần mua',

    'field_reward_qty' => 'Số lượng được tặng',

    'field_max_reward_qty' => 'Tối đa được tặng',

    'field_max_reward_qty_hint' => 'Để trống để thưởng cho mọi bộ đủ điều kiện.',

    'field_automatically_add_rewards' => 'Tự động thêm hàng tặng vào giỏ',

    'field_automatically_add_rewards_hint' => 'Thêm sản phẩm tặng thay cho khách thay vì chờ họ tự thêm.',

    'section_targets' => 'Áp dụng cho',

    'section_targets_description' => 'Giới hạn chương trình này trong một phần danh mục. Để trống một khối nghĩa là áp dụng cho tất cả.',

    'section_customers' => 'Khách hàng đủ điều kiện',

    'bucket_limitation' => 'Áp dụng cho',

    'bucket_limitation_description' => 'Chỉ những mục này được giảm.',

    'bucket_exclusion' => 'Loại trừ',

    'bucket_exclusion_description' => 'Không bao giờ được giảm, kể cả khi khớp ở trên.',

    'bucket_condition' => 'Sản phẩm đủ điều kiện',

    'bucket_condition_description' => 'Khách phải mua gì để nhận quà.',

    'bucket_reward' => 'Sản phẩm tặng',

    'bucket_reward_description' => 'Khách được nhận gì.',

    'bucket_customers' => 'Khách hàng đủ điều kiện',

    'bucket_customers_description' => 'Chỉ những khách này dùng được. Để trống để mọi người đều dùng được.',

    'kind_products' => 'Sản phẩm',

    'kind_variants' => 'Biến thể',

    'kind_collections' => 'Bộ sưu tập',

    'kind_brands' => 'Thương hiệu',

    'kind_customers' => 'Khách hàng',

    'target_add' => 'Thêm',

    'target_remove' => 'Bỏ {label}',

    'target_empty' => 'Chưa chọn gì nên áp dụng cho tất cả.',

    'target_dialog_title' => 'Thêm mục tiêu',

    'target_dialog_description' => 'Tìm trong mọi thứ khối này có thể nhắm tới.',

    'target_search_placeholder' => 'Tìm sản phẩm, bộ sưu tập, thương hiệu',

    'target_no_results' => 'Không có kết quả.',

    'target_add_selected' => 'Thêm {count}',

    'section_conditions' => 'Điều kiện',

    'section_conditions_description' => 'Giỏ hàng phải thỏa điều kiện gì trước khi được giảm.',

    'field_min_spend' => 'Chi tiêu tối thiểu',

    'field_min_spend_hint' => 'Đặt ngưỡng cho từng loại tiền tệ. Loại tiền để trống thì không có mức tối thiểu.',

    'automatic' => 'Tự động',
    'no_end_date' => 'Không có ngày kết thúc',
    'usage_unlimited' => 'không giới hạn',
    'usage_of' => '{used} trên {max}',

    'section_details' => 'Chi tiết',
    'section_details_description' => 'Cách nhận biết chương trình giảm giá này và vị trí của nó trong thứ tự áp dụng.',
    'section_configuration' => 'Cấu hình',
    'section_configuration_description' => 'Chương trình giảm giá này tác động thế nào đến giá.',
    'section_schedule' => 'Lịch chạy',
    'section_usage' => 'Lượt dùng',
    'section_activity' => 'Hoạt động',
    'activity_see_all' => 'Xem tất cả',
    'activity_empty' => 'Chưa ghi nhận hoạt động nào.',

    'field_name' => 'Tên',
    'field_name_create_hint' => 'Hiển thị cho nhân viên. Định danh được tạo từ tên và có thể sửa lại sau.',
    'field_handle' => 'Định danh',
    'field_handle_hint' => 'Tham chiếu duy nhất và cố định cho chương trình giảm giá này.',
    'field_type' => 'Loại',
    'field_coupon' => 'Mã giảm giá',
    'field_coupon_hint' => 'Để trống để chương trình giảm giá tự động áp dụng.',
    'field_starts_at' => 'Bắt đầu',
    'field_ends_at' => 'Kết thúc',
    'field_ends_at_hint' => 'Để trống để chạy cho đến khi bạn tắt đi.',
    'field_priority' => 'Độ ưu tiên',
    'field_priority_hint' => 'Giá trị nhỏ hơn được áp dụng trước. Các chương trình cùng độ ưu tiên áp dụng theo thứ tự không xác định.',
    'field_stop' => 'Dừng sau chương trình này',
    'field_stop_hint' => 'Bỏ qua mọi chương trình giảm giá có độ ưu tiên thấp hơn khi chương trình này được áp dụng.',
    'field_max_uses' => 'Số lượt dùng tối đa',
    'field_max_uses_hint' => 'Để trống nếu không giới hạn.',
    'field_max_uses_per_user' => 'Tối đa mỗi khách hàng',
    'field_max_uses_per_user_hint' => 'Để trống nếu không giới hạn.',

    'usage_redeemed' => 'Đã dùng',

    'raw_data_description' => 'Loại giảm giá này chưa đăng ký biểu mẫu trong bảng quản trị, nên các thiết lập đã lưu được chỉnh sửa ở đây dưới dạng JSON.',
    'raw_data_invalid' => 'Hãy nhập JSON hợp lệ.',
    'type_missing' => 'Gói đã đăng ký loại giảm giá này không còn được cài đặt.',

    'bulk_end_now' => 'Kết thúc ngay',
    'bulk_delete' => 'Xóa',
    'confirm_bulk_end' => 'Kết thúc ngay các chương trình đã chọn? Chúng ngừng áp dụng lập tức nhưng vẫn còn trong danh sách.',
    'confirm_bulk_delete' => 'Xóa các chương trình giảm giá đã chọn? Các giỏ hàng đang dùng chúng sẽ được tính lại mà không có chúng.',

    'flash_created' => 'Đã tạo chương trình giảm giá.',
    'flash_updated' => 'Đã cập nhật chương trình giảm giá.',
    'flash_deleted' => 'Đã xóa chương trình giảm giá.',
    'flash_bulk_ended' => 'Đã kết thúc {count} chương trình giảm giá.',
    'flash_bulk_deleted' => 'Đã xóa {count} chương trình giảm giá.',
];
