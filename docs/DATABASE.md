# Base de données

## Connexion locale

La connexion n’est pas versionnée. Le fichier `wp-config.php`, ignoré par Git, lit `SEEF_DB_NAME`, `SEEF_DB_USER`, `SEEF_DB_PASSWORD` et `SEEF_DB_HOST` afin d’éviter de figer des secrets. La CI utilise une base MariaDB jetable `seef_store_ci` avec des identifiants explicitement réservés aux tests.

## Schéma

WordPress fournit `wp_posts`, `wp_postmeta`, `wp_users`, `wp_usermeta`, `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships` et les tables d’options/commentaires.

WooCommerce a créé ses tables lookup, sessions, taxes, webhooks et HPOS : `wp_wc_orders`, `wp_wc_orders_meta`, `wp_wc_order_addresses`, `wp_wc_order_operational_data`, etc. L’option `woocommerce_custom_orders_table_enabled` vaut `yes`.

## Ressources personnalisées

- `seef_contact` : post privé ; nom, e-mail et statut dans les métadonnées ; sujet dans le titre ; message dans le contenu ;
- `seef_subscriber` : post privé dont le titre est l’e-mail normalisé ;
- statuts de contact : `new`, `read`, `replied`, `archived`.

Aucune table custom n’est créée : les APIs de métadonnées répondent au besoin et évitent une migration inutile.

## Données de démonstration

16 produits, quatre catégories, un client et sept pages principales/configurées. Les assets produits sont locaux dans `wp-content/uploads/seef-store-products/`.
