<?php

return [
    'title' => 'Descuentos',
    'description' => 'Configure las promociones que reducen lo que paga el cliente — un porcentaje, un importe fijo o una oferta de compra uno y llévate otro — y controle cuándo, dónde y a quién se aplica cada una.',
    'new_discount' => 'Nuevo descuento',
    'create_title' => 'Nuevo descuento',
    'create_description' => 'Ponga nombre al descuento y elija cómo reduce el precio; todo lo demás se configura en la página del descuento.',
    'create_discount' => 'Crear descuento',
    'back_to_discounts' => 'Volver a los descuentos',
    'delete_discount' => 'Eliminar descuento',
    'confirm_delete_discount' => '¿Eliminar este descuento? Los carritos que lo estén usando se recalcularán sin él.',

    'column_status' => 'Estado',
    'column_name' => 'Nombre',
    'column_type' => 'Tipo',
    'column_coupon' => 'Cupón',
    'column_window' => 'Vigencia',
    'column_usage' => 'Uso',
    'column_priority' => 'Prioridad',

    'search_placeholder' => 'Buscar descuentos',
    'filter_status' => 'Estado',
    'filter_all_statuses' => 'Todos los estados',
    'filter_type' => 'Tipo',
    'filter_all_types' => 'Todos los tipos',
    'filter_channel' => 'Canal',
    'filter_all_channels' => 'Todos los canales',
    'filter_customer_group' => 'Grupo de clientes',
    'filter_all_customer_groups' => 'Todos los grupos de clientes',
    'filter_redemption' => 'Aplicación',
    'filter_all_redemptions' => 'Cupón y automáticos',
    'redemption_coupon' => 'Requiere un cupón',
    'redemption_automatic' => 'Se aplica automáticamente',
    'sort_priority' => 'Por prioridad',
    'sort_name' => 'Nombre A-Z',
    'sort_starts' => 'Empiezan antes',
    'sort_ends' => 'Terminan antes',
    'sort_uses' => 'Más canjeados',
    'count_of' => '{shown} de {total}',
    'clear_filters' => 'Limpiar filtros',
    'empty_title' => 'Ningún descuento coincide',
    'empty_description' => 'Pruebe a limpiar la búsqueda o los filtros, o cree un descuento nuevo.',
    'empty_none_title' => 'Todavía no hay descuentos',
    'empty_none_description' => 'Cree su primer descuento para empezar con las promociones.',

    'status_active' => 'Activo',
    'status_scheduled' => 'Programado',
    'status_expired' => 'Caducado',
    'status_pending' => 'Pendiente',

    'kpi_active_label' => 'Activos ahora',
    'kpi_active_hint' => 'En marcha hoy',
    'kpi_scheduled_label' => 'Programados',
    'kpi_scheduled_hint' => 'Empiezan más adelante',
    'kpi_ending_label' => 'Terminan pronto',
    'kpi_ending_hint' => 'En 7 días',
    'kpi_redemptions_label' => 'Canjes',
    'kpi_redemptions_hint' => 'Todos los descuentos, desde siempre',
    'show_kpis' => 'Mostrar estadísticas',

    'summary_percentage_off' => ':percentage % de descuento',

    'summary_fixed_amount_off' => ':amount de descuento',

    'summary_buy_x_get_y' => 'Compra :buy, llévate :get',

    'field_percentage' => 'Porcentaje de descuento',

    'field_percentage_hint' => 'Se descuenta de cada línea elegible.',

    'field_amount' => 'Importe de descuento',

    'field_amounts_hint' => 'Fije un importe por moneda. Una moneda en blanco no recibe descuento.',

    'field_min_qty' => 'Cantidad a comprar',

    'field_reward_qty' => 'Cantidad de regalo',

    'field_max_reward_qty' => 'Máximo de regalo',

    'field_max_reward_qty_hint' => 'Déjelo vacío para premiar cada conjunto que califique.',

    'field_automatically_add_rewards' => 'Añadir los regalos al carrito automáticamente',

    'field_automatically_add_rewards_hint' => 'Añade los productos de regalo por el cliente en lugar de esperar a que los añada.',

    'section_targets' => 'Se aplica a',

    'section_targets_description' => 'Limite este descuento a una parte del catálogo. Deje un bloque vacío para aplicarlo en todas partes.',

    'section_customers' => 'Clientes elegibles',

    'bucket_limitation' => 'Se aplica a',

    'bucket_limitation_description' => 'Solo estos se descuentan.',

    'bucket_exclusion' => 'Excluidos',

    'bucket_exclusion_description' => 'Nunca se descuentan, aunque coincidan con lo anterior.',

    'bucket_condition' => 'Productos que califican',

    'bucket_condition_description' => 'Lo que debe comprar el cliente para obtener el regalo.',

    'bucket_reward' => 'Productos de regalo',

    'bucket_reward_description' => 'Lo que recibe el cliente.',

    'bucket_customers' => 'Clientes elegibles',

    'bucket_customers_description' => 'Solo estos clientes pueden usar el descuento. Déjelo vacío para permitir a todos.',

    'kind_products' => 'Productos',

    'kind_variants' => 'Variantes',

    'kind_collections' => 'Colecciones',

    'kind_brands' => 'Marcas',

    'kind_customers' => 'Clientes',

    'target_add' => 'Añadir',

    'target_remove' => 'Quitar {label}',

    'target_empty' => 'No hay nada seleccionado, así que se aplica a todo.',

    'target_dialog_title' => 'Añadir objetivos',

    'target_dialog_description' => 'Busque entre todo lo que este bloque puede abarcar.',

    'target_search_placeholder' => 'Buscar productos, colecciones, marcas',

    'target_no_results' => 'No hay coincidencias.',

    'target_add_selected' => 'Añadir {count}',

    'section_conditions' => 'Condiciones',

    'section_conditions_description' => 'Qué debe cumplir un carrito antes de que se aplique este descuento.',

    'field_min_spend' => 'Gasto mínimo',

    'field_min_spend_hint' => 'Fije un umbral por moneda. Una moneda en blanco no tiene mínimo.',

    'automatic' => 'Automático',
    'no_end_date' => 'Sin fecha de fin',
    'usage_unlimited' => 'sin límite',
    'usage_of' => '{used} de {max}',

    'section_details' => 'Detalles',
    'section_details_description' => 'Cómo se identifica este descuento y qué lugar ocupa en el orden de aplicación.',
    'section_configuration' => 'Configuración',
    'section_configuration_description' => 'Qué hace este descuento con el precio.',
    'section_schedule' => 'Programación',
    'section_usage' => 'Uso',
    'section_activity' => 'Actividad',
    'activity_see_all' => 'Ver todo',
    'activity_empty' => 'Todavía no hay nada registrado.',

    'field_name' => 'Nombre',
    'field_name_create_hint' => 'Visible para el personal. El identificador se genera a partir de él y puede cambiarse después.',
    'field_handle' => 'Identificador',
    'field_handle_hint' => 'Una referencia única y estable para este descuento.',
    'field_type' => 'Tipo',
    'field_coupon' => 'Código de cupón',
    'field_coupon_hint' => 'Déjelo vacío para aplicar el descuento automáticamente.',
    'field_starts_at' => 'Empieza',
    'field_ends_at' => 'Termina',
    'field_ends_at_hint' => 'Déjelo vacío para que siga vigente hasta que lo desactive.',
    'field_priority' => 'Prioridad',
    'field_priority_hint' => 'El valor más bajo se aplica primero. Los descuentos con la misma prioridad se aplican en un orden indeterminado.',
    'field_stop' => 'Detener después de este descuento',
    'field_stop_hint' => 'Omitir todos los descuentos de menor prioridad en cuanto se aplique este.',
    'field_max_uses' => 'Canjes máximos',
    'field_max_uses_hint' => 'Déjelo vacío para ilimitados.',
    'field_max_uses_per_user' => 'Máximo por cliente',
    'field_max_uses_per_user_hint' => 'Déjelo vacío para ilimitados.',

    'usage_redeemed' => 'Canjeado',

    'raw_data_description' => 'Este tipo de descuento no tiene un formulario registrado en el panel, así que sus ajustes guardados se editan aquí como JSON.',
    'raw_data_invalid' => 'Introduzca un JSON válido.',
    'type_missing' => 'El paquete que registró este tipo de descuento ya no está instalado.',

    'bulk_end_now' => 'Terminar ahora',
    'bulk_delete' => 'Eliminar',
    'confirm_bulk_end' => '¿Terminar ahora los descuentos seleccionados? Dejarán de aplicarse de inmediato, pero seguirán en la lista.',
    'confirm_bulk_delete' => '¿Eliminar los descuentos seleccionados? Los carritos que los estén usando se recalcularán sin ellos.',

    'flash_created' => 'Descuento creado.',
    'flash_updated' => 'Descuento actualizado.',
    'flash_deleted' => 'Descuento eliminado.',
    'flash_bulk_ended' => 'Se han terminado {count} descuentos.',
    'flash_bulk_deleted' => 'Se han eliminado {count} descuentos.',
];
