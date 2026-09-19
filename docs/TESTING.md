# Tests

## Suite d’intégration

`$env:SEEF_DEMO_MODE='true'; C:\xampp\php\php.exe tests\run-integration.php`

Elle charge WordPress et WooCommerce puis contrôle notamment l’activation sans seeding, le flag de démo, les assets, les prix inversés, la newsletter, le catalogue, les stocks, les rôles, le contact, les paiements, le panier, les commandes, les langues et le RTL.

`C:\xampp\php\php.exe tests\theme-helpers.php` vérifie les URLs et le compteur panier lorsque WooCommerce n’est pas chargé.

`C:\xampp\php\php.exe tests\unit.php` couvre la sanitization et le rejet des valeurs non scalaires pour le contact, les prix et le sélecteur de langue. `C:\xampp\php\php.exe tests\demo-mode-safety.php` vérifie que le seeding reste désactivé par défaut et impossible en staging/production.

Les ressources créées pour les tests contact/commande sont supprimées à la fin ; le stock initial est restauré.

## Smoke HTTP

`powershell -ExecutionPolicy Bypass -File tests\http-smoke.ps1`

Vérifie les réponses 200 de l’accueil, boutique, produit, À propos, Contact, Panier et Mon compte, les en-têtes de sécurité et l’attribut RTL arabe.

`C:\xampp\php\php.exe tests\http-smoke.php` fournit l’équivalent portable utilisé sous Linux dans la CI. Les tests HTTP PHP acceptent `SEEF_BASE_URL` ; sa valeur par défaut reste l’URL XAMPP locale.

`C:\xampp\php\php.exe tests\contact-http.php` effectue un aller-retour HTTP anonyme complet : extraction du nonce, soumission, redirection de succès, rejet d’un nonce invalide et contrôle de persistance privée en base.

`tests\admin-http.php` exige `SEEF_ADMIN_USER` et `SEEF_ADMIN_PASSWORD`, puis authentifie ce compte local via `wp-login.php` et vérifie l’accès aux écrans produits, commandes HPOS, clients et messages.

## GitHub Actions

Le job statique lint uniquement le code propriétaire, exécute les tests sans base, vérifie le JavaScript et lance Gitleaks. Le job d’intégration crée une base MariaDB temporaire, génère un `wp-config.php` de CI avec des valeurs de test, initialise et seed WordPress avec `SEEF_DEMO_MODE=true`, puis exécute l’intégration et les parcours HTTP. Aucun identifiant de production n’est requis.

## Validation statique

```powershell
Get-ChildItem -Recurse -File wp-content\plugins\seef-store-core,wp-content\themes\seef-store,tools,tests -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
node --check wp-content\themes\seef-store\assets\js\store.js
```

## Validation manuelle recommandée

Parcourir les breakpoints 320, 375, 430, 768, 1024, 1280, 1440 et 1920 px ; vérifier navigation clavier, panier dynamique, formulaire, checkout complet et interfaces admin. Aucun navigateur pilotable n’était connecté lors de la validation automatisée finale.
