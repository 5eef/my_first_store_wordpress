# Base de données

## Connexion locale détectée

`my_first_store_wordpress` sur `127.0.0.1:3307`, MariaDB 10.4.32, charset `utf8mb4`. La base était vide avant la mission. `wp-config.php` accepte des variables d’environnement afin d’éviter de figer des secrets.

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
