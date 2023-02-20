<?php

return [
    /**
     * Channels.
     */
    'channels.index.title' => 'Каналы',
    'channels.index.create_btn' => 'Создать канал',
    'channels.index.table_row_action_text' => 'Редактировать канал',
    /**
     * Channels show page.
     */
    'channels.show.title' => 'Редактировать канал',
    /**
     * Channels create page.
     */
    'channels.create.title' => 'Создать канал',
    /**
     * Settings layout.
     */
    'layout.menu_btn' => 'Меню настроек',
    /**
     * Staff listing page.
     */
    'staff.index.title' => 'Сотрудники',
    'staff.index.search_placeholder' => 'Поиск сотрудников',
    'staff.index.active_filter' => 'Показать неактивных',
    'staff.index.create_btn' => 'Добавить сотрудника',
    'staff.index.table_row_action_text' => 'Редактировать сотрудника',
    /**
     * Staff show page.
     */
    'staff.show.title' => 'Редактировать сотрудника',
    'staff.show.delete_btn' => 'Деактивировать учётную запись',
    'staff.show.delete_title' => 'Удалить сотрудника',
    'staff.show.restore_title' => 'Восстановить сотрудника',
    /**
     * Staff create page.
     */
    'staff.create.title' => 'Создать учётную запись сотрудника',
    /**
     * Staff form.
     */
    'staff.form.create_btn' => 'Создать учётную запись сотрудника',
    'staff.form.update_btn' => 'Обновить учётную запись сотрудника',
    'staff.form.permissions_heading' => 'Права доступа',
    'staff.form.permissions_description' => 'Управление индивидуальными правами доступа для сотрудников.',
    'staff.form.admin_message' => 'Администратор имеет доступ ко всем разрешениям.',
    'staff.form.danger_zone.label' => 'Удалить сотрудника',
    'staff.form.danger_zone.delete_strapline' => 'Удаление сотрудника приведет к остановке всех доступов к хабу, но вы сможете восстановить его позже.',
    'staff.form.danger_zone.restore_strapline' => 'Восстановление учетной записи сотрудника позволит ему снова получить доступ к хабу.',
    'staff.form.danger_zone.own_account' => 'Удаление своей учетной записи немедленно выйдет из системы.',

    /**
     * Addons listing page.
     */
    'addons.index.title' => 'Дополнения',
    'addons.index.table_row_action_text' => 'Просмотр',
    /**
     * Addons show page.
     */
    'addons.show.title' => 'Дополнение',
    /*
     * Languages listing page.
     */
    'languages.index.title' => 'Языки',
    'languages.index.create_btn' => 'Создать язык',
    'languages.index.table_row_action_text' => 'Редактировать язык',
    /**
     * Languages create page.
     */
    'languages.create.title' => 'Создать язык',
    /**
     * Languages show page.
     */
    'languages.show.title' => 'Редактирование языка',
    /**
     * Language form.
     */
    'languages.form.create_btn' => 'Создать язык',
    'languages.form.update_btn' => 'Обновить язык',
    'languages.form.default_instructions' => 'Установите, является ли этот язык языком по умолчанию, это переопределит текущий язык по умолчанию.',
    /**
     * Currencies table.
     */
    'currencies.index.title' => 'Валюты',
    'currencies.index.table_row_action_text' => 'Редактировать',
    'currencies.index.no_results' => 'У вас пока нет валют в системе.',
    /**
     * Currency show page.
     */
    'currencies.show.title' => 'Редактирование валюты',
    /**
     * Currency create page.
     */
    'currencies.create.title' => 'Создать валюту',
    'currencies.index.create_currency_btn' => 'Создать валюту',
    /**
     * Currency form.
     */
    'currencies.form.update_btn' => 'Обновить валюту',
    'currencies.form.create_btn' => 'Создать валюту',
    'currencies.form.notify.created' => 'Валюта создана',
    'currencies.form.format_help_text' => [
        'Это позволяет указать формат, который должны использовать поля цены для этой валюты.',
        'При отображении Lunar заменяет <code>{value}</code> на отформатированную цену. Например, <code>£{value}</code>.',
        'Вы всегда должны включать <code>{value}</code>, чтобы это работало правильно.',
    ],
    /**
     * Attributes.
     */
    'attributes.index.title' => 'Атрибуты',
    'attributes.show.title' => 'Редактирование атрибутов :type',
    'attributes.show.locked' => 'Этот атрибут требуется системой и поэтому заблокирован для редактирования.',
    'attributes.create.title' => 'Создать атрибут',
    'attributes.form.update_btn' => 'Обновить атрибут',
    'attributes.form.create_btn' => 'Создать атрибут',
    'attributes.form.notify.created' => 'Атрибут создан',
    /**
     * Tags.
     */
    'tags.show.title' => 'Редактирование тега',
    'tags.index.title' => 'Теги',
    'tags.index.table_row_action_text' => 'Редактировать',
    'tags.form.update_btn' => 'Обновить тег',
    'tags.form.create_btn' => 'Создать тег',
    'tags.form.notify.updated' => 'Тег обновлен',
    /**
     * Activity log page.
     */
    'activity_log.index.title' => 'Журнал активности',
    /*
     * Product Options
     */
    'product.options.index.title' => 'Опции',
    'product.options.index.create_btn' => 'Создать опцию',
    'product.options.index.table_row_action_text' => 'Редактировать опцию',
    /**
     * Taxes.
     */
    'taxes.tax-zones.index.title' => 'Зоны налогообложения',
    'taxes.tax-zones.confirm_delete.title' => 'Подтвердите удаление',
    'taxes.tax-zones.confirm_delete.message' => 'Вы уверены, что хотите удалить эту зону налогообложения? Это может привести к потере данных.',
    'taxes.tax-zones.customer_groups.title' => 'Ограничить для групп клиентов',
    'taxes.tax-zones.customer_groups.instructions' => 'Выберите, для каких групп клиентов вы хотите ограничить эту зону. Оставьте незаполненным, чтобы не ограничивать.',
    'taxes.tax-zones.create_title' => 'Создать зону налогообложения',
    'taxes.tax-zones.create_btn' => 'Создать зону налогообложения',
    'taxes.tax-zones.delete_btn' => 'Удалить зону налогообложения',
    'taxes.tax-zones.index.table_row_action_text' => 'Управление',
    'taxes.tax-classes.index.title' => 'Классы налогов',
    'taxes.tax-classes.index.create.title' => 'Создать класс налогов',
    'taxes.tax-classes.index.update.title' => 'Обновить класс налогов',
    'taxes.tax-classes.create_btn' => 'Создать класс налогов',
    'taxes.tax-zones.price_display.label' => 'Отображение цены',
    'taxes.tax-zones.price_display.excl_tax' => 'Без налогов',
    'taxes.tax-zones.price_display.incl_tax' => 'С налогами',
    'taxes.tax-zones.zone_type.countries' => 'Ограничить по странам',
    'taxes.tax-zones.zone_type.states' => 'Ограничить по штатам/провинциям',
    'taxes.tax-zones.zone_type.postcodes' => 'Ограничить по почтовым индексам',
    'taxes.tax-zones.tax_rates.title' => 'Налоговые ставки',
    'taxes.tax-zones.tax_rates.create_button' => 'Добавить налоговую ставку',
    'taxes.tax-zones.save_btn' => 'Сохранить зону налогообложения',
    'taxes.tax-classes.index.delete_message' => 'Вы уверены? Это может привести к потере данных.',
    'taxes.tax-classes.index.delete_message_disabled' => 'Вы не можете удалить класс налогов, который связан с вариантами продуктов',
    'taxes.tax-classes.index.delete_message_default' => 'Вы должны выбрать новый класс по умолчанию перед удалением',
    /**
     * Customer Groups.
     */
    'customer-groups.index.title' => 'Группы клиентов',
    'customer-groups.index.create_btn' => 'Создать группу клиентов',
    'customer-groups.index.table_row_action_text' => 'Изменить группу',
    /**
     * Customer Groups show page.
     */
    'customer-groups.show.title' => 'Изменить группу клиентов',
    /**
     * Customer Groups create page.
     */
    'customer-groups.create.title' => 'Создать группу клиентов',
    'customer-groups.form.default_instructions' => 'Установите, должна ли эта группа клиентов быть по умолчанию',
];
