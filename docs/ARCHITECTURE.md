# Architecture SEEF STORE

## Vue générale

```text
WordPress
├── Custom Theme: seef-store
│   └── WooCommerce Templates / UI
├── WooCommerce
│   ├── Product, Cart, Checkout, Order, Customer services
│   └── MariaDB (HPOS pour les commandes)
└── Custom Plugin: seef-store-core
    ├── Controllers
    ├── Frontend / Admin / Security
    ├── Validators / Models / Setup
    └── WordPress + WooCommerce APIs → MariaDB
```

WordPress n’a pas été converti en MVC. Le découpage MVC-like concerne uniquement le code personnalisé : les contrôleurs gèrent les requêtes, les validateurs normalisent les entrées, les modèles encapsulent une ressource, et les services enregistrent leurs hooks.

## Thème

`seef-store` contient la présentation : header, footer, landing page, pages À propos/Contact, wrappers WooCommerce, styles et interactions légères. `functions.php` ne fait que charger la classe de thème et les template tags.

## Plugin métier

Le point d’entrée installe un autoloader PSR-4 léger pour l’espace de noms `SeefStore`. `Plugin` compose les services : contact, newsletter, filtres produits, locale, traductions, en-têtes et protection de connexion. `DemoSeeder` utilise les API WordPress/WooCommerce, reste idempotent et n’est jamais lancé à l’activation. Il exige un lancement CLI explicite, `SEEF_DEMO_MODE=true`, un environnement `local`/`development` et un nouveau processus WordPress où WooCommerce est entièrement initialisé.

## Limites de responsabilité

- WooCommerce : catalogue, prix, inventaire, panier, checkout, commandes, paiements, clients ;
- plugin : contact, sécurité complémentaire, seeding, filtres et adaptation linguistique ;
- thème : rendu, accessibilité, responsive et micro-interactions ;
- MariaDB : persistance via les APIs officielles, sans requête SQL métier artisanale.
