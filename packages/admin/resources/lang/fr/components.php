<?php

return [
    'dashboard.top_timeframe' => 'Dernier :period jours',
    'products.option-manager.add_btn' => 'Ajouter une option',
    'products.option-manager.toggle_btn' => 'Tout basculer',
    'products.product-selector.select_btn' => 'Sélectionnez les options',
    'products.product-selector.title' => 'Sélectionnez les options',
    'products.product-selector.available_tab' => 'Options disponibles',
    'products.product-selector.selected_tab' => 'Options sélectionnées',
    'products.product-selector.selected_empty' => 'Impossible de trouver des options avec le terme de recherche donné.',
    'products.product-selector.no_results' => 'Impossible de trouver des options avec le terme de recherche donné.',
    'products.product-selector.no_options' => 'Aucune option disponible, créez une nouvelle option pour la voir ici.',
    'products.product-selector.add_new_btn' => 'Créer une nouvelle option',
    'products.product-selector.use_selected_btn' => 'Utiliser les options sélectionnées',
    'products.option-creator.option_placeholder' => 'Par exemple: Couleur',
    'products.option-creator.value_placeholder' => 'Par exemple: Bleu',
    'products.option-creator.min_values_notice' => 'Vous devez avoir au moins :min valeurs.',
    'products.option-creator.values_title' => 'Valeurs des options de produit',
    'products.option-creator.title' => 'Créer une nouvelle option',
    'products.option-creator.add_value_btn' => 'Ajouter la valeur',
    'products.option-creator.create_option_btn' => 'Créer une option',
    'products.option-creator.values_strapline' => 'Ajoutez toutes les différentes valeurs possibles disponibles pour cette option de produit.',
    'product-search.btn' => 'Ajouter des produits',
    'product-search.first_tab' => 'Recherche de produits',
    'product-search.second_tab' => 'Produits sélectionnés',
    'product-search.max_results_exceeded' => 'Affichage de :max sur :total produits. Essayez d\'être plus précis dans votre recherche.',
    'product-search.exists_in_collection' => 'Déjà associé',
    'product-search.no_results' => 'Aucun résultat trouvé.',
    'product-search.pre_search_message' => 'Recherchez des produits par attribut ou SKU.',
    'product-search.select_empty' => 'Lorsque vous sélectionnez des produits, ils apparaîtront ici.',
    'product-search.title' => 'Rechercher des produits',
    'product-search.associate_self' => 'Vous ne pouvez pas associer le même produit',
    'product-search.commit_btn' => 'Sélectionner des produits',
    'product-variant-search.btn' => 'Ajouter des variantes',
    'product-variant-search.first_tab' => 'Rechercher des variantes',
    'product-variant-search.second_tab' => 'Variantes sélectionnées',
    'product-variant-search.max_results_exceeded' => 'Affichage de :max sur :total variantes. Essayez d\'être plus précis dans votre recherche.',
    'product-variant-search.exists_in_collection' => 'Déjà associé',
    'product-variant-search.no_results' => 'Aucun résultat trouvé.',
    'product-variant-search.pre_search_message' => 'Rechercher des variantes par SKU.',
    'product-variant-search.select_empty' => 'Lorsque vous sélectionnez des variantes, elles apparaîtront ici.',
    'product-variant-search.title' => 'Rechercher des variantes',
    'product-variant-search.associate_self' => 'Vous ne pouvez pas associer les mêmes variantes',
    'product-variant-search.commit_btn' => 'Sélectionnez des variantes',
    /**
     * Option Value Create Modal.
     */
    'ovcm.title' => 'Ajouter une nouvelle option à :name',
    /**
     * Attribute group create.
     */
    'attribute-group-edit.name.placeholder' => 'Détails supplémentaires',
    'attribute-group-edit.create_btn' => 'Créer un groupe d\'attributs',
    'attribute-group-edit.update_btn' => 'Mettre à jour le groupe d\'attributs',
    'attribute-group-edit.non_unique_handle' => 'Le nom du groupe d\'attributs doit être unique.',
    /**
     * Attribute show.
     */
    'attributes.show.create_group_btn' => 'Créer un groupe d\'attributs',
    'attributes.show.create_attribute' => 'Créer un attribut',
    'attributes.show.edit_group_btn' => 'Modifier le groupe d\'attributs',
    'attributes.show.edit_attribute_btn' => 'Modifier l\'attribut',
    'attributes.show.delete_group_btn' => 'Supprimer le groupe d\'attributs',
    'attributes.show.edit_title' => 'Modifier le groupe d\'attributs',
    'attributes.show.create_title' => 'Créer un groupe d\'attributs',
    'attributes.show.delete_title' => 'Supprimer le groupe d\'attributs',
    'attributes.show.delete_warning' => 'La suppression de ce groupe de clients supprimera également tous les attributs qui lui sont associés. Cette action ne peut pas être annulée.',
    'attributes.show.group_protected' => 'Ce groupe contient des attributs requis par le système et ne peut donc pas être supprimé.',
    'attributes.show.no_attributes_text' => 'Aucun attribut existant, glissez des attributs existants ou ajoutez-en de nouveaux ici.',
    'attributes.show.delete_attribute_btn' => 'Supprimer l’attribut',
    'attributes.show.delete_attribute_title' => 'Supprimer l’attribut',
    'attributes.show.delete_attribute_warning' => 'Êtes-vous sûr de vouloir supprimer cet attribut ?',
    'attributes.show.delete_attribute_protected' => 'Vous ne pouvez pas supprimer un attribut système.',
    'attributes.show.no_groups' => 'Aucun groupe d’attributs trouvé, ajoutez votre premier groupe avant de pouvoir y ajouter des attributs.',
    /**
     * Attribute edit.
     */
    'attribute-edit.create_title' => 'Créer un attribut',
    'attribute-edit.update_title' => 'Mettre à jour l’attribut',
    'attribute-edit.system_locked' => 'Cet attribut est requis par le système, donc certains champs sont désactivés.',
    'attribute-edit.name.placeholder' => 'ex. Nom',
    'attribute-edit.required.instructions' => 'Cet attribut est-il obligatoire lors de la modification/création ?',
    'attribute-edit.searchable.instructions' => 'Les utilisateurs doivent-ils pouvoir rechercher via cet attribut ?',
    'attribute-edit.filterable.instructions' => 'Les utilisateurs doivent-ils pouvoir filtrer les résultats en fonction de cet attribut ?',
    'attribute-edit.validation.instructions' => 'Spécifiez toutes les règles de validation Laravel pour cette entrée.',
    'attribute-edit.cancel_btn' => 'Annuler',
    'attribute-edit.save_attribute_btn' => 'Sauvegarder l’attribut',

    /**
     * Brand search.
     */
    'brand-search.btn' => 'Ajouter des marques',
    'brand-search.first_tab' => 'Rechercher des marques',
    'brand-search.second_tab' => 'Marques sélectionnées',
    'brand-search.max_results_exceeded' => 'Affichage des premières :max marques sur :total. Essayez d’être plus précis dans votre recherche.',
    'brand-search.exists_in_collection' => 'Déjà associé',
    'brand-search.no_results' => 'Aucun résultat trouvé.',
    'brand-search.pre_search_message' => 'Rechercher des marques par attribut.',
    'brand-search.select_empty' => 'Lorsque vous sélectionnez des marques, elles apparaîtront ici.',
    'brand-search.title' => 'Recherche de marques',
    'brand-search.commit_btn' => 'Sélectionner des marques',

    /**
     * Recherche de collections.
     */
    'collection-search.btn' => 'Ajouter des collections',
    'collection-search.first_tab' => 'Rechercher des collections',
    'collection-search.second_tab' => 'Collections sélectionnées',
    'collection-search.max_results_exceeded' => 'Affichage des premières :max collections sur :total. Essayez d’être plus précis dans votre recherche.',
    'collection-search.exists_in_collection' => 'Déjà associé',
    'collection-search.no_results' => 'Aucun résultat trouvé.',
    'collection-search.pre_search_message' => 'Rechercher des collections par attribut.',
    'collection-search.select_empty' => 'Lorsque vous sélectionnez des collections, elles apparaîtront ici.',
    'collection-search.title' => 'Recherche de collections',
    'collection-search.commit_btn' => 'Sélectionner des collections',

    /**
     * Clients.
     */
    'customers.show.metrics.total_orders' => 'Total des commandes',
    'customers.show.metrics.avg_spend' => 'Dépense moyenne',
    'customers.show.metrics.total_spend' => 'Dépense totale',
    'customers.show.year_spending' => 'Dépenses de l’année passée',
    'customers.show.purchase_history' => 'Historique d’achat',
    'customers.show.order_history' => 'Historique de commandes',
    'customers.show.users' => 'Utilisateurs',
    'customers.show.addresses' => 'Adresses des clients',
    'customers.show.customer_groups' => 'Groupes de clients',
    'customers.show.save_customer' => 'Sauvegarder le client',
    'customers.show.no_purchase_history' => 'Ce client n’a pas d’historique d’achat.',
    'customers.show.no_order_history' => 'Ce client n’a pas d’historique de commandes.',
    'customers.show.no_users' => 'Ce client n’a pas d’utilisateurs associés.',
    'customers.show.no_addresses' => 'Ce client n’a pas d’adresses.',
    'customers.show.remove_address_btn' => 'Retirer',
    'customers.show.remove_address.title' => 'Retirer l’adresse',
    'customers.show.remove_address.confirm' => 'Êtes-vous sûr de vouloir retirer cette adresse ?',
    /**
     * Index des commandes
     */
    'orders.index.returning_customer' => 'Client fidèle',
    'orders.index.new_customer' => 'Nouveau client',
    /**
     * Affichage de la commande.
     */
    'orders.show.title' => 'Commande',
    'orders.show.save_shipping_btn' => 'Sauvegarder l’adresse',
    'orders.show.save_billing_btn' => 'Sauvegarder l’adresse',
    'orders.show.print_btn' => 'Imprimer',
    'orders.show.refund_btn' => 'Rembourser',
    'orders.show.refund_lines_btn' => 'Lignes de remboursement',
    'orders.show.update_status_btn' => 'Mettre à jour le statut',
    'orders.show.more_actions_btn' => 'Plus d’actions',
    'orders.show.show_all_lines_btn' => 'Afficher toutes les lignes',
    'orders.show.additional_lines_text' => ':count lignes supplémentaires sont cachées',
    'orders.show.collapse_lines_btn' => 'Réduire les lignes',
    'orders.show.transactions_header' => 'Transactions',
    'orders.show.timeline_header' => 'Chronologie',
    'orders.show.additional_fields_header' => 'Informations supplémentaires',
    'orders.show.billing_matches_shipping' => 'Identique à l’adresse de livraison',
    'orders.show.billing_header' => 'Adresse de facturation',
    'orders.show.shipping_header' => 'Adresse de livraison',
    'orders.show.requires_capture' => 'Cette commande nécessite encore la capture du paiement.',
    'orders.show.capture_payment_btn' => 'Capturer le paiement',
    'orders.show.partially_refunded' => 'Cette commande a été partiellement remboursée.',
    'orders.show.refunded' => 'Cette commande a été remboursée.',
    'orders.show.view_customer' => 'Voir le client',
    'orders.show.tags_header' => 'Étiquettes',
    'orders.show.download_pdf' => 'Télécharger le PDF',

    /**
     * Remboursement de la commande.
     */
    'orders.refund.confirm_text' => 'CONFIRMER',
    'orders.refund.confirm_message' => 'Veuillez confirmer que vous souhaitez rembourser ce montant.',
    'orders.refund.no_charges' => 'Il n’y a pas de frais remboursables sur cette commande',
    'orders.refund.select_transaction' => 'Sélectionner une transaction',
    'orders.refund.refund_btn' => 'Envoyer le remboursement',
    'orders.refund.fully_refunded' => 'Les captures de cette commande ont été remboursées',
    /**
     * Index des marques.
     */
    'brands.index.title' => 'Marques',
    'brands.index.create_brand' => 'Créer une marque',
    'brands.index.table_row_action_text' => 'Modifier la marque',
    'brands.index.table_count_header_text' => 'Nombre de produits',
    'brands.choose_brand_default_option' => 'Sans marque',
    /**
     * Index des produits.
     */
    'products.index.title' => 'Produits',
    'products.index.create_product' => 'Créer un produit',
    'products.index.selected_products' => 'Vous avez sélectionné :count produits, voulez-vous sélectionner tous',
    'products.index.you_have_selected_all' => 'Vous avez sélectionné tous les :count produits.',
    'products.index.select_all_btn' => 'Sélectionner tout',
    'products.index.deselect_all_btn' => 'Désélectionner tout',
    'products.index.draft' => 'Brouillon',
    'products.index.published' => 'Publié',
    'products.index.deleted' => 'Supprimé',
    'products.index.only_deleted_visible' => 'Seuls les produits supprimés sont actuellement affichés',
    'products.index.products_empty' => 'Impossible de trouver des produits correspondant à la recherche/filtres.',

    /**
     * Capture de commande.
     */
    'orders.capture.confirm_text' => 'CONFIRMER',
    'orders.capture.confirm_message' => 'Veuillez confirmer que vous souhaitez capturer ce paiement',
    'orders.capture.no_intents' => 'Il n’y a pas de transactions disponibles pour la capture',
    'orders.capture.select_transaction' => 'Sélectionner une transaction',
    'orders.capture.capture_btn' => 'Capturer le paiement',

    /**
     * Statut de la commande.
     */
    'orders.status.update_btn' => 'Mettre à jour le statut',
    'orders.status.select_new' => 'Sélectionner un nouveau statut',
    'orders.status.preview.title' => 'Aperçu du modèle',
    'orders.status.preview.alert' => 'Ceci est un aperçu de l’apparence de votre e-mail.',
    'orders.status.no_status_selected_alert' => 'Sélectionnez un statut de commande pour voir les expéditeurs disponibles.',
    'orders.status.additional-content.label' => 'Contenu supplémentaire',
    'orders.status.additional-content.instructions' => 'Si pris en charge, ajoutez un message supplémentaire à la notification ou à l’expéditeur.',
    'orders.status.mailers.label' => 'Expéditeurs',
    'orders.status.mailers.instructions' => 'Sélectionnez les expéditeurs que vous souhaitez envoyer.',
    'orders.status.mailers.empty' => 'Il n’y a pas d’expéditeurs disponibles pour ce statut.',
    'orders.status.notifications.label' => 'Notifications',
    'orders.status.notifications.instructions' => 'Sélectionnez les notifications que vous souhaitez envoyer.',
    'orders.status.notifications.empty' => 'Il n’y a pas de notifications disponibles pour ce statut.',
    'orders.status.email_addresses.label' => 'Adresses email',
    'orders.status.email_addresses.instructions' => 'Sélectionnez les adresses email que vous souhaitez utiliser',
    'orders.status.additional_email.label' => 'Adresse email supplémentaire',
    'orders.status.additional_email.instructions' => 'Si vous devez utiliser une adresse email personnalisée, saisissez-la ici.',

    /**
     * Journal d'activité.
     */
    'activity-log.system' => 'Système',
    'activity-log.orders.status_change' => 'Statut mis à jour',
    'activity-log.orders.order_created' => 'Commande créée',
    'activity-log.orders.capture' => 'Paiement de :amount sur carte se terminant par :last_four',
    'activity-log.orders.authorized' => 'Autorisation de :amount sur carte se terminant par :last_four',
    'activity-log.orders.refund' => 'remboursement de :amount sur carte se terminant par :last_four',
    /**
     * Modification de la valeur d'option.
     */
    'option.value.edit.create_title' => 'Créer une valeur d’option',
    'option.value.edit.update_title' => 'Mettre à jour la valeur d’option',
    'option.value.edit.delete_locked' => 'Cette valeur d’option ne peut pas être supprimée car elle est requise par :count variantes de produit',
    'option.value.edit.system_locked' => 'Cette valeur d’option est requise par le système, donc certains champs sont désactivés.',
    'option.value.edit.name.placeholder' => 'ex. Nom',
    'option.value.edit.required.instructions' => 'Cette valeur d’option est-elle obligatoire lors de la modification/création ?',
    'option.value.edit.searchable.instructions' => 'Les utilisateurs doivent-ils pouvoir rechercher via cette option.value ?',
    'option.value.edit.filterable.instructions' => 'Les utilisateurs doivent-ils pouvoir filtrer les résultats en fonction de cette option.value ?',
    'option.value.edit.validation.instructions' => 'Spécifiez toutes les règles de validation Laravel pour cette entrée.',
    'option.value.edit.cancel_btn' => 'Annuler',
    'option.value.edit.save_feature.value.btn' => 'Sauvegarder la valeur d’option',

    /**
     * Affichage de la valeur d'option.
     */
    'option.create_group_btn' => 'Créer une option',
    'option.create_option_value' => 'Créer une valeur d’option',
    'option.update_option_value' => 'Mettre à jour la valeur d’option',
    'option.value_title' => 'Valeurs d’option de produit',
    'option.save_positions' => 'Sauvegarder les positions',
    'option.edit_group_btn' => 'Modifier l’option',
    'option.edit_option.value.btn' => 'Modifier la valeur d’option',
    'option.delete_group_btn' => 'Supprimer l’option',
    'option.edit_title' => 'Modifier l’option',
    'option.create_title' => 'Créer une option',
    'option.delete_title' => 'Supprimer une option',
    'option.delete_warning' => 'Vous ne pouvez pas supprimer une option qui a des valeurs associées.',
    'option.group_protected' => 'Ce groupe contient des valeurs d’option requises par le système et ne peut donc pas être supprimé.',
    'option.no_option_values_text' => 'Aucune valeur d’option existante.',
    'option.delete_option.value.btn' => 'Supprimer la valeur d’option',
    'option.delete_option.value.title' => 'Supprimer la valeur de caractéristique',
    'option.delete_option.value.warning' => 'Êtes-vous sûr de vouloir supprimer cette valeur d’option ?',
    'option.delete_option.value.protected' => 'Vous ne pouvez pas supprimer une option.value système.',
    'option.no_groups' => 'Aucune option trouvée, ajoutez la première avant de pouvoir y ajouter des valeurs d’option.',

    /**
     * Modification d'option.
     */
    'option-edit.create_btn' => 'Créer une option',
    'option-edit.update_btn' => 'Mettre à jour l’option',
    'option.value.edit.save_option.value.btn' => 'Sauvegarder la valeur d’option',
    /**
     * Réductions.
     */
    'discounts.index.title' => 'Réductions',
    'discounts.index.status.pending' => 'En attente',
    'discounts.index.status.active' => 'Actif',
    'discounts.index.status.scheduled' => 'Planifié',
    'discounts.index.status.expired' => 'Expiré',
    'discounts.index.create_discount' => 'Créer une réduction',
    'discounts.create.title' => 'Créer une réduction',
    'discounts.create_btn' => 'Créer une réduction',
    'discounts.save_btn' => 'Sauvegarder la réduction',
    'discounts.show.stop.label' => 'Arrêter l’application d’autres réductions après celle-ci',
    'discounts.show.danger_zone.label' => 'Supprimer la réduction',
    'discounts.show.danger_zone.instructions' => 'Entrez le nom de la réduction pour confirmer la suppression.',

    /**
     * Composant URL du modèle
     */
    'model-url.preview' => 'Aperçu',
    'model-url.view' => 'Voir',
];
