<?php

return [
    /**
     * Chaînes.
     */
    'channels.index.title' => 'Canaux',
    'channels.index.create_btn' => 'Créer un canal',
    'channels.index.table_row_action_text' => 'Modifier le canal',
    /**
     * Page de détail des canaux.
     */
    'channels.show.title' => 'Modifier le canal',
    /**
     * Page de création de canaux.
     */
    'channels.create.title' => 'Créer un canal',
    /**
     * Mise en page des paramètres.
     */
    'layout.menu_btn' => 'Menu des paramètres',
    /**
     * Page de liste du personnel.
     */
    'staff.index.title' => 'Personnel',
    'staff.index.search_placeholder' => 'Rechercher du personnel',
    'staff.index.active_filter' => 'Afficher les inactifs',
    'staff.index.create_btn' => 'Ajouter un membre du personnel',
    'staff.index.table_row_action_text' => 'Modifier le personnel',
    /**
     * Page de détail du personnel.
     */
    'staff.show.title' => 'Modifier le personnel',
    'staff.show.delete_btn' => 'Désactiver le compte',
    'staff.show.delete_title' => 'Supprimer le personnel',
    'staff.show.restore_title' => 'Restaurer le personnel',
    /**
     * Page de création du personnel.
     */
    'staff.create.title' => 'Créer un membre du personnel',
    /**
     * Formulaire du personnel.
     */
    'staff.form.create_btn' => 'Créer un membre du personnel',
    'staff.form.update_btn' => 'Mettre à jour le membre du personnel',
    'staff.form.permissions_heading' => 'Permissions',
    'staff.form.permissions_description' => 'Gérer les permissions individuelles d\'un membre du personnel.',
    'staff.form.admin_message' => 'Un utilisateur administrateur a accès à toutes les permissions.',
    'staff.form.danger_zone.label' => 'Supprimer le membre du personnel',
    'staff.form.danger_zone.delete_strapline' => 'La suppression d\'un membre du personnel arrêtera tout accès au hub, vous pourrez le restaurer ultérieurement.',
    'staff.form.danger_zone.restore_strapline' => 'Restaurer le compte de ce membre du personnel afin qu\'il puisse accéder au hub.',
    'staff.form.danger_zone.own_account' => 'La suppression de votre propre compte vous déconnectera instantanément.',
    /**
     * Page de liste des modules complémentaires.
     */
    'addons.index.title' => 'Modules complémentaires',
    'addons.index.table_row_action_text' => 'Voir',
    /**
     * Page de détail des modules complémentaires.
     */
    'addons.show.title' => 'Module complémentaire',
    /*
     * Page de liste des langues.
     */
    'languages.index.title' => 'Langues',
    'languages.index.create_btn' => 'Créer une langue',
    'languages.index.table_row_action_text' => 'Modifier la langue',
    /**
     * Page de création des langues.
     */
    'languages.create.title' => 'Créer une langue',
    /**
     * Page de détail des langues.
     */
    'languages.show.title' => 'Modifier la langue',
    /**
     * Formulaire de langue.
     */
    'languages.form.create_btn' => 'Créer une langue',
    'languages.form.update_btn' => 'Mettre à jour la langue',
    'languages.form.default_instructions' => 'Définir si cette langue est la langue par défaut, cela remplacera la langue actuelle par défaut.',
    /**
     * Tableau des devises.
     */
    'currencies.index.title' => 'Devises',
    'currencies.index.table_row_action_text' => 'Modifier',
    'currencies.index.no_results' => 'Vous n\'avez actuellement aucune devise dans le système.',
    /**
     * Page de détail des devises.
     */
    'currencies.show.title' => 'Modifier la devise',
    /**
     * Page de création de devises.
     */
    'currencies.create.title' => 'Créer une devise',
    'currencies.index.create_currency_btn' => 'Créer une devise',
    /**
     * Formulaire de devises.
     */
    'currencies.form.update_btn' => 'Mettre à jour la devise',
    'currencies.form.create_btn' => 'Créer une devise',
    'currencies.form.notify.created' => 'Devise créée',
    'currencies.form.format_help_text' => [
        'Cela vous permet de spécifier le format que les champs de prix doivent utiliser pour cette devise.',
        'Lors de l\'affichage, Lunar remplacera <code>{value}</code> par le prix formaté. Par exemple, <code>£{value}</code>.',
        'Vous devez toujours inclure <code>{value}</code> pour que cela fonctionne correctement.',
    ],
    /**
     * Attributs.
     */
    'attributes.index.title' => 'Attributs',
    'attributes.show.title' => 'Modification des attributs de :type',
    'attributes.show.locked' => 'Cet attribut est requis par le système et a donc été verrouillé en modification.',
    'attributes.create.title' => 'Créer un attribut',
    'attributes.form.update_btn' => 'Mettre à jour l\'attribut',
    'attributes.form.create_btn' => 'Créer un attribut',
    'attributes.form.notify.created' => 'Attribut créé',
    /**
     * Tags.
     */
    'tags.show.title' => 'Modifier le tag',
    'tags.index.title' => 'Tags',
    'tags.index.table_row_action_text' => 'Modifier',
    'tags.form.update_btn' => 'Mettre à jour le tag',
    'tags.form.create_btn' => 'Créer un tag',
    'tags.form.notify.updated' => 'Tag mis à jour',
    /**
     * Page de journal d'activité.
     */
    'activity_log.index.title' => 'Journal d\'activité',
    /*
     * Options de produit
     */
    'product.options.index.title' => 'Options',
    'product.options.index.create_btn' => 'Créer une option',
    'product.options.index.table_row_action_text' => 'Modifier l\'option',
    /**
     * Taxes.
     */
    'taxes.tax-zones.index.title' => 'Zones fiscales',
    'taxes.tax-zones.confirm_delete.title' => 'Confirmer la suppression',
    'taxes.tax-zones.confirm_delete.message' => 'Êtes-vous sûr de vouloir supprimer cette zone fiscale ? Cela pourrait entraîner une perte de données.',
    'taxes.tax-zones.customer_groups.title' => 'Restreindre aux groupes de clients',
    'taxes.tax-zones.customer_groups.instructions' => 'Sélectionnez les groupes de clients auxquels vous souhaitez restreindre cette zone. Laissez décoché pour aucune restriction.',
    'taxes.tax-zones.create_title' => 'Créer une zone fiscale',
    'taxes.tax-zones.create_btn' => 'Créer une zone fiscale',
    'taxes.tax-zones.delete_btn' => 'Supprimer la zone fiscale',
    'taxes.tax-zones.index.table_row_action_text' => 'Gérer',
    'taxes.tax-classes.index.title' => 'Classes de taxes',
    'taxes.tax-classes.index.create.title' => 'Créer une classe de taxe',
    'taxes.tax-classes.index.update.title' => 'Mettre à jour la classe de taxe',
    'taxes.tax-classes.create_btn' => 'Créer une classe de taxe',
    'taxes.tax-zones.price_display.label' => 'Affichage du prix',
    'taxes.tax-zones.price_display.excl_tax' => 'Hors taxe',
    'taxes.tax-zones.price_display.incl_tax' => 'Toutes taxes comprises',
    'taxes.tax-zones.zone_type.countries' => 'Limite aux pays',
    'taxes.tax-zones.zone_type.states' => 'Limite aux États / provinces',
    'taxes.tax-zones.zone_type.postcodes' => 'Limite aux codes postaux',
    'taxes.tax-zones.tax_rates.title' => 'Taux de taxe',
    'taxes.tax-zones.tax_rates.create_button' => 'Ajouter un taux de taxe',
    'taxes.tax-zones.save_btn' => 'Enregistrer la zone fiscale',
    'taxes.tax-classes.index.delete_message' => 'Êtes-vous sûr ? Cela pourrait entraîner une perte de données.',
    'taxes.tax-classes.index.delete_message_disabled' => 'Vous ne pouvez pas supprimer une classe de taxe associée à des variantes de produits',
    'taxes.tax-classes.index.delete_message_default' => 'Vous devez sélectionner une nouvelle valeur par défaut avant de supprimer',
    /**
     * Groupes de clients.
     */
    'customer-groups.index.title' => 'Groupes de clients',
    'customer-groups.index.create_btn' => 'Créer un groupe de clients',
    'customer-groups.index.table_row_action_text' => 'Modifier le groupe',
    /**
     * Page de détail des groupes de clients.
     */
    'customer-groups.show.title' => 'Modifier le groupe de clients',
    /**
     * Page de création des groupes de clients.
     */
    'customer-groups.create.title' => 'Créer un groupe de clients',
    'customer-groups.form.default_instructions' => 'Définissez si ce groupe de clients doit être le groupe par défaut.',
];
