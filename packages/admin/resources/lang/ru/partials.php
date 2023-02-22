<?php

return [
    /**
     * Activity Log.
     */
    'activity-log.added_images.description' => 'Добавлено :count изображений',
    'activity-log.system.system' => 'Система',
    'activity-log.update.updated' => 'Обновлено',
    'activity-log.create.created' => 'Создано',
    /**
     * Associations.
     */
    'products.associations.heading' => 'Ассоциации',
    'products.associations.cross-sell' => 'Сопутствующие товары',
    'products.associations.up-sell' => 'Рекомендуемые товары',
    'products.associations.alternate' => 'Альтернативные товары',
    'products.associations.show_inverse' => 'Показать инвертированные',
    'products.associations.add_inverse' => 'Добавить инвертированную ассоциацию',
    'products.associations.add_association' => 'Добавить ассоциацию',
    'products.associations.up-sell_selecting_products' => 'Добавьте товары для рекомендации, выполнив поиск выше и выбрав их.',
    /**
     * Availability.
     */
    'availability.heading' => 'Наличие',
    'availability.schedule_notice' => 'Когда вы устанавливаете доступность, этот :type не будет доступен для канала / группы клиентов, пока дата не пройдет и :type не будет активен.',
    'availability.channel_heading' => 'Каналы',
    'availability.channel_strapline' => 'Выберите каналы, на которых этот :type доступен.',
    'availability.channels.hidden' => 'Скрытый',
    'availability.channels.purchasable' => 'Доступен для покупки',
    'availability.channels.strapline' => 'Установите доступность для групп клиентов.',
    'availability.channels.scheduled_from' => 'Запланировано с :datetime',
    'availability.channels.scheduled_to' => 'Доступен до :datetime',
    'availability.channels.scheduled_range' => ':from до :to',
    'availability.channels.scheduled_always' => 'Всегда доступен',
    'availability.channels.scheduled_never' => 'Никогда недоступен',
    'availability.channels.schedule_modal.title' => 'Планирование доступности',
    'availability.channels.schedule_modal.starts_at.label' => 'Начинается',
    'availability.channels.schedule_modal.starts_at.instructions' => 'Установите дату начала доступности для этой группы клиентов, без даты - всегда доступен.',
    'availability.channels.schedule_modal.ends_at.label' => 'Окончание',
    'availability.channels.schedule_modal.ends_at.instructions' => 'Установите дату окончания доступности для этой группы клиентов, без даты - всегда доступен.',
    'availability.channels.schedule_modal.btn_text' => 'Принять и закрыть',
    'availability.scheduled_text' => 'Этот :type запланирован для публикации :date.',
    'availability.schedule_placeholder' => 'Дата публикации расписания.',
    'availability.schedule_btn_text' => 'Запланировать наличие',
    'availability.clear_btn' => 'Очистить',
    'availability.customer_groups.title' => 'Группы клиентов',
    'availability.customer_groups.visible' => 'Видимые',
    'availability.customer_groups.hidden' => 'Скрытые',
    'availability.customer_groups.purchasable' => 'Доступно для покупки',
    'availability.customer_groups.strapline' => 'Запланируйте, для каких групп клиентов этот :type будет доступен.',
    'availability.customer_groups.scheduled_from' => 'Запланировано с :datetime',
    'availability.customer_groups.scheduled_to' => 'Доступно до :datetime',
    'availability.customer_groups.scheduled_range' => ':from до :to',
    'availability.customer_groups.scheduled_always' => 'Всегда доступно',
    'availability.customer_groups.scheduled_never' => 'Никогда недоступно',
    'availability.customer_groups.schedule_modal.title' => 'Запланировать наличие',
    'availability.customer_groups.schedule_modal.starts_at.label' => 'Начало',
    'availability.customer_groups.schedule_modal.starts_at.instructions' => 'Укажите дату начала доступности для данной группы клиентов, если дата не указана, то доступно всегда.',
    'availability.customer_groups.schedule_modal.ends_at.label' => 'Окончание',
    'availability.customer_groups.schedule_modal.ends_at.instructions' => 'Укажите дату окончания доступности для данной группы клиентов, если дата не указана, то доступно всегда.',
    'availability.customer_groups.schedule_modal.btn_text' => 'Принять и закрыть',
    /**
     * Basic Information.
     */
    'products.basic-information.heading' => 'Базовая информация',
    /**
     * Image Manager.
     */
    'image-manager.generic_upload_error' => 'При загрузке произошла ошибка, пожалуйста, проверьте, что вы выбрали только изображения.',
    'image-manager.heading' => 'Изображения',
    'image-manager.download_original_btn' => 'Скачать оригинал',
    'image-manager.remake_transforms' => 'Пересоздать преобразования',
    'image-manager.remake_transforms.notify.success' => 'Преобразования изображения были пересозданы',
    'image-manager.save_btn' => 'Сохранить изображение',
    'image-manager.edit_row_btn' => 'Редактировать',
    'image-manager.delete_row_btn' => 'Удалить',
    'image-manager.delete_primary' => 'Вы не можете удалить основное изображение.',
    'image-manager.delete_message' => 'Это изображение будет удалено при сохранении,',
    'image-manager.undo_btn' => 'отменить',
    'image-manager.no_results' => 'Для этого продукта нет изображений, добавьте свое первое изображение выше.',
    'image-manager.upload_file' => 'Загрузить файл или перетащить',
    'image-manager.file_format' => 'PNG, JPG, GIF до 10МБ',
    'image-manager.select_images' => 'Выберите изображения',
    'image-manager.select_images_btn' => 'Выберите изображения',
    /**
     * Discounts
     */
    'discounts.discount_type.heading' => 'Тип скидки',
    'discounts.conditions.heading' => 'Условия',
    'discounts.coupon.heading' => 'Купон',
    'discounts.coupon.instructions' => 'Введите купон, необходимый для применения скидки, если оставить его пустым, он будет применен автоматически.',
    'discounts.coupon.max_uses.instructions' => 'Оставьте пустым для неограниченного использования.',
    'discounts.limitations.heading' => 'Ограничения',
    'discounts.limitations.by_collection' => 'Ограничить по коллекции',
    'discounts.limitations.by_brand' => 'Ограничить по бренду',
    'discounts.limitations.by_product' => 'Ограничить по продукту',
    'discounts.limitations.view_brand' => 'Просмотреть бренд',
    'discounts.limitations.view_product' => 'Просмотреть продукт',

    /**
     * Product Collections.
     */
    'products.collections.heading' => 'Коллекции',
    'products.collections.view_collection' => 'Просмотреть коллекцию',
    /**
     * Product Status Bar.
     */
    'products.status-bar.published.label' => 'Опубликовано',
    'products.status-bar.published.description' => 'Этот продукт будет доступен на всех включенных каналах и группах клиентов.',
    'products.status-bar.draft.label' => 'Черновик',
    'products.status-bar.draft.description' => 'Этот продукт будет скрыт от всех каналов и групп клиентов.',
    /**
     * Variants.
     */
    'products.variants.heading' => 'Варианты',
    'products.variants.strapline' => 'Этот продукт имеет несколько вариантов, таких как разные размеры или цвета.',
    'products.variants.table_row_action_text' => 'Редактировать',
    'products.variants.table_row_delete_text' => 'Удалить',
    'products.variants.removal_message' => 'Это удалит все варианты этого продукта',
    /**
     * Product type.
     */
    'product-type.available_title' => 'Доступные атрибуты',
    'product-type.selected_title' => 'Выбранные атрибуты (:count)',
    'product-type.attribute_search_placeholder' => 'Поиск атрибута по имени',
    'product-type.attribute_system_required' => 'Этот атрибут требуется системой',
    'product-type.product_attributes_btn' => 'Атрибуты продукта',
    'product-type.variant_attributes_btn' => 'Атрибуты варианта',
    /**
     * Pricing.
     */
    'pricing.title' => 'Ценообразование',
    'pricing.customer_groups.title' => 'Ценообразование для групп клиентов',
    'pricing.customer_groups.strapline' => 'Определяет, хотели бы вы иметь разную цену для разных групп клиентов.',
    'pricing.tiers.title' => 'Ступенчатое ценообразование',
    'pricing.tiers.strapline' => 'Ступенчатое ценообразование позволяет предлагать скидки на основе проданных единиц.',
    'pricing.non_default_currency_alert' => 'Некоторые поля могут быть изменены только при использовании базовой валюты.',
    'pricing.tiers.add_tier_btn' => 'Добавить ступень',
    /**
     * Indentifiers.
     */
    'identifiers.title' => 'Идентификаторы продуктов',
    /**
     * URLs.
     */
    'urls.title' => 'URL-адреса',
    'urls.create_btn' => 'Добавить URL-адрес',
    /**
     * Inventory.
     */
    'inventory.title' => 'Инвентаризация',
    'inventory.maintenance_notice' => 'Этот раздел все еще находится в разработке и скорее всего изменится в следующем выпуске.',
    'inventory.options.in_stock' => 'В наличии',
    'inventory.options.always' => 'Всегда',
    'inventory.options.backorder' => 'Заказать',
    'inventory.purchasable.in_stock' => 'Этот товар можно купить только в наличии.',
    'inventory.purchasable.always' => 'Этот товар можно купить всегда.',
    'inventory.purchasable.backorder' => 'Этот товар можно купить, когда ожидается наличие.',
    /**
     * Shipping.
     */
    'shipping.title' => 'Доставка',
    'shipping.calculated_volume' => 'Рассчитывается как :value.',
    'shipping.manual_volume_btn' => 'Нажмите, чтобы установить вручную',
    'shipping.auto_volume_btn' => 'Использовать сгенерированный объем',
    /**
     * Customer Addresses.
     */
    'customers.addresses.billing_default' => 'По умолчанию для выставления счетов',
    'customers.addresses.shipping_default' => 'По умолчанию для доставки',
    /**
     * Customers.
     */
    'customers.purchase-history.purchasable' => 'Доступный для покупки',
    'customers.purchase-history.identifier' => 'Идентификатор',
    'customers.purchase-history.quantity' => 'Количество',
    'customers.purchase-history.revenue' => 'Доход',
    'customers.purchase-history.order_count' => 'Кол-во заказов',
    'customers.purchase-history.last_ordered' => 'Последний заказ',
    /**
     * Orders.
     */
    'orders.totals.sub_total' => 'Итого',
    'orders.totals.shipping_total' => 'Стоимость доставки',
    'orders.totals.total' => 'Всего',
    'orders.totals.notes_empty' => 'Нет заметок по этому заказу',
    'orders.totals.discount_total' => 'Скидка',
    'orders.lines.unit_price' => 'Цена за единицу',
    'orders.lines.quantity' => 'Количество',
    'orders.lines.sub_total' => 'Итого',
    'orders.lines.discount_total' => 'Скидка',
    'orders.lines.total' => 'Всего',
    'orders.lines.current_stock_level' => 'Текущий уровень запасов: :count',
    'orders.lines.purchase_stock_level' => 'на момент заказа: :count',
    'orders.details.status' => 'Статус',
    'orders.details.reference' => 'Ссылка',
    'orders.details.customer_reference' => 'Ссылка клиента',
    'orders.details.channel' => 'Канал',
    'orders.details.date_created' => 'Дата создания',
    'orders.details.date_placed' => 'Дата размещения',
    'orders.details.new_returning' => 'Новый / Постоянный',
    'orders.details.new_customer' => 'Новый клиент',
    'orders.details.returning_customer' => 'Постоянный клиент',
    'orders.address.not_set' => 'Адрес не указан',
    /**
     * Forms.
     */
    'forms.channel.delete_channel' => 'Удалить канал',
    'forms.channel.channel_name_delete' => 'Введите название канала, чтобы его удалить',
    'forms.brand_delete_brand' => 'Удалить бренд',
    'forms.brand_name_delete' => 'Введите название бренда, чтобы его удалить',
    'forms.customer-group.delete_customer_group' => 'Удалить группу клиентов',
    'forms.customer-group.customer_group_name_delete' => 'Введите название группы клиентов, чтобы ее удалить',
    /**
     * Transactions.
     */
    'orders.transactions.capture' => 'Захвачено',
    'orders.transactions.intent' => 'Намерение',
    'orders.transactions.refund' => 'Возвращено',
];
