<?php

return [
    'title' => 'Descontos',
    'description' => 'Configure as promoções que reduzem o que o cliente paga — uma porcentagem, um valor fixo ou uma oferta compre um leve outro — e controle quando, onde e para quem cada uma se aplica.',
    'new_discount' => 'Novo desconto',
    'create_title' => 'Novo desconto',
    'create_description' => 'Dê um nome ao desconto e escolha como ele reduz o preço; o restante é configurado na página do desconto.',
    'create_discount' => 'Criar desconto',
    'back_to_discounts' => 'Voltar aos descontos',
    'delete_discount' => 'Excluir desconto',
    'confirm_delete_discount' => 'Excluir este desconto? Os carrinhos que o utilizam serão recalculados sem ele.',

    'column_status' => 'Situação',
    'column_name' => 'Nome',
    'column_type' => 'Tipo',
    'column_coupon' => 'Cupom',
    'column_window' => 'Vigência',
    'column_usage' => 'Uso',
    'column_priority' => 'Prioridade',

    'search_placeholder' => 'Buscar descontos',
    'filter_status' => 'Situação',
    'filter_all_statuses' => 'Todas as situações',
    'filter_type' => 'Tipo',
    'filter_all_types' => 'Todos os tipos',
    'filter_channel' => 'Canal',
    'filter_all_channels' => 'Todos os canais',
    'filter_customer_group' => 'Grupo de clientes',
    'filter_all_customer_groups' => 'Todos os grupos de clientes',
    'filter_redemption' => 'Aplicação',
    'filter_all_redemptions' => 'Cupom e automáticos',
    'redemption_coupon' => 'Exige um cupom',
    'redemption_automatic' => 'Aplicado automaticamente',
    'sort_priority' => 'Por prioridade',
    'sort_name' => 'Nome A-Z',
    'sort_starts' => 'Começam antes',
    'sort_ends' => 'Terminam antes',
    'sort_uses' => 'Mais resgatados',
    'count_of' => '{shown} de {total}',
    'clear_filters' => 'Limpar filtros',
    'empty_title' => 'Nenhum desconto corresponde',
    'empty_description' => 'Tente limpar a busca ou os filtros, ou crie um novo desconto.',
    'empty_none_title' => 'Ainda não há descontos',
    'empty_none_description' => 'Crie seu primeiro desconto para começar as promoções.',

    'status_active' => 'Ativo',
    'status_scheduled' => 'Agendado',
    'status_expired' => 'Expirado',
    'status_pending' => 'Pendente',

    'kpi_active_label' => 'Ativos agora',
    'kpi_active_hint' => 'Valendo hoje',
    'kpi_scheduled_label' => 'Agendados',
    'kpi_scheduled_hint' => 'Começam depois',
    'kpi_ending_label' => 'Terminam em breve',
    'kpi_ending_hint' => 'Em até 7 dias',
    'kpi_redemptions_label' => 'Resgates',
    'kpi_redemptions_hint' => 'Todos os descontos, desde sempre',
    'show_kpis' => 'Mostrar estatísticas',

    'summary_percentage_off' => ':percentage% de desconto',

    'summary_fixed_amount_off' => ':amount de desconto',

    'summary_buy_x_get_y' => 'Compre :buy, leve :get',

    'field_percentage' => 'Percentual de desconto',

    'field_percentage_hint' => 'Descontado de cada linha elegível.',

    'field_amount' => 'Valor do desconto',

    'field_amounts_hint' => 'Defina um valor por moeda. Uma moeda deixada em branco não recebe desconto.',

    'field_min_qty' => 'Quantidade a comprar',

    'field_reward_qty' => 'Quantidade de brinde',

    'field_max_reward_qty' => 'Máximo de brinde',

    'field_max_reward_qty_hint' => 'Deixe em branco para premiar cada conjunto qualificado.',

    'field_automatically_add_rewards' => 'Adicionar os brindes ao carrinho automaticamente',

    'field_automatically_add_rewards_hint' => 'Adiciona os produtos de brinde pelo cliente em vez de esperar que ele adicione.',

    'section_targets' => 'Aplica-se a',

    'section_targets_description' => 'Restrinja este desconto a parte do catálogo. Deixe um bloco vazio para valer em tudo.',

    'section_customers' => 'Clientes elegíveis',

    'bucket_limitation' => 'Aplica-se a',

    'bucket_limitation_description' => 'Somente estes recebem desconto.',

    'bucket_exclusion' => 'Excluídos',

    'bucket_exclusion_description' => 'Nunca recebem desconto, mesmo que correspondam acima.',

    'bucket_condition' => 'Produtos que qualificam',

    'bucket_condition_description' => 'O que o cliente precisa comprar para ganhar o brinde.',

    'bucket_reward' => 'Produtos de brinde',

    'bucket_reward_description' => 'O que o cliente recebe.',

    'bucket_customers' => 'Clientes elegíveis',

    'bucket_customers_description' => 'Somente estes clientes podem usar o desconto. Deixe em branco para liberar a todos.',

    'kind_products' => 'Produtos',

    'kind_variants' => 'Variações',

    'kind_collections' => 'Coleções',

    'kind_brands' => 'Marcas',

    'kind_customers' => 'Clientes',

    'target_add' => 'Adicionar',

    'target_remove' => 'Remover {label}',

    'target_empty' => 'Nada selecionado, então vale para tudo.',

    'target_dialog_title' => 'Adicionar alvos',

    'target_dialog_description' => 'Busque entre tudo o que este bloco pode alcançar.',

    'target_search_placeholder' => 'Buscar produtos, coleções, marcas',

    'target_no_results' => 'Nada corresponde.',

    'target_add_selected' => 'Adicionar {count}',

    'section_conditions' => 'Condições',

    'section_conditions_description' => 'O que um carrinho precisa atender antes de este desconto valer.',

    'field_min_spend' => 'Gasto mínimo',

    'field_min_spend_hint' => 'Defina um limite por moeda. Uma moeda deixada em branco não tem mínimo.',

    'automatic' => 'Automático',
    'no_end_date' => 'Sem data de término',
    'usage_unlimited' => 'sem limite',
    'usage_of' => '{used} de {max}',

    'section_details' => 'Detalhes',
    'section_details_description' => 'Como este desconto é identificado e em que ponto da ordem ele entra.',
    'section_configuration' => 'Configuração',
    'section_configuration_description' => 'O que este desconto faz com o preço.',
    'section_schedule' => 'Agendamento',
    'section_usage' => 'Uso',
    'section_activity' => 'Atividade',
    'activity_see_all' => 'Ver tudo',
    'activity_empty' => 'Nada registrado ainda.',

    'field_name' => 'Nome',
    'field_name_create_hint' => 'Visível para a equipe. O identificador é gerado a partir dele e pode ser alterado depois.',
    'field_handle' => 'Identificador',
    'field_handle_hint' => 'Uma referência única e estável para este desconto.',
    'field_type' => 'Tipo',
    'field_coupon' => 'Código do cupom',
    'field_coupon_hint' => 'Deixe em branco para aplicar o desconto automaticamente.',
    'field_starts_at' => 'Começa',
    'field_ends_at' => 'Termina',
    'field_ends_at_hint' => 'Deixe em branco para valer até você desligá-lo.',
    'field_priority' => 'Prioridade',
    'field_priority_hint' => 'O menor valor entra primeiro. Descontos de mesma prioridade entram em ordem indefinida.',
    'field_stop' => 'Parar após este desconto',
    'field_stop_hint' => 'Ignorar todos os descontos de prioridade menor assim que este for aplicado.',
    'field_max_uses' => 'Resgates máximos',
    'field_max_uses_hint' => 'Deixe em branco para ilimitado.',
    'field_max_uses_per_user' => 'Máximo por cliente',
    'field_max_uses_per_user_hint' => 'Deixe em branco para ilimitado.',

    'usage_redeemed' => 'Resgatado',

    'raw_data_description' => 'Este tipo de desconto não tem formulário registrado no painel, então suas configurações salvas são editadas aqui como JSON.',
    'raw_data_invalid' => 'Informe um JSON válido.',
    'type_missing' => 'O pacote que registrou este tipo de desconto não está mais instalado.',

    'bulk_end_now' => 'Encerrar agora',
    'bulk_delete' => 'Excluir',
    'confirm_bulk_end' => 'Encerrar agora os descontos selecionados? Eles deixam de valer imediatamente, mas continuam na lista.',
    'confirm_bulk_delete' => 'Excluir os descontos selecionados? Os carrinhos que os utilizam serão recalculados sem eles.',

    'flash_created' => 'Desconto criado.',
    'flash_updated' => 'Desconto atualizado.',
    'flash_deleted' => 'Desconto excluído.',
    'flash_bulk_ended' => '{count} descontos encerrados.',
    'flash_bulk_deleted' => '{count} descontos excluídos.',
];
