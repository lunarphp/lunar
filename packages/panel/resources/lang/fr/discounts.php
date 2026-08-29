<?php

return [
    'title' => 'Réductions',
    'description' => 'Configurez les promotions qui réduisent ce que paie le client — un pourcentage, un montant fixe ou une offre un acheté un offert — et déterminez quand, où et pour qui chacune s\'applique.',
    'new_discount' => 'Nouvelle réduction',
    'create_title' => 'Nouvelle réduction',
    'create_description' => 'Nommez la réduction et choisissez comment elle diminue le prix ; tout le reste se configure sur la page de la réduction.',
    'create_discount' => 'Créer la réduction',
    'back_to_discounts' => 'Retour aux réductions',
    'delete_discount' => 'Supprimer la réduction',
    'confirm_delete_discount' => 'Supprimer cette réduction ? Les paniers qui l\'utilisent seront recalculés sans elle.',

    'column_status' => 'Statut',
    'column_name' => 'Nom',
    'column_type' => 'Type',
    'column_coupon' => 'Code promo',
    'column_window' => 'Période',
    'column_usage' => 'Utilisation',
    'column_priority' => 'Priorité',

    'search_placeholder' => 'Rechercher des réductions',
    'filter_status' => 'Statut',
    'filter_all_statuses' => 'Tous les statuts',
    'filter_type' => 'Type',
    'filter_all_types' => 'Tous les types',
    'filter_channel' => 'Canal',
    'filter_all_channels' => 'Tous les canaux',
    'filter_customer_group' => 'Groupe de clients',
    'filter_all_customer_groups' => 'Tous les groupes de clients',
    'filter_redemption' => 'Application',
    'filter_all_redemptions' => 'Code promo et automatique',
    'redemption_coupon' => 'Nécessite un code promo',
    'redemption_automatic' => 'Appliquée automatiquement',
    'sort_priority' => 'Par priorité',
    'sort_name' => 'Nom A-Z',
    'sort_starts' => 'Commençant le plus tôt',
    'sort_ends' => 'Se terminant le plus tôt',
    'sort_uses' => 'Les plus utilisées',
    'count_of' => '{shown} sur {total}',
    'clear_filters' => 'Effacer les filtres',
    'empty_title' => 'Aucune réduction correspondante',
    'empty_description' => 'Effacez la recherche ou les filtres, ou créez une nouvelle réduction.',
    'empty_none_title' => 'Aucune réduction pour le moment',
    'empty_none_description' => 'Créez votre première réduction pour lancer vos promotions.',

    'status_active' => 'Active',
    'status_scheduled' => 'Planifiée',
    'status_expired' => 'Expirée',
    'status_pending' => 'En attente',

    'kpi_active_label' => 'Actives',
    'kpi_active_hint' => 'En cours aujourd\'hui',
    'kpi_scheduled_label' => 'Planifiées',
    'kpi_scheduled_hint' => 'Débutent plus tard',
    'kpi_ending_label' => 'Bientôt terminées',
    'kpi_ending_hint' => 'Sous 7 jours',
    'kpi_redemptions_label' => 'Utilisations',
    'kpi_redemptions_hint' => 'Toutes réductions, depuis le début',
    'show_kpis' => 'Afficher les statistiques',

    'summary_percentage_off' => ':percentage % de réduction',

    'summary_fixed_amount_off' => ':amount de réduction',

    'summary_buy_x_get_y' => 'Achetez :buy, obtenez :get',

    'field_percentage' => 'Pourcentage de réduction',

    'field_percentage_hint' => 'Retiré de chaque ligne éligible.',

    'field_amount' => 'Montant de la réduction',

    'field_amounts_hint' => 'Définissez un montant par devise. Une devise laissée vide ne reçoit aucune réduction.',

    'field_min_qty' => 'Quantité à acheter',

    'field_reward_qty' => 'Quantité offerte',

    'field_max_reward_qty' => 'Maximum offert',

    'field_max_reward_qty_hint' => 'Laissez vide pour récompenser chaque lot éligible.',

    'field_automatically_add_rewards' => 'Ajouter automatiquement les articles offerts au panier',

    'field_automatically_add_rewards_hint' => 'Ajoute les produits offerts à la place du client plutôt que d\'attendre qu\'il le fasse.',

    'section_targets' => 'S\'applique à',

    'section_targets_description' => 'Limitez cette réduction à une partie du catalogue. Un bloc laissé vide s\'applique partout.',

    'section_customers' => 'Clients éligibles',

    'bucket_limitation' => 'S\'applique à',

    'bucket_limitation_description' => 'Seuls ceux-ci sont réduits.',

    'bucket_exclusion' => 'Exclus',

    'bucket_exclusion_description' => 'Jamais réduits, même s\'ils correspondent ci-dessus.',

    'bucket_condition' => 'Produits qualifiants',

    'bucket_condition_description' => 'Ce que le client doit acheter pour obtenir l\'article offert.',

    'bucket_reward' => 'Produits offerts',

    'bucket_reward_description' => 'Ce que reçoit le client.',

    'bucket_customers' => 'Clients éligibles',

    'bucket_customers_description' => 'Seuls ces clients peuvent utiliser la réduction. Laissez vide pour l\'ouvrir à tous.',

    'kind_products' => 'Produits',

    'kind_variants' => 'Variantes',

    'kind_collections' => 'Collections',

    'kind_brands' => 'Marques',

    'kind_customers' => 'Clients',

    'target_add' => 'Ajouter',

    'target_remove' => 'Retirer {label}',

    'target_empty' => 'Rien de sélectionné : s\'applique donc à tout.',

    'target_dialog_title' => 'Ajouter des cibles',

    'target_dialog_description' => 'Recherchez parmi tout ce que ce bloc peut cibler.',

    'target_search_placeholder' => 'Rechercher produits, collections, marques',

    'target_no_results' => 'Aucun résultat.',

    'target_add_selected' => 'Ajouter {count}',

    'section_conditions' => 'Conditions',

    'section_conditions_description' => 'Ce qu\'un panier doit remplir avant que cette réduction s\'applique.',

    'field_min_spend' => 'Montant minimum',

    'field_min_spend_hint' => 'Définissez un seuil par devise. Une devise laissée vide n\'a aucun minimum.',

    'automatic' => 'Automatique',
    'no_end_date' => 'Sans date de fin',
    'usage_unlimited' => 'sans limite',
    'usage_of' => '{used} sur {max}',

    'section_details' => 'Détails',
    'section_details_description' => 'Comment cette réduction est identifiée et où elle se place dans l\'ordre d\'application.',
    'section_configuration' => 'Configuration',
    'section_configuration_description' => 'Ce que cette réduction fait au prix.',
    'section_schedule' => 'Planification',
    'section_usage' => 'Utilisation',
    'section_activity' => 'Activité',
    'activity_see_all' => 'Tout voir',
    'activity_empty' => 'Rien d\'enregistré pour l\'instant.',

    'field_name' => 'Nom',
    'field_name_create_hint' => 'Visible par l\'équipe. L\'identifiant en est dérivé et reste modifiable ensuite.',
    'field_handle' => 'Identifiant',
    'field_handle_hint' => 'Une référence unique et stable pour cette réduction.',
    'field_type' => 'Type',
    'field_coupon' => 'Code promo',
    'field_coupon_hint' => 'Laissez vide pour appliquer la réduction automatiquement.',
    'field_starts_at' => 'Début',
    'field_ends_at' => 'Fin',
    'field_ends_at_hint' => 'Laissez vide pour qu\'elle s\'applique jusqu\'à ce que vous la désactiviez.',
    'field_priority' => 'Priorité',
    'field_priority_hint' => 'La valeur la plus basse passe en premier. Les réductions de même priorité s\'appliquent dans un ordre indéterminé.',
    'field_stop' => 'Arrêter après cette réduction',
    'field_stop_hint' => 'Ignorer toutes les réductions de priorité inférieure dès que celle-ci s\'applique.',
    'field_max_uses' => 'Utilisations maximales',
    'field_max_uses_hint' => 'Laissez vide pour un nombre illimité.',
    'field_max_uses_per_user' => 'Maximum par client',
    'field_max_uses_per_user_hint' => 'Laissez vide pour un nombre illimité.',

    'usage_redeemed' => 'Utilisée',

    'raw_data_description' => 'Aucun formulaire n\'est enregistré dans le panneau pour ce type de réduction ; ses réglages sont donc modifiés ici au format JSON.',
    'raw_data_invalid' => 'Saisissez un JSON valide.',
    'type_missing' => 'Le paquet qui avait enregistré ce type de réduction n\'est plus installé.',

    'bulk_end_now' => 'Terminer maintenant',
    'bulk_delete' => 'Supprimer',
    'confirm_bulk_end' => 'Terminer maintenant les réductions sélectionnées ? Elles cessent aussitôt de s\'appliquer mais restent dans la liste.',
    'confirm_bulk_delete' => 'Supprimer les réductions sélectionnées ? Les paniers qui les utilisent seront recalculés sans elles.',

    'flash_created' => 'Réduction créée.',
    'flash_updated' => 'Réduction mise à jour.',
    'flash_deleted' => 'Réduction supprimée.',
    'flash_bulk_ended' => '{count} réductions terminées.',
    'flash_bulk_deleted' => '{count} réductions supprimées.',
];
