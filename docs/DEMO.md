# Démonstration

## Parcours conseillé

1. Ouvrir l’accueil et basculer clair/sombre.
2. Changer FR, EN puis arabe et observer le RTL.
3. Filtrer la boutique puis ouvrir `Aura Mini Speaker`.
4. Ajouter deux unités au panier et mettre à jour la quantité.
5. Se connecter avec le compte client local créé par le seeder de démonstration.
6. Valider une commande avec le virement de démonstration ou le paiement à la livraison.
7. Envoyer un message depuis Contact puis le consulter dans WooCommerce → Messages clients.
8. Dans l’administration, modifier un prix, un stock ou un statut de commande.

## Initialisation explicite

Définir `SEEF_DEMO_MODE=true` uniquement avec `WP_ENVIRONMENT_TYPE=local` ou `development`, puis exécuter `tools/configure-store.php` en CLI. L’administrateur et son mot de passe proviennent de `SEEF_ADMIN_USER` / `SEEF_ADMIN_PASSWORD`; si le mot de passe est omis au premier bootstrap, sa valeur aléatoire n’est affichée qu’une fois dans le terminal. Le mot de passe du client peut être fourni par `SEEF_DEMO_CUSTOMER_PASSWORD` (12 caractères minimum) ; sinon il est généré et affiché une seule fois lors de sa création.

## Avertissement

N’utiliser aucune identité, adresse ou donnée bancaire réelle. Les paiements, coordonnées, produits, témoignages et livraisons sont fictifs.
