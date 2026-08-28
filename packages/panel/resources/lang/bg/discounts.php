<?php

return [
    'title' => 'Отстъпки',
    'description' => 'Настройте промоциите, които намаляват сумата, платена от клиента — процент, фиксирана сума или оферта купи един, вземи още един — и определете кога, къде и за кого важи всяка от тях.',
    'new_discount' => 'Нова отстъпка',
    'create_title' => 'Нова отстъпка',
    'create_description' => 'Наименувайте отстъпката и изберете как намалява цената; всичко останало се настройва в страницата на отстъпката.',
    'create_discount' => 'Създаване на отстъпка',
    'back_to_discounts' => 'Назад към отстъпките',
    'delete_discount' => 'Изтриване на отстъпката',
    'confirm_delete_discount' => 'Да се изтрие ли тази отстъпка? Количките, които я използват, ще бъдат преизчислени без нея.',

    'column_status' => 'Състояние',
    'column_name' => 'Име',
    'column_type' => 'Тип',
    'column_coupon' => 'Купон',
    'column_window' => 'Период',
    'column_usage' => 'Използване',
    'column_priority' => 'Приоритет',

    'search_placeholder' => 'Търсене на отстъпки',
    'filter_status' => 'Състояние',
    'filter_all_statuses' => 'Всички състояния',
    'filter_type' => 'Тип',
    'filter_all_types' => 'Всички типове',
    'filter_channel' => 'Канал',
    'filter_all_channels' => 'Всички канали',
    'filter_customer_group' => 'Клиентска група',
    'filter_all_customer_groups' => 'Всички клиентски групи',
    'filter_redemption' => 'Начин на прилагане',
    'filter_all_redemptions' => 'С купон и автоматични',
    'redemption_coupon' => 'Изисква купон',
    'redemption_automatic' => 'Прилага се автоматично',
    'sort_priority' => 'По приоритет',
    'sort_name' => 'Име А-Я',
    'sort_starts' => 'Започващи най-скоро',
    'sort_ends' => 'Приключващи най-скоро',
    'sort_uses' => 'Най-често използвани',
    'count_of' => '{shown} от {total}',
    'clear_filters' => 'Изчистване на филтрите',
    'empty_title' => 'Няма съответстващи отстъпки',
    'empty_description' => 'Изчистете търсенето или филтрите, или създайте нова отстъпка.',
    'empty_none_title' => 'Все още няма отстъпки',
    'empty_none_description' => 'Създайте първата си отстъпка, за да започнете промоции.',

    'status_active' => 'Активна',
    'status_scheduled' => 'Планирана',
    'status_expired' => 'Изтекла',
    'status_pending' => 'Изчакваща',

    'kpi_active_label' => 'Активни сега',
    'kpi_active_hint' => 'Важат днес',
    'kpi_scheduled_label' => 'Планирани',
    'kpi_scheduled_hint' => 'Започват по-късно',
    'kpi_ending_label' => 'Приключват скоро',
    'kpi_ending_hint' => 'До 7 дни',
    'kpi_redemptions_label' => 'Използвания',
    'kpi_redemptions_hint' => 'Всички отстъпки, за всички времена',
    'show_kpis' => 'Показване на статистиките',

    'summary_percentage_off' => ':percentage% отстъпка',

    'summary_fixed_amount_off' => ':amount отстъпка',

    'summary_buy_x_get_y' => 'Купи :buy, вземи :get',

    'field_percentage' => 'Процент отстъпка',

    'field_percentage_hint' => 'Приспада се от всеки допустим ред.',

    'field_amount' => 'Сума на отстъпката',

    'field_amounts_hint' => 'Задайте сума за всяка валута. Валута, оставена празна, не получава отстъпка.',

    'field_min_qty' => 'Количество за купуване',

    'field_reward_qty' => 'Количество за награда',

    'field_max_reward_qty' => 'Максимум за награда',

    'field_max_reward_qty_hint' => 'Оставете празно, за да се награждава всеки отговарящ комплект.',

    'field_automatically_add_rewards' => 'Автоматично добавяне на наградите в количката',

    'field_automatically_add_rewards_hint' => 'Добавя наградните продукти вместо клиента, вместо да чака той да ги добави.',

    'section_targets' => 'Отнася се за',

    'section_targets_description' => 'Ограничете отстъпката до част от каталога. Оставете блок празен, за да важи навсякъде.',

    'section_customers' => 'Допустими клиенти',

    'bucket_limitation' => 'Отнася се за',

    'bucket_limitation_description' => 'Само тези получават отстъпка.',

    'bucket_exclusion' => 'Изключени',

    'bucket_exclusion_description' => 'Никога не получават отстъпка, дори да отговарят на горното.',

    'bucket_condition' => 'Отговарящи продукти',

    'bucket_condition_description' => 'Какво трябва да купи клиентът, за да получи наградата.',

    'bucket_reward' => 'Наградни продукти',

    'bucket_reward_description' => 'Какво получава клиентът.',

    'bucket_customers' => 'Допустими клиенти',

    'bucket_customers_description' => 'Само тези клиенти могат да ползват отстъпката. Оставете празно, за да важи за всички.',

    'kind_products' => 'Продукти',

    'kind_variants' => 'Варианти',

    'kind_collections' => 'Колекции',

    'kind_brands' => 'Марки',

    'kind_customers' => 'Клиенти',

    'target_add' => 'Добавяне',

    'target_remove' => 'Премахване на {label}',

    'target_empty' => 'Нищо не е избрано, затова важи за всичко.',

    'target_dialog_title' => 'Добавяне на цели',

    'target_dialog_description' => 'Търсете сред всичко, което този блок може да обхване.',

    'target_search_placeholder' => 'Търсене на продукти, колекции, марки',

    'target_no_results' => 'Няма съвпадения.',

    'target_add_selected' => 'Добавяне на {count}',

    'section_conditions' => 'Условия',

    'section_conditions_description' => 'На какво трябва да отговаря количката, преди отстъпката да се приложи.',

    'field_min_spend' => 'Минимална сума',

    'field_min_spend_hint' => 'Задайте праг за всяка валута. Валута, оставена празна, няма минимум.',

    'automatic' => 'Автоматична',
    'no_end_date' => 'Без крайна дата',
    'usage_unlimited' => 'без ограничение',
    'usage_of' => '{used} от {max}',

    'section_details' => 'Детайли',
    'section_details_description' => 'Как се разпознава тази отстъпка и къде стои в реда на прилагане.',
    'section_configuration' => 'Конфигурация',
    'section_configuration_description' => 'Какво прави тази отстъпка с цената.',
    'section_schedule' => 'График',
    'section_usage' => 'Използване',
    'section_activity' => 'Активност',
    'activity_see_all' => 'Виж всички',
    'activity_empty' => 'Все още няма записана активност.',

    'field_name' => 'Име',
    'field_name_create_hint' => 'Вижда се от персонала. Идентификаторът се генерира от него и може да се промени след това.',
    'field_handle' => 'Идентификатор',
    'field_handle_hint' => 'Уникална, постоянна референция за тази отстъпка.',
    'field_type' => 'Тип',
    'field_coupon' => 'Код на купон',
    'field_coupon_hint' => 'Оставете празно, за да се прилага отстъпката автоматично.',
    'field_starts_at' => 'Начало',
    'field_ends_at' => 'Край',
    'field_ends_at_hint' => 'Оставете празно, за да важи, докато не я изключите.',
    'field_priority' => 'Приоритет',
    'field_priority_hint' => 'По-ниската стойност се прилага първа. Отстъпки с еднакъв приоритет се прилагат в неопределен ред.',
    'field_stop' => 'Спиране след тази отстъпка',
    'field_stop_hint' => 'Пропускане на всички отстъпки с по-нисък приоритет, щом тази бъде приложена.',
    'field_max_uses' => 'Максимален брой използвания',
    'field_max_uses_hint' => 'Оставете празно за неограничено.',
    'field_max_uses_per_user' => 'Максимум на клиент',
    'field_max_uses_per_user_hint' => 'Оставете празно за неограничено.',

    'usage_redeemed' => 'Използвана',

    'raw_data_description' => 'За този тип отстъпка няма регистрирана форма в панела, затова запазените ѝ настройки се редактират тук като JSON.',
    'raw_data_invalid' => 'Въведете валиден JSON.',
    'type_missing' => 'Пакетът, който регистрира този тип отстъпка, вече не е инсталиран.',

    'bulk_end_now' => 'Приключване сега',
    'bulk_delete' => 'Изтриване',
    'confirm_bulk_end' => 'Да се приключат ли избраните отстъпки сега? Спират да важат веднага, но остават в списъка.',
    'confirm_bulk_delete' => 'Да се изтрият ли избраните отстъпки? Количките, които ги използват, ще бъдат преизчислени без тях.',

    'flash_created' => 'Отстъпката е създадена.',
    'flash_updated' => 'Отстъпката е обновена.',
    'flash_deleted' => 'Отстъпката е изтрита.',
    'flash_bulk_ended' => 'Приключени са {count} отстъпки.',
    'flash_bulk_deleted' => 'Изтрити са {count} отстъпки.',
];
