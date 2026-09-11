# Tests

## Suite d’intégration

`C:\xampp\php\php.exe tests\run-integration.php`

Elle charge WordPress et WooCommerce puis contrôle installation, activation, pages, catalogue, prix, promotions, stock, images/alt, rôles, mot de passe hashé, validation contact, nonce, confidentialité, paiements, livraison, panier, quantités, totaux, commande HPOS, réduction/restauration de stock, anglais, arabe et RTL.

Les ressources créées pour les tests contact/commande sont supprimées à la fin ; le stock initial est restauré.

## Smoke HTTP

`powershell -ExecutionPolicy Bypass -File tests\http-smoke.ps1`

Vérifie les réponses 200 de l’accueil, boutique, produit, À propos, Contact, Panier et Mon compte, les en-têtes de sécurité et l’attribut RTL arabe.

`C:\xampp\php\php.exe tests\contact-http.php` effectue un aller-retour HTTP anonyme complet : extraction du nonce, soumission, redirection de succès, rejet d’un nonce invalide et contrôle de persistance privée en base.

`C:\xampp\php\php.exe tests\admin-http.php` authentifie le compte administrateur local via `wp-login.php`, puis vérifie l’accès aux écrans produits, commandes HPOS, clients et messages.

## Validation statique

```powershell
Get-ChildItem -Recurse -File wp-content\plugins\seef-store-core,wp-content\themes\seef-store,tools,tests -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
node --check wp-content\themes\seef-store\assets\js\store.js
```

## Validation manuelle recommandée

Parcourir les breakpoints 320, 375, 430, 768, 1024, 1280, 1440 et 1920 px ; vérifier navigation clavier, panier dynamique, formulaire, checkout complet et interfaces admin. Aucun navigateur pilotable n’était connecté lors de la validation automatisée finale.
